<?php 
namespace EventManager\Modules\FrontendForm;

class FormStepState {

    public bool $isPassed;
    public bool $isCurrent; 

    public function __construct(FormStep $step, FormState $state)
    {
        $this->isPassed = $this->isPassed($step, $state);
        $this->isCurrent = $this->isCurrent($step, $state);
    }

    public function isPassed(FormStep $step, FormState $state): bool {
        if($state->currentStep > $step->step) {
            return true;
        }
        return false;
    }

    public function isCurrent(FormStep $step, FormState $state): bool {
        if($state->currentStep === $step->step) {
            return true;
        }
        return false;
    }
}

