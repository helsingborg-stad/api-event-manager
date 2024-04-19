<?php 

namespace EventManager\Modules\FrontendForm;

class FormStep {
    public int $step;
    public string $title;
    public string $description;
    public string $group;
    public object $state;

    public function __construct(int $step, array $fieldGroups)
    {
        $this->step   = $step;
        $fieldGroups  = $this->normalizeFieldGroupsIndex($fieldGroups);

        foreach($this->get($fieldGroups) as $key => $value) {
            $this->$key = $value;
        }
    }

    private function normalizeFieldGroupsIndex($fieldGroups): array
    {
        if(!is_array($fieldGroups)) {
            throw new \Exception('Field groups must be an not empty array.');
        }
        $result = [];
        foreach($fieldGroups as $index => $fieldGroup) {
            $result[$index+1] = $fieldGroup;
        }
        return $result;
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