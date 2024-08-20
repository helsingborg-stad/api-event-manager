<?php

namespace EventManager\Modules\FrontendForm;

class FormState
{
    public bool $isValidStep;
    public int $currentStep;
    public ?int $nextStep;
    public ?int $previousStep;
    public int $totalSteps;
    public int $percentageCompleted;
    public bool $hasStoredData;
    public bool $isCompleted;

    public function __construct(array $steps, string $queryParam = '')
    {
        $this->currentStep = $this->getCurrentStep($queryParam);

        $this->nextStep            = $this->getNextStep($steps);
        $this->previousStep        = $this->getPreviousStep();
        $this->currentStep         = $this->getCurrentStep($queryParam);
        $this->isValidStep         = $this->isValidStep(
            $steps,
            $this->currentStep
        );
        $this->totalSteps          = count($steps);
        $this->percentageCompleted = $this->calculatePercentageCompleted(
            $steps,
            $this->currentStep
        );
        $this->hasStoredData      = $this->hasStoredData();
        $this->isCompleted        = $this->isCompleted($steps);
    }

    private function calculatePercentageCompleted(array $steps, int $currentStep): int
    {
        $percentage = ($currentStep / count($steps)) * 100;
        return round($percentage);
    }

    private function isFirstStep(): bool
    {
        return $this->currentStep === 1;
    }

    private function isLastStep($steps): bool
    {
        return $this->currentStep === count($steps);
    }

    private function getNextStep($steps): ?int
    {
        if ($this->isLastStep($steps)) {
            return null;
        }
        return $this->currentStep + 1;
    }

    private function getPreviousStep(): ?int
    {
        if ($this->isFirstStep()) {
            return null;
        }
        return $this->currentStep - 1;
    }

    public function isCurrentStep($step, $queryParam): bool
    {
        return $this->getCurrentStep($queryParam) === $step;
    }

    private function getCurrentStep(string $queryParam): int
    {
        $step = get_query_var($queryParam, 1); //todo: wpservice
        if (is_numeric($step) && $step > 0) {
            return $step;
        }
        return 1;
    }

    private function isValidStep(array $steps, int $step): bool
    {
        if (array_key_exists($step, $steps)) {
            return true;
        }
        return false;
    }

    private function hasStoredData(): bool
    {
        return false;
    }

    private function isCompleted(array $steps): bool
    {
        return true;
    }
}
