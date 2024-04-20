<?php 
namespace EventManager\Modules\FrontendForm;

/* TODO: Make this gpt code work */
class FormStepNav {

    public string $previousStepLink;
    public string $nextStepLink; 

    public function __construct(FormStep $step, FormState $state)
    {
      if(!is_a($step->state, 'FormStepState')) {
        throw new \Exception('FormStep must have a FormStepState object.');
      }

      $this->previousStepLink = $this->getPreviousStepLink($step, $state);
      $this->nextStepLink = $this->getNextStepLink($step, $state);
    }

    private function getPreviousStepLink(FormStep $step, FormState $state): string
    {
        if($state->isFirstStep) {
            return '';
        }
        return $this->getStepLink($state->previousStep);
    }

    private function getNextStepLink(FormStep $step, FormState $state): string
    {
        if($state->isLastStep) {
            return '';
        }
        return $this->getStepLink($state->nextStep);
    }

    private function getStepLink($step): string
    {
        return add_query_arg('step', $step);
    }
}

