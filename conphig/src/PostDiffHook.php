<?php
/**
 * The EatStreet post diff hook to push the diff to GitHub so it goes through
 * Travis.
 */
class PostDiffHook extends BaseHook {

    const OUT_PREFIX = "ESDIFF";

    public function doHook(ArcanistWorkflow $workflow) {
        $diffObj = $this->getDiffObj($workflow);

        $revisionID = HookUtils::getStringValueFromObj(self::PH_REVISIONID, $diffObj);
        $topicBranch = HookUtils::getStringValueFromObj(self::PH_BRANCH, $diffObj);

        $revisionDict = $this->getRevisionObj($workflow, $revisionID);
        if ($revisionDict) {
            if ( $revisionID && $topicBranch ) {
                $remoteBranchName = HookUtils::createRemoteBranchName($revisionID, $topicBranch);

                if ( HookUtils::shouldSkipCi($revisionDict) ) {
                    $this->writeOut(pht(
                        "Saw skip ci message in commit, skipping push of remote branch %s\n",
                        $remoteBranchName));
                } else {
                    // this is where the magic happens
                    $this->pushBranchToRemote($topicBranch, $remoteBranchName);
                }
            } else {
                $this->writeErr("Could not determine branch name to push to GitHub");
            }
        }
    }

    private function getRevisionObj(ArcanistWorkflow $workflow, $revisionID) {
        $this->writeOut(pht("Looking up revision ID %s ...", $revisionID));

        // setup our query
        $conduit = $workflow->getConduit();
        $query = array( "ids" => array( $revisionID ) );

        $revisionDictArr =
            $conduit->callMethodSynchronous('differential.query', $query);

        // look for index 0, since there should only be one result
        // looking up by id
        $revisionDict = null;
        if ( array_key_exists(0, $revisionDictArr) ) {
            $revisionDict = $revisionDictArr[0];
        } else {
            $this->writeErr("Did not find revision in query result from Phabricate");

            $errorMessage =
                HookUtils::getStringValueFromObj("errorMessage", $revisionDictArr);

            if ( $errorMessage ) {
                $this->writeErr($errorMessage);
            }
        }

        return $revisionDict;
    }

    private function getDiffObj(ArcanistWorkflow $workflow) {
        $diffId = $workflow->getDiffID();

        // The diff information is not in the workflow object, so we need
        // to request it from Phabricator via the diffId. One "differential"
        // can have many "diffs", e.g. if you amend a commit or have
        // multipe ones for the same differential. The "revision"
        // refers to the whole differential.
        $this->writeOut(pht("Getting diff with ID %s ...", $diffId));

        // setup our query
        $conduit = $workflow->getConduit();
        $query = array( "ids" => array( $diffId ) );

        // This gives a key/value pair of results, e.g.:
        // 11 => { ... the diff object ... }
        // where "11" is the diffId
        $diffQueryResultArr =
            $conduit->callMethodSynchronous('differential.querydiffs', $query);

        $diffObj = null;
        if ( array_key_exists($diffId, $diffQueryResultArr) ) {
            $diffObj = $diffQueryResultArr[$diffId];
        } else {
            $this->writeErr("Did not find diff in query result from Phabricate");

            $errorMessage =
                HookUtils::getStringValueFromObj("errorMessage", $diffQueryResultArr);

            if ( $errorMessage ) {
                $this->writeErr($errorMessage);
            }
        }

        return $diffObj;
    }

    private function pushBranchToRemote($topicBranch, $remoteBranchName) {
        // Using force here because we don't really care what was there
        // before... we just want the new changes to get CI'd.
        $gitCommand = escapeshellcmd("git push origin '$topicBranch:$remoteBranchName' --force");

        $this->writeOut(pht(
            "Pushing to remote branch %s on GitHutb with this command:\n    %s\n",
            $remoteBranchName, $gitCommand));

        $exitCode = 0;
        passthru($gitCommand, $exitCode);
        if ( $exitCode ) {
            $this->writeErr("The push to GitHub failed, and you probably saw an error");
        }
    }

    protected function getOutPrefix() {
        return self::OUT_PREFIX;
    }

}
?>
