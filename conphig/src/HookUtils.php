<?php
/**
 * Static utility helpers
 */
class HookUtils {
    const PH_TITLE = "title";
    const PH_SUMMARY = "summary";
    const PH_TESTPLAN = "testPlan";

    // the first %s is the diff id, the second is the topic branch name
    const REMOTE_BRANCH_NAME_TEMPLATE = "ES_D%s_%s";

    /**
     * Create the remote branch name in an consistent way
     */
    public static function createRemoteBranchName($revisionId, $topicBranch) {
        return pht(self::REMOTE_BRANCH_NAME_TEMPLATE, $revisionId, $topicBranch);
    }

    /**
     * See if we should skip the branch push/delete if we have
     * [skip ci] or [ci skip] in various fields of the diff.
     */
    public static function shouldSkipCi($revisionDict) {
        $title = self::getStringValueFromObj(self::PH_TITLE, $revisionDict);
        $summary = self::getStringValueFromObj(self::PH_SUMMARY, $revisionDict);
        $testPlan = self::getStringValueFromObj(self::PH_TESTPLAN, $revisionDict);
        return self::matchesSkipCi($title) ||
               self::matchesSkipCi($summary) ||
               self::matchesSkipCi($testPlan);
    }

    /**
     * Determing if a string matches our skip ci text.
     */
    public static function matchesSkipCi($str) {
        return preg_match("/\[(?:skip ci|ci skip)\]/i", $str);
    }

    /**
     * There's probably a better way to do this, but give explicit warnings
     * about things we can't find rather than PHP warnings.
     */
    public static function getStringValueFromObj($field, $obj) {
        $ret = null;
        if ( array_key_exists($field, $obj) ) {
            $ret = $obj[$field];
        }
        return is_string($ret) ? $ret : null;
    }
}
?>
