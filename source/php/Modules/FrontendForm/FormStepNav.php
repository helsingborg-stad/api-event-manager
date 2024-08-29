<?php

namespace EventManager\Modules\FrontendForm;

use EventManager\Modules\FrontendForm\FormStepState;

class FormStepNav
{
    public ?string $previous = null;
    public ?string $current  = null;
    public ?string $next     = null;

    /**
     * Construct the FormStepNav object.
     *
     * @param FormStep $step
     * @param FormStepState $stepState
     * @param array $steps
     * @param string $baseUrl
     * @param array $queryVars
     */
    public function __construct(
        FormStep $step,
        FormStepState $stepState,
        array $steps,
        string $baseUrl,
        array $queryVars
    ) {
        $this->previous = $this->getprevious($step, $stepState, $steps, $baseUrl, $queryVars);
        $this->current  = $this->getcurrent($step, $baseUrl, $queryVars);
        $this->next     = $this->getnext($step, $stepState, $steps, $baseUrl, $queryVars);
    }

    /**
     * Get the previous step link.
     *
     * @param FormStep $step
     * @param FormStepState $state
     * @param array $steps
     * @param string $baseUrl
     * @param array $queryVars
     * @return string|null
     */
    private function getprevious(
        FormStep $step,
        FormStepState $state,
        array $steps,
        string $baseUrl,
        array $queryVars
    ): ?string {
        $previousStep = $state->previousStep($step, $steps);
        if ($previousStep) {
            return $this->getStepLink($previousStep, $baseUrl, $queryVars);
        }
        return null;
    }

    /**
     * Get the next step link.
     *
     * @param FormStep $step
     * @param FormStepState $state
     * @param array $steps
     * @param string $baseUrl
     * @param array $queryVars
     * @return string|null
     */
    private function getnext(FormStep $step, FormStepState $state, $steps, string $baseUrl, array $queryVars): ?string
    {
        $nextStep = $state->nextStep($step, $steps);
        if ($nextStep) {
            return $this->getStepLink($nextStep, $baseUrl, $queryVars);
        }
        return null;
    }

    /**
     * Get the current step link.
     *
     * @param FormStep $step
     * @param string $baseUrl
     * @param array $queryVars
     * @return string|null
     */
    private function getcurrent(FormStep $step, string $baseUrl, array $queryVars): ?string
    {
        return $this->getStepLink($step->step, $baseUrl, $queryVars) ?? null;
    }

    /**
     * Get the step link. Purposly handles query vars without
     * http_build_query due to bad handling in acf of % in strings.
     *
     * @param int $step
     * @param string $baseUrl
     * @param array $queryVars
     * @return string
     */
    private function getStepLink(int $step, string $baseUrl, array $queryVars): string
    {
        if (is_countable($queryVars) && count($queryVars) > 0) {
            $queryVars['step'] = $step;
            $baseUrl           = $baseUrl . '?' . (function () use ($queryVars) {
                foreach ($queryVars as $param => $value) {
                    $paramsJoined[] = "$param=$value";
                }
                return implode('&', $paramsJoined);
            })();
        }

        return $baseUrl . '#frmstp' . $step;
    }
}
