<?php

namespace EventManager\Modules\FrontendForm;

use AcfService\AcfService;

class FormatSteps {
    public function __construct(private AcfService $acfService)
    {
    }

    public function formatSteps(array $steps) 
    {
        $formattedSteps = [];
        foreach ($steps as $key => $step) {
            $formattedSteps[$key]['title'] = !empty($step['formStepIncludesPostTitle']) ?
                ($step['formStepTitle'] ?? null) :
                null;

            $formattedSteps[$key]['description'] = $step['formStepIncludesPostTitle'] ?? null;
            $formattedSteps[$key]['fields'] = $this->formatStep($step);
        }

        return $formattedSteps;
    }

    public function formatStep(array $unformattedStep) 
    {
        $fieldGroups = $unformattedStep['formStepGroup'] ?? [];

        $formattedStep = [];
        foreach ($fieldGroups as $fieldGroup) {
            $fields = acf_get_fields($fieldGroup);
            foreach ($fields as $field) {
                $formattedStep[] = $this->fieldMapper($field);
            }
        }

        return $formattedStep;
    }

    private function fieldMapper(array $field)
    {
        switch ($field['type']) {
            case 'select':
                return $this->mapSelect($field);
            case 'checkbox':
                return $this->mapCheckbox($field);
            case 'true_false':
                return $this->mapTrueFalse($field);
            case 'text':
                return $this->mapText($field);  
            case 'taxonomy':
                return $this->mapTaxonomy($field);
            case 'repeater':
                return $this->mapRepeater($field);
            case 'date_picker':
                return $this->mapDatePicker($field);
            case 'time_picker':
                return $this->mapTimePicker($field);
            case 'button_group':
                return $this->mapButtonGroup($field);
            case 'open_street_map':
                return;
            case 'url':
                return $this->mapUrl($field);
            case 'textarea':
                return $this->mapTextarea($field);
            case 'radio':
                return $this->mapRadio($field);
            case 'number':
                return $this->mapNumber($field);
            case 'image':
                return $this->mapImage($field);
            
        }

        // TODO: Location field google maps/osm?
        echo '<pre>' . print_r( $field, true ) . '</pre>';die;
    }

    private function mapBasic(array $field, string $type)
    {
        return [
            'type'        => $type,
            'label'       => $field['label'],
            'name'        => $field['name'],
            'required'    => $field['required'] ?? false,
            'description' => $field['instructions'] ?? '',
        ];
    }

    private function mapImage(array $field): array
    {
        $mapped = $this->mapBasic($field, 'image');
        $mapped['multiple']    = $field['multiple'] ?? false;
        $mapped['maxFileSize'] = $field['max_size'] ?? 0;
        $mapped['maxHeight']   = $field['max_height'] ?? 0;
        $mapped['maxWidth']    = $field['max_width'] ?? 0;
        $mapped['helperText']  = $mapped['description'] ?? '';

        return $mapped;
    }

    private function mapNumber(array $field): array
    {
        // TODO: Append ex. SEK?
        $mapped = $this->mapBasic($field, 'number');
        $mapped['placeholder'] = $field['placeholder'] ?? '';
        $mapped['value']       = $field['default_value'] ?? '';
        $mapped['min']         = $field['min'] ?? null;
        $mapped['max']         = $field['max'] ?? null;

        return $mapped;
    }

    private function mapRadio(array $field): array
    {
        $mapped = $this->mapBasic($field, 'radio');
        $mapped['choices'] = $field['choices'] ?? [];
        $mapped['checked'] = $field['default_value'] ?? [];

        return $mapped;
    }

    private function mapTextarea(array $field)
    {
        // TODO: Max words (maxlength)?
        $mapped = $this->mapBasic($field, 'textarea');
        $mapped['placeholder'] = $field['placeholder'] ?? '';
        $mapped['value']       = $field['default_value'] ?? '';
        $mapped['rows']        = $field['rows'] ?? 5;

        return $mapped;
    }

