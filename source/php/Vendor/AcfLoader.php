<?php

namespace HelsingborgsStad;

class AcfLoader
{
    protected $exportFolder;
    protected $exportPosts = array();
    protected $textdomain;

    public function __construct()
    {
        add_action('acf/update_field_group', array($this, 'export'));
        add_action('acf/delete_field_group', array($this, 'deleteExport'));
    }

    /**
     * Import (require) acf export files
     * @return boolean
     */
    public function import()
    {
        $files = glob($this->exportFolder . '*.php');

        if (empty($files)) {
            return false;
        }

        foreach ($files as $file) {
            require_once $file;
        }

        return true;
    }

    /**
     * Deletes export file for deleted fieldgroup
     * @param  array $fieldgroup
     * @return boolean
     */
    public function deleteExport($fieldgroup)
    {
        $filename = $this->getExportFilename($fieldgroup);

        if (!file_exists($this->exportFolder . $filename)) {
            return true;
        }

        unlink($this->exportFolder . $filename);
        return true;
    }

    /**
     * Export all fieldgroups in exportPosts list
     * @return void
     */
    public function exportAll()
    {
        foreach ($this->exportPosts as $post) {
            $this->export(acf_get_field_group($post));
        }
    }

    /**
     * Does the actual export of the php fields
     * @param  array $fieldgroup  Fieldgroup data
     * @return string             Path to exported file
     */
    public function export(array $fieldgroup)
    {
        $l10nBefore = acf_get_setting('l10n');
        $l10nVarExportBefore = acf_get_setting('l10n_var_export');
        $l10nTextdomainBefore = acf_get_setting('l10n_textdomain');

        if ($this->textdomain) {
            acf_update_setting('l10n', true);
            acf_update_setting('l10n_var_export', true);
            acf_update_setting('l10n_textdomain', $this->textdomain);
        }

        // Bail if the fieldgroup shouldn't be exported
        if (!in_array($fieldgroup['ID'], $this->exportPosts)) {
            return;
        }

        $code = $this->generate($fieldgroup['ID']);
        $filename = $this->getExportFilename($fieldgroup);

        $file = fopen($this->exportFolder . $filename, 'w');
        fwrite($file, $code);
        fclose($file);

        if ($this->textdomain) {
            acf_update_setting('l10n', $l10nBefore);
            acf_update_setting('l10n_var_export', $l10nVarExportBefore);
            acf_update_setting('l10n_textdomain', $l10nTextdomainBefore);
        }

        return $this->exportFolder . $filename;
    }

    /**
     * Get filename for the export file
     * @param  array $fieldgroup Fieldgroup data
     * @return string
     */
    public function getExportFilename($fieldgroup)
    {
        if ($key = array_search($fieldgroup['ID'], $this->exportPosts)) {
            return rtrim($key, '.php') . '.php';
        }

        return sanitize_title($fieldgroup['title']) . '.php';
    }

    /**
     * Generates PHP exportcode for a fieldgroup
     * @param  int    $fieldgroupId
     * @return string
     */
    protected function generate(int $fieldgroupId)
    {
        $strReplace = array(
            "  "      => "    ",
            "!!\'"    => "'",
            "'!!"     => "",
            "!!'"     => "",
            "array (" => "array(",
            " => \n" => " => "
        );

        $pregReplace = array(
            '/([\t\r\n]+?)array/'   => 'array',
            '/[0-9]+ => array/'     => 'array',
            '/=>(\s+)array\(/'       => '=> array('
        );

        $fieldgroup = $this->getFieldgroupParams($fieldgroupId);

        $code = var_export($fieldgroup, true);
        $code = str_replace(array_keys($strReplace), array_values($strReplace), $code);
        $code = preg_replace(array_keys($pregReplace), array_values($pregReplace), $code);

        $export = "<?php \n\r\n\rif (function_exists('acf_add_local_field_group')) {\n\r";
        $export .= "    acf_add_local_field_group({$code});";
        $export .= "\n\r}";

        return $export;
    }

    /**
     * Get exportable fieldgroup params
     * @param  int    $fieldgroupId
     * @return array
     */
    public function getFieldgroupParams(int $fieldgroupId)
    {
        // Get the fieldgroup
        $fieldgroup = acf_get_field_group($fieldgroupId);

        // Bail if fieldgroup is empty
        if (empty($fieldgroup)) {
            trigger_error('The fieldgroup with id "' . $fieldgroupId . '" is empty.', E_USER_WARNING);
            return '';
        }

        $fieldgroup['title'] = acf_translate($fieldgroup['title']);

        // Get the fields in the fieldgroup
        $fieldgroup['fields'] = acf_get_fields($fieldgroup);
        foreach ($fieldgroup['fields'] as &$field) {
            $keys = array(
                'default_value',
                'placeholder',
                'button_label',
                'append',
                'prepend'
            );

            foreach ($keys as $key) {
                if (!isset($field[$key])) {
                    continue;
                }

                $field[$key] = acf_translate($field[$key]);
            }
        }

        // Preapre for export
        return acf_prepare_field_group_for_export($fieldgroup);
    }

    /**
     * Set exports folder
     * @param string      $folder  Path to exports folder
     * @return void
     */
    public function setExportFolder(string $folder)
    {
        $folder = trailingslashit($folder);

        if (!file_exists($folder)) {
            trigger_error('The export folder (' . $folder .') can not be found. Exports will not be saved.', E_USER_WARNING);
        }

        if (!is_writable($folder)) {
            trigger_error('The export folder (' . $folder .') is not writable. Exports will not be saved.', E_USER_WARNING);
        }

        $this->exportFolder = $folder;
    }

    /**
     * Sets which acf-fieldgroups postids to autoexport
     * @param  array  $ids
     * @return void
     */
    public function autoExport(array $ids)
    {
        $this->exportPosts = array_replace($this->exportPosts, $ids);
        $this->exportPosts = array_unique($this->exportPosts);
    }

    /**
     * Sets the textdomain to use for field translations
     * @param string $textdomain
     */
    public function setTextdomain($textdomain)
    {
        $this->textdomain = $textdomain;
    }
}
