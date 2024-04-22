<?php 
namespace EventManager\Modules\FrontendForm;

class FormStepState {

    public bool $isPassed;
    public bool $isCurrent; 
    public ?int $nextStep;
    public ?int $previousStep;

    public function __construct(FormStep $step, FormState $state, $steps)
    {
        $this->isPassed = $this->isPassed($step, $state);
        $this->isCurrent = $this->isCurrent($step, $state);
        $this->nextStep = $this->nextStep($step, $steps);
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

    public function nextStep(FormStep $step, $steps): ?int {
        $nextStep = $step->step + 1;
        if(array_key_exists($nextStep, $steps)) {
            return $nextStep;
        }
        return null;
    }

    public function previousStep(FormStep $step, $steps): ?int {
        $previousStep = $step->step - 1;
        if(array_key_exists($previousStep, $steps)) {
            return $previousStep;
        }
        return null;
    }
}

