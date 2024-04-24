<?php

/** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace EventManager\Modules\FrontendForm;

use EventManager\Modules\FrontendForm\FormStep;

use ComponentLibrary\Init as ComponentLibraryInit;
use WpService\Contracts\EnqueueStyle;
use WpService\Contracts\WPServiceFactory;
use EventManager\Resolvers\FileSystem\ManifestFilePathResolver;
use EventManager\Resolvers\FileSystem\StrictFilePathResolver;
use EventManager\Services\FileSystem\FileSystemFactory;
use WpService\Implementations\NativeWpService;
use PharIo\Manifest\Manifest;
use Throwable;

/**
 * @property string $description
 * @property string $namePlural
 * @property string $nameSingular
 * @property int $ID
 */
class FrontendForm extends \Modularity\Module
{
    public $slug     = 'event-form';
    public $supports = [];
    public $hidden   = false;

    // The field groups that should be displayed in the form.
    private $fieldGroups = [
        'group_661e41bb1781f', // Test step 1
        'group_661e425070deb', // Test step 2
        'group_65a115157a046'
    ];

    private $formStepQueryParam = 'step'; // The query parameter for the form steps.
    private $formIdQueryParam   = 'formid'; // The query parameter for the form id.

    private $formPostType = 'event'; // The post type for the form.
    private $formPostStatus = 'draft'; // The post status for the form.

    private $blade = null;

    private EnqueueStyle $wpService;

    public function init(): void
    {
        $this->nameSingular = __('Event Form', 'api-event-manager');
        $this->namePlural   = __('Event Forms', 'api-event-manager');
        $this->description  = __('Module for creating public event form', 'api-event-manager');

        $this->wpService = new NativeWpService(); // TODO: use custom modularity middleware.

        add_filter('query_vars',[$this, 'registerFormStepQueryVar']); // add from wpservice
        add_filter('query_vars',[$this, 'registerFormIdQueryVar']); // add from wpservice

        //TODO: Resolve issue with modularity style/script not loading in drafts.
        add_action('wp_enqueue_scripts', function() {
            $this->wpService->enqueueStyle('event-manager-frontend-form');
        });
    }

    public function data(): array
    {
        //Needs to be called, otherwise a notice will be thrown.
        $fields = $this->getFields(); 

        // TODO: Make this a setting
        $postType = "event"; 
        $postStatus = "draft";

        //Define form steps 
        $steps = [];
        foreach($this->fieldGroups as $index => $fieldGroup) {
            $steps[$index + 1] = new FormStep(
                $index + 1, 
                $this->fieldGroups
            );
        }

        //Set form state
        $formState = new FormState(
            $steps, 
            $this->formStepQueryParam
        );

        //Decorate step with state, and link
        foreach($steps as &$step) {
            $step->state = $stepState = new FormStepState(
                $step, 
                $formState,
                $steps
            );

            $step->nav = new FormStepNav(
                $step, 
                $stepState,
                $steps,
                get_permalink(),
                [
                    'formid' => get_query_var($this->formIdQueryParam, "%%postid%%"),
                    'step' => null
                ]
            );
        }

        //Invalid step: Show error message
        if(!$formState->isValidStep) {
            return [
                'error' => $this->renderView('partials.message', [
                    'text' => __('Whoops! It looks like we ran out of form.', 'api-event-manager'),
                    'icon' => ['name' => 'error'],
                    'type' => 'warning'
                ])
            ];
        } else {
            $data['error'] = false;
        }

        //Get current step form
        $self = $this; //Avoids lexical scope issues
        $form = (function ($step) use ($self) {

            //Message when form is sent.
            $htmlUpdatedMessage = $self->renderView('partials.message', [
                'text' => '%s',
                'icon' => ['name' => 'info'],
                'type' => 'sucess'
            ]);

            $htmlSubmitButton = $self->renderView(
                'partials.button-wrapper', ['step' => $step]
            );

            acf_form([
                'post_id'               => $step->state->isFirst ? 'new_post' : false,
                'return'                => $step->nav->next ?? false, // Add form result page here
                'post_title'            => $step->state->isFirst ? true : false,
                'post_content'          => false,
                'field_groups'          => [
                    $step->group
                ],
                'form_attributes' => ['class' => 'acf-form js-form-validation js-form-validation'],
                'uploader'              => 'basic',
                'updated_message'       => __("The event has been submitted for review. You will be notified when the event has been published.", 'acf'),
                'html_updated_message'  => $htmlUpdatedMessage,
                'html_submit_button'    => $htmlSubmitButton,
                'new_post'              => [
                    'post_type'   => $self->formPostType,
                    'post_status' => $self->formPostStatus
                ],
                'instruction_placement' => 'field',
                'submit_value'          => __('Create Event', 'api-event-manager')
            ]);
        });
        
        $lang = (object) [
            'disclaimer' => __("By submitting this form, you're agreeing to our terms and conditions. You're also consenting to us processing your personal data in line with GDPR regulations, and confirming that you have full rights to use all provided content.", 'api-event-manager'),
            'edit' => __('Edit', 'api-event-manager'),
            'submit' => __('Submit', 'api-event-manager'),
            'previous' => __('Previous', 'api-event-manager'),
            'next' => __('Next', 'api-event-manager')
        ];

        return [
            'error' => false,
            'steps' => $steps,
            'state' => $formState,
            'form'  => $form,
            'formSettings' => (object) [
                'postType' => $postType,
                'postStatus' => $postStatus
            ],
            'lang'  => $lang
        ];
    }

