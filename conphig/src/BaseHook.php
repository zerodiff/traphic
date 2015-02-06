<?php
/**
 * Base functionality for EatStreet arcanist hooks
 */
abstract class BaseHook {
    const PH_ID = "id";
    const PH_REVISIONID = "revisionID";
    const PH_BRANCH = "branch";

    protected $console;

    public function __construct() {
        $this->console = PhutilConsole::getConsole();
    }

    /**
     * Override this to actually do stuff.
     */
    public abstract function doHook(ArcanistWorkflow $workflow);

    protected function writeOut($str) {
        $this->console->writeOut(pht(
            "%s: %s\n", $this->getOutPrefix(), $str));
    }

    protected function writeErr($str) {
        $this->console->writeOut(pht(
            "%s: !!! %s\n", $this->getOutPrefix(), $str));
    }

    /**
     * Which hook our error messages are coming from.
     */
    protected abstract function getOutPrefix();

}
?>
