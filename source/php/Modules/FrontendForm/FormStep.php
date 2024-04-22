<?php 

namespace EventManager\Modules\FrontendForm;

class FormStep {
    public int $step;
    public string $title;
    public string $description;
    public string $group;
    public object $state;
    public object $nav;

    public function __construct(int $step, array $fieldGroups)
    {
        $this->step   = $step;
        $fieldGroups  = $this->normalizeFieldGroupsIndex($fieldGroups);

        foreach($this->get($fieldGroups) as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Normalize the field groups index
     * This function makes the group array index start from 1, instead of 0.
     * This will simplify getting data representing a step from the field groups array.
     */
    private function normalizeFieldGroupsIndex(array $fieldGroups): array
    {
        if(empty($fieldGroups)) {
            throw new \Exception('Field groups must be an not empty array.');
        }
        return array_filter(array_merge(array(0), $fieldGroups));
    }

    private function get(array $fieldGroups): array
    {
        return [
            'step' => $this->step,
            'title' => $this->getFieldGroupDataItem('title', $fieldGroups),
            'description' => $this->getFieldGroupDataItem('description', $fieldGroups),
            'group' => $this->getFieldGroupKey($this->step, $fieldGroups)
        ];
    }

    private function getFieldGroupKey(int $step, array $fieldGroups): string|bool
    {
        if($step > count($fieldGroups)) {
            return false;
        }
        return $fieldGroups[$step];
    }

    private function getFieldGroupDataItem(string $targetKey, array $fieldGroups): string|int|bool {
        $groupKey = $this->getFieldGroupKey($this->step, $fieldGroups);
        $return   = "";
        array_walk($this->getFieldGroups(), function($fieldGroup) use ($groupKey, &$return, $targetKey) {
            if($fieldGroup["key"] === $groupKey) {
                $return = $fieldGroup[$targetKey];
            }
        });
        return $return;
    }

    private function getFieldGroups(): array
    {
        return acf_get_field_groups();
    }
}