    private function getQueryParam($key, $default = ""): string
    {
        return get_query_var($key, $default);
    }

    public function template(): string
    {
        return 'frontend-form.blade.php';
    }

    public function script(): void
    {
        acf_form_head();
        acf_enqueue_uploader();
    }

    public function style(): void
    {
        //$this->wpService->enqueueStyle('event-manager-frontend-form');
    }

    /**
     * @param $view
     * @param array $data
     * @return bool
     * @throws \Exception
     *
     */
    public function renderView($view, $data = array()): string
    {
        if (is_null($this->blade)) {
            $this->blade = (
            new ComponentLibraryInit([])
            )->getEngine();
        }

        try {
            return $this->blade->makeView($view, $data, [], $this->templateDir)->render();
        } catch (Throwable $e) {
            $sourceFileContentsAsArray = file($e->getFile()); 

            printf('
                <style>
                    .error-table {
                        font-family: sans-serif;
                        width: calc(100%% - 32px);
                        border-collapse: collapse;
                        margin: 16px;
                        background: #fff;
                        box-shadow: 0 0 16px rgb(0,0,0,.25);
                        border-radius: 4px;
                        overflow: hidden;
                        outline: 2px solid #f00;
                        outline-offset: -2px;
                        box-sizing: border-box;
                    }

                    .error-table pre {
                        font-family: inherit;
                        white-space: pre-wrap;
                        margin: 0;
                    }
                    
                    .error-table, 
                    .error-table tr,
                    .error-table td {
                        border: 2px solid #f00;
                        padding: 8px 16px;
                    }
                    .error-table td.stacktrace {
                        padding: 16px;
                    }
                </style>

                <table class="error-table">
                    <tr>
                        <td><strong>Error Message:</strong></td>
                        <td>%s</td>
                    </tr>
                    <tr>
                        <td><strong>Error Line:</strong></td>
                        <td>%s</td>
                    </tr>
                    <tr>
                        <td><strong>Error File:</strong></td>
                        <td>%s</td>
                    </tr>
                    <tr>
                        <td><strong>Source code (line %s):</strong></td>
                        <td>
                            <pre>%s</pre>
                        </td>
                    </tr>
                    <tr>
                    <td><strong>Stacktrace:</strong></td>
                        <td class="stacktrace">
                            <pre>%s</pre>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>View paths:</strong></td>
                        <td>%s</td>
                    </tr>
                </table>',
                $e->getMessage(),
                $e->getLine(),
                $e->getFile(),
                $e->getLine(),
                htmlspecialchars(trim($sourceFileContentsAsArray[$e->getLine()-2])) . "<br/>" . 
                "<mark>" . htmlspecialchars(trim($sourceFileContentsAsArray[$e->getLine()-1])) . "</mark><br/>" . 
                htmlspecialchars(trim($sourceFileContentsAsArray[$e->getLine()])),
                $e->getTraceAsString(),
                implode(PHP_EOL, $this->viewPaths ?? [])
            );
        }

        return false;
    }

    /**
     * Registers the form step query variable.
     *
     * This method takes an array of registered query variables and adds the form step key to it.
     *
     * @param array $registeredQueryVars The array of registered query variables.
     * @return array The updated array of registered query variables.
     */
    public function registerFormStepQueryVar(array $registeredQueryVars): array {
        return $registeredQueryVars = array_merge(
            $registeredQueryVars,
            [$this->formStepQueryParam]
        );
    }

    /**
     * Registers the form id query variable.
     *
     * This method takes an array of registered query variables and adds the form id key to it.
     *
     * @param array $registeredQueryVars The array of registered query variables.
     * @return array The updated array of registered query variables.
     */
    public function registerFormIdQueryVar(array $registeredQueryVars): array {
        return $registeredQueryVars = array_merge(
            $registeredQueryVars,
            [$this->formIdQueryParam]
        );
    }

}

