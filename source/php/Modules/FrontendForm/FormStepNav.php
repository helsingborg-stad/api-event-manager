<?php 
namespace EventManager\Modules\FrontendForm;

use EventManager\Modules\FrontendForm\FormStepState;

/* TODO: Make this gpt code work */
class FormStepNav {

    public ?string $previous = null;
    public ?string $current = null;
    public ?string $next = null; 

    public function __construct(FormStep $step, FormStepState $stepState, $steps)
    {
      $this->previous = $this->getprevious($step, $stepState, $steps);
      $this->current = $this->getcurrent($step, $stepState, $steps);
      $this->next = $this->getnext($step, $stepState, $steps);
    }

    private function getprevious(FormStep $step, FormStepState $state, $steps): ?string
    {
      $previousStep = $state->previousStep($step, $steps);
      if($previousStep) {
        return $this->getStepLink($previousStep);
      }
      return null; 
    }

    private function getnext(FormStep $step, FormStepState $state, $steps): ?string
    {
      $nextStep = $state->nextStep($step, $steps);
      if($nextStep) {
        return $this->getStepLink($nextStep);
      }
      return null;
    }

    private function getcurrent(FormStep $step, FormStepState $state, $steps): ?string
    {
      return $this->getStepLink($step->step) ?? null;
    }

    private function getStepLink($step): string
    {
        return add_query_arg('step', $step);
    }

    //* Todo remove/move */ 
    private function createReturnUrl($step, $formId): string
    {
        $urlParts = [
            'formid' => $formId,
            'step' => $step
        ];
        $urlParts    = array_filter($urlParts);
        $queryString = http_build_query($urlParts);
        return $queryString ? '?' . $queryString : '';
    }
}

