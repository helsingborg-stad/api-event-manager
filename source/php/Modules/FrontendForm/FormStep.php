<?php

namespace EventManager\Modules\FrontendForm;

class FormStep
{
    public int $step;
    public string $title;
    public string $description;
    public array $group;
    public object $properties;
    public object $state;
    public object $nav;

    public function __construct(int $step, array $acfFieldGroup)
    {
        $this->step        = $step;
        $this->title       = $acfFieldGroup['formStepTitle'] ?? '';
        $this->description = $acfFieldGroup['formStepContent'] ?? '';
        $this->group       = $acfFieldGroup['formStepGroup'] ?? [];

        //Set properties
        $this->addProperty(
            'includePostTitle',
            $acfFieldGroup['formStepIncludesPostTitle'] ?? false
        );
    }

    private function addProperty(string $name, $value): void
    {
        if (!isset($this->properties)) {
            $this->properties = new \stdClass();
        }
        $this->properties->$name = $value;
    }
}
