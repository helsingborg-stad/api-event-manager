<?php

namespace EventManager\Modules\FrontendForm;

class FormStepState
{
    public bool $isPassed;
    public bool $isCurrent;
    public bool $isFuture;
    public ?int $nextStep;
    public ?int $previousStep;
    public bool $isLast;
    public bool $isFirst;

    public function __construct(FormStep $step, FormState $state, $steps)
    {
        $this->isPassed  = $this->isPassed($step, $state);
        $this->isCurrent = $this->isCurrent($step, $state);
        $this->isFuture  = (!$this->isPassed && !$this->isCurrent);
        $this->nextStep  = $this->nextStep($step, $steps);
        $this->isLast    = $this->isLast($step, $steps);
        $this->isFirst   = $this->isFirst($step);
    }

    public function isPassed(FormStep $step, FormState $state): bool
    {
        if ($state->currentStep > $step->step) {
            return true;
        }
        return false;
    }

    public function isCurrent(FormStep $step, FormState $state): bool
    {
        if ($state->currentStep === $step->step) {
            return true;
        }
        return false;
    }

    public function nextStep(FormStep $step, $steps): ?int
    {
        $nextStep = $step->step + 1;
        if (array_key_exists($nextStep, $steps)) {
            return $nextStep;
        }
        return null;
    }

    public function previousStep(FormStep $step, $steps): ?int
    {
        $previousStep = $step->step - 1;
        if (array_key_exists($previousStep, $steps)) {
            return $previousStep;
        }
        return null;
    }

    public function isLast(FormStep $step, $steps): bool
    {
        if ($step->step === count($steps)) {
            return true;
        }
        return false;
    }

    public function isFirst(FormStep $step): bool
    {
        if ($step->step === 1) {
            return true;
        }
        return false;
    }
}
