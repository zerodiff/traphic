<?php

/**
 * Add arcanist hooks to, e.g., push commits to GitHub for Travis-CI integration.
 *
 * See:
 *  https://secure.phabricator.com/book/arcanist/class/ArcanistConfiguration
 *  https://secure.phabricator.com/diffusion/ARC/browse/master/src/configuration/ArcanistConfiguration.php
 *  https://secure.phabricator.com/book/arcanist/class/ArcanistDiffWorkflow/
 *  https://secure.phabricator.com/diffusion/ARC/browse/master/src/workflow/ArcanistDiffWorkflow.php
 */
class Conphig extends ArcanistConfiguration {

    /**
     * Adds post workflow hooks
     */
    public function didRunWorkflow($command, ArcanistWorkflow $workflow, $err) {
        $workflowName = $workflow->getWorkflowName();

        if ( ! $err ) {
            $hook = null;

            switch($workflowName) {
                case "diff": $hook = new PostDiffHook(); break;
                case "land": $hook = new PostLandHook(); break;
                default: $hook = null;
            }

            if ( $hook ) {
                $hook->doHook($workflow);
            }
        }
    }
}

?>
