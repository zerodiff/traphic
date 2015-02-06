<?php
/**
 * The EatStreet post diff hook to push the diff to GitHub so it goes through
 * Travis.
 */
class PostLandHook extends BaseHook {

    const OUT_PREFIX = "ESLAND";

    public function doHook(ArcanistWorkflow $workflow) {
        $dict = $workflow->getRevisionDict();

        if ( $dict ) {
            // Here we actually have a differential object, aka a revision.
            $revisionId = HookUtils::getStringValueFromObj(self::PH_ID, $dict);
            $topicBranch = HookUtils::getStringValueFromObj(self::PH_BRANCH, $dict);

            if ( $revisionId && $topicBranch ) {
                $remoteBranchName =
                    HookUtils::createRemoteBranchName($revisionId, $topicBranch);

                if ( HookUtils::shouldSkipCi($dict) ) {
                    $this->writeOut(pht(
                        "Saw skip ci message in commit, skipping delete of remote branch %s\n",
                        $remoteBranchName));
                } else {
                    // Here is the majicks:
                    $this->deleteRemoteBranch($remoteBranchName);
                }
            }
        }
    }

    public function deleteRemoteBranch($remoteBranchName) {
        // sanity
        if ( $remoteBranchName ) {
            $gitCommand = escapeshellcmd(pht(
                "git push origin --delete '%s'", $remoteBranchName));

            $this->writeOut(pht(
                "Removing remote branch %s after landing with this command:\n    %s\n",
                $remoteBranchName, $gitCommand));

            $exitCode = 0;
            passthru($gitCommand, $exitCode);
            if ( $exitCode ) {
                $this->writeErr("The push to GitHub failed, and you probably saw an error");
            }
        } else {
            $this->writeErr("Cowardly refusing to delete a remote branch with no name");
        }
    }

    protected function getOutPrefix() {
        return self::OUT_PREFIX;
    }

}
?>
