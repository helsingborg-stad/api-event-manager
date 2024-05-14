<?php

namespace EventManager\Modules\FrontendForm;

class FormStep
{
    public int $step;
    public string $title;
    public string $description;
    public string $group;
    public object $state;
    public object $nav;

    public function __construct(int $step, array $acfFieldGroup)
    {
        $this->step        = $step;
        $this->title       = $acfFieldGroup['formStepTitle'] ?? '';
        $this->description = $acfFieldGroup['formStepContent'] ?? '';
        $this->group       = $acfFieldGroup['formStepGroup'] ?? '';
    }
}
