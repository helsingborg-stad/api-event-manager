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
                return ['type' => 'null', 'view' => 'null'];
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

        // TODO: Conditionals
        // TODO: Repeater
        // TODO: OSM Field
        // TODO: Location field google maps/osm?
        echo '<pre>' . print_r( $field, true ) . '</pre>';die;
    }

    private function mapBasic(array $field, string $type)
    {
        return [
            'type'        => $type,
            'view'        => $type,
            'label'       => $field['label'],
            'name'        => $field['name'],
            'required'    => $field['required'] ?? false,
            'description' => $field['instructions'] ?? '',
        ];
    }

    private function mapImage(array $field): array
    {
        // TODO: imageinput component missing description
        $mapped = $this->mapBasic($field, 'image');
        $mapped['multiple']    = $field['multiple'] ?? false;
        $mapped['maxFileSize'] = $field['max_size'] ?? 0;
        $mapped['maxHeight']   = $field['max_height'] ?? 0;
        $mapped['maxWidth']    = $field['max_width'] ?? 0;

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

    private function mapTextarea(array $field)
    {
        // TODO: Max words (maxlength)?
        $mapped = $this->mapBasic($field, 'textarea');
        $mapped['placeholder'] = $field['placeholder'] ?? '';
        $mapped['value']       = $field['default_value'] ?? '';
        $mapped['rows']        = $field['rows'] ?? 5;
        $mapped['multiline']   = true;

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
        $mapped['type'] = 'radio';

        $mapped['choices'] = [];
        foreach ($field['choices'] as $key => $value) {
            $mapped['choices'][$key] = [
                'type' => $mapped['type'],
                'label' => $value,
                'required' => $mapped['required'] ?? false,
                'name' => $field['name'],
                'value' => $key,
                'checked' => ($field['default_value'] ?? '') === $key,
            ];
        }

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

        // TODO: Should we add description to select/checkbox component?
        $mapped['type']        = $field['field_type'] ?? 'checkbox';
        $mapped['terms'] = $this->structureTerms($mapped, $this->getTermsFromTaxonomy($field));

        return $mapped;
    }

    private function mapText(array $field): array
    {
        $mapped = $this->mapBasic($field, 'text');
        
        // TODO: Add maxLength to component (field)?
        // 'maxlength'     => $field['maxlength'] ?? '',
        $mapped['placeholder']                         = $field['placeholder'] ?? '';
        $mapped['value']                               = $field['default_value'] ?? '';
        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }

    private function mapTrueFalse(array $field): array
    {
        $mapped = $this->mapBasic($field, 'trueFalse');

        $mapped['checked'] = !empty($field['default_value']) ? true : false;
        $mapped['type']    = 'checkbox';

        return $mapped;
    }

    private function mapCheckbox(array $field): array
    {
        $mapped = $this->mapBasic($field, 'checkbox');
        $mapped['choices'] = [];
        foreach ($field['choices'] as $key => $value) {
            $mapped['choices'][$key] = [
                'type' => $mapped['type'],
                'label' => $value,
                'required' => $mapped['required'] ?? false,
                'name' => $field['name'],
                'value' => $key,
                'checked' => in_array($key, ($field['default_value'] ?? [])),
            ];
        }

        return $mapped;
    }

    private function mapRadio(array $field): array
    {
        $mapped = $this->mapBasic($field, 'radio');
        $mapped['choices'] = [];
        foreach ($field['choices'] as $key => $value) {
            $mapped['choices'][$key] = [
                'type' => $mapped['type'],
                'label' => $value,
                'required' => $mapped['required'] ?? false,
                'name' => $field['name'],
                'value' => $key,
                'checked' => ($field['default_value'] ?? '') === $key,
            ];
        }

        return $mapped;
    }

    private function mapSelect(array $field): array
    {
        $mapped = $this->mapBasic($field, 'select');

        $mapped['options']     = $field['choices'] ?? [];
        $mapped['preselected'] = $field['default_value'] ?? null;
        $mapped['placeholder'] = $field['placeholder'] ?? '';
        $mapped['multiple']    = $field['multiple'] ?? false;

        return $mapped;
    }

    private function structureTerms(array $mapped, array $terms): array
    {
        // TODO: Needs to be compatible with select, radio and checkbox (multiselect for select/checkbox/radio)
        $structured = [];
        foreach ($terms as $term) {
            $structured[$term->term_id] = [
                'name' => $mapped['name'],
                'type' => $mapped['type'],
                'label' => $term->name,
                'required' => !empty($mapped['required']) ? true : false,
            ];
        }

        return $structured;
    }

    private function getTermsFromTaxonomy(array $field): array
    {
        if (empty($field['taxonomy'])) {
            return [];
        }

        $terms = get_terms([
            'taxonomy'   => $field['taxonomy'],
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms)) {
            return [];
        }

        return $terms;
    }
}