    private function mapUrl(array $field): array
    {
        $mapped = $this->mapBasic($field, 'url');

        $mapped['placeholder'] = $field['placeholder'] ?? '';
        $mapped['value']       = $field['default_value'] ?? '';

        return $mapped;
    }
    
    // TODO: We do not have anything like this
    private function mapButtonGroup(array $field): array
    {
        $mapped = $this->mapBasic($field, 'buttonGroup');

        $mapped['options']     = $field['choices'] ?? [];
        $mapped['preselected'] = $field['default_value'] ?? null;
        $mapped['placeholder'] = $field['placeholder'] ?? '';
        
        return $mapped;
    }

    private function mapTimePicker(array $field): array
    {
        $mapped = $this->mapBasic($field, 'time');

        $mapped['placeholder'] = $field['placeholder'] ?? '';
        $mapped['value']       = $field['default_value'] ?? '';
        $mapped['minTime']     = $field['min_time'] ?? '';
        $mapped['maxTime']     = $field['max_time'] ?? '';

        return $mapped;
    }

    private function mapDatePicker(array $field): array
    {
        $mapped = $this->mapBasic($field, 'date');
        // TODO: Do we need to set format?
        $mapped['placeholder'] = $field['placeholder'] ?? '';
        $mapped['value']       = $field['default_value'] ?? '';
        $mapped['minDate']     = $field['min_date'] ?? '';
        $mapped['maxDate']     = $field['max_date'] ?? '';

        return $mapped;
    }

    private function mapRepeater(array $field): array
    {
        $subFields = [];
        foreach ($field['sub_fields'] as $subField) {
            $subFields[] = $this->fieldMapper($subField);
        }

        $mapped = $this->mapBasic($field, 'repeater');

        $mapped['fields'] = $subFields;
        $mapped['min']    = $field['min'] ?? 0;
        $mapped['max']    = $field['max'] ?? 100;

        return $mapped;
    }
    
    private function mapTaxonomy(array $field): array
    {
        $mapped = $this->mapBasic($field, 'taxonomy');

        // TODO: Should we add description to select component (select)?
        $mapped['options'] = $field['choices'] ?? [];
        $mapped['view']    = $field['field_type'] ?? 'checkbox';
        $mapped['terms']   = $this->getTermsFromTaxonomy($field['taxonomy'] ?? '');
        $mapped['preselected'] = $field['default_value'] ?? null;
        $mapped['placeholder'] = $field['placeholder'] ?? '';
   
        return $mapped;
    }

    private function mapText(array $field): array
    {
        $mapped = $this->mapBasic($field, 'text');
        
        // TODO: Add maxLength to component (field)?
        // 'maxlength'     => $field['maxlength'] ?? '',
        $mapped['placeholder'] = $field['placeholder'] ?? '';
        $mapped['value']       = $field['default_value'] ?? '';

        return $mapped;
    }

    private function mapTrueFalse(array $field): array
    {
        $mapped = $this->mapBasic($field, 'trueFalse');

        $mapped['checked'] = $field['default_value'] ?? false;

        return $mapped;
    }

    private function mapCheckbox(array $field): array
    {
        $mapped = $this->mapBasic($field, 'checkbox');
        $mapped['choices'] = $field['choices'] ?? [];
        $mapped['checked'] = $field['default_value'] ?? [];

        return $mapped;
    }

    private function mapSelect(array $field): array
    {
        $mapped = $this->mapBasic($field, 'select');

        $mapped['options']     = $field['choices'] ?? [];
        $mapped['preselected'] = $field['default_value'] ?? null;
        $mapped['placeholder'] = $field['placeholder'] ?? '';
        $mapped['helperText']  = $mapped['description'] ?? '';
        $mapped['multiple']    = $field['multiple'] ?? false;

        return $mapped;
    }

    private function getTermsFromTaxonomy(string $slug): array
    {
        $terms = get_terms([
            'taxonomy'   => $slug,
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms)) {
            return [];
        }

        return array_map(function ($term) {
            return [
                'id'    => $term->term_id,
                'label' => $term->name,
                'value' => $term->slug,
            ];
        }, $terms);
    }
}