<?php

/** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace EventManager\Modules\FrontendForm;

use EventManager\Modules\FrontendForm\FormStep;
use ComponentLibrary\Init as ComponentLibraryInit;
use Throwable;

use AcfService\Contracts\EnqueueUploader;
use AcfService\Contracts\Form;
use AcfService\Contracts\FormHead;
use AcfService\Contracts\GetFieldGroups;
use AcfService\Implementations\NativeAcfService;

use WpService\Contracts\EnqueueStyle;
use WpService\Implementations\NativeWpService;
use WpService\Contracts\__;
use WpService\Contracts\AddFilter;
use WpService\Contracts\AddAction;
use WpService\Contracts\IsUserLoggedIn;
use WpService\Contracts\GetQueryVar;
use WpService\Contracts\GetPostType;
use WpService\Contracts\GetPostTypeObject;
use WpService\Contracts\GetPermalink;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\UpdatePostMeta;
use WpService\Implementations\WpServiceWithTypecastedReturns;

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
    public $cacheTtl = 0;

    private $formStepQueryParam     = 'step'; // The query parameter for the form steps.
    private $formIdQueryParam       = 'formid'; // The query parameter for the form id.
    private $formTokenQueryParam    = 'token';  // The query parameter for the form token.

    private $formSecurity = null; // The form security service.

    private $formPostType   = null; // The post type for the form (set by config).
    private $formPostStatus = null; // The post status for the form (set by config).

    private $blade = null;

    private EnqueueStyle &__&IsUserLoggedIn&AddFilter&AddAction&GetQueryVar&GetPostType&GetPostTypeObject&GetPermalink&GetPostMeta&UpdatePostMeta $wpService;
    private FormHead&EnqueueUploader&Form&GetFieldGroups $acfService;

    public function init(): void
    {
        $this->wpService    = new WpServiceWithTypecastedReturns(new NativeWpService());
        $this->acfService   = new NativeAcfService();
        $this->formSecurity = new FormSecurity(
            $this->wpService,
            $this->formIdQueryParam, 
            $this->formTokenQueryParam
        );

        $this->nameSingular = $this->wpService->__('Event Form');
        $this->namePlural   = $this->wpService->__('Event Forms');
        $this->description  = $this->wpService->__('Module for creating public event form');

        $this->wpService->addFilter('query_vars', [$this, 'registerFormQueryVars']);
        $this->wpService->addFilter('acf/load_field/name=formStepGroup', [$this, 'addOptionsToGroupSelect']);

        //TODO: Maybe move to form secority. Needs implement Hookable interface? 
        $this->wpService->addAction('save_post_'. "post", [$this->formSecurity, 'saveFormEditToken'], 10, 3);
    }

    /**
     * Check if the acf-field confuguration is currently being edited.
     * @return bool
     */
    private function isInEditMode(): bool 
    {
        global $post;
        if (is_a($post, 'WP_Post') && in_array($this->wpService->getPostType($post), array('acf-field', 'acf-field-group'))) {
            return true;
        }
        return false;
    }

    /**
     * Retrieves the form data.
     * 
     * This method retrieves the form data by checking if the form is empty, protected, or needs a tokenized request.
     * 
     * @return array The form data.
     */
    public function data(): array
    {
        //Needs to be called, otherwise a notice will be thrown.
        $fields = (object) $this->getFields();

        //Empty form. Show message.
        if ($fields->formSteps == false) {
            return array_merge(
                $this->defaultDataResponse(),
                [
                'empty' => $this->renderView('partials.message', [
                    'text' => $this->wpService->__(
                        'No steps defined. Please Add some steps, to enable this form functionality.'
                    ),
                    'icon' => ['name' => 'info'],
                    'type' => 'info'
                ])
                ]
            );
        }

        //Protected form. Show message.
        if ($fields->isPublicForm == false && !$this->wpService->isUserLoggedIn()) {
            return array_merge(
                $this->defaultDataResponse(),
                [
                'empty' => $this->renderView('partials.message', [
                    'title' => $this->wpService->__('Access denied'),
                    'text' => $this->wpService->__('This form is protected. Please log in to access it.'),
                    'icon' => ['name' => 'info'],
                    'type' => 'warning'
                ])
                ]
            );
        }

        //If we consider this form to need a tokenized request, and the token is missing, show message.
        if($this->formSecurity->needsTokenizedRequest() && !$this->formSecurity->hasTokenizedAccess()) {
            return array_merge(
                $this->defaultDataResponse(),
                [
                'empty' => $this->renderView('partials.message', [
                    'title' => $this->wpService->__('Access denied'),
                    'text' => $this->wpService->__('Please use the edit link in the e-mail we sent you when you first created the post.'),
                    'icon' => ['name' => 'info'],
                    'type' => 'warning'
                ])
                ]
            );
        }

        //Define form steps
        $steps = [];
        foreach ($fields->formSteps as $index => $fieldGroup) {
            $steps[$index + 1] = new FormStep(
                $index + 1,
                $fieldGroup
            );
        }

        //Set form state
        $formState = new FormState(
            $steps,
            $this->wpService->getQueryVar($this->formStepQueryParam, 1)
        );

        //Decorate step with state, and link
        foreach ($steps as &$step) {
            $step->state = $stepState = new FormStepState(
                $step,
                $formState,
                $steps
            );

            $step->nav = new FormStepNav(
                $step,
                $stepState,
                $steps,
                $this->wpService->getPermalink(),
                [
                    'formid' => $this->wpService->getQueryVar(
                        $this->formIdQueryParam, 
                        "%post_id%"
                    ),
                    'step'   => null,
                    'token'  => $this->wpService->getQueryVar(
                        $this->formTokenQueryParam, 
                        null
                    )
                ]
            );
        }

        //Invalid step: Show error message
        if (!$formState->isValidStep) {
            return array_merge(
                $this->defaultDataResponse(),
                [
                'error' => $this->renderView('partials.message', [
                    'text' => $this->wpService->__('Whoops! It looks like we ran out of form.'),
                    'icon' => ['name' => 'error'],
                    'type' => 'warning'
                ])
                ]
            );
        }

        //Get current step form
        $self = $this; //Avoids lexical scope issues
        $form = (function ($step) use ($self) {
            $self->getForm($step, $self);
        });

        return array_merge(
            $this->defaultDataResponse(),
            [
                'error'        => false,
                'steps'        => $steps,
                'state'        => $formState,
                'form'         => $form,
                'formSettings' => (object) [
                    'postType'   => $fields->saveToPostType ?? "post",
                    'postStatus' => $fields->saveToPostTypeStatus ?? "draft"
                ],
                'summary' => (object) [
                    'isEnabled' => $fields->hasSummaryStep ?? false,
                    'title'     => $fields->summaryTitle,
                    'lead'      => $fields->summaryLead
                ],
                'lang'         => $this->getLang()
            ]
        );
    }

    /**
     * Retrives default values for keys used in the form display.
     *
     * @return array The default keys and values.
     */
    private function defaultDataResponse(): array
    {
        return [
            'empty' => false,
            'error' => false
        ];
    }

    /**
     * Retrives varous text strings for the form.
     *
     * @return object The (translated) text strings.
     */
    private function getLang(): object
    {
        $disclaimer = $this->wpService->__(
            <<<EOD
            By submitting this form, you're agreeing to our terms and conditions. 
            You're also consenting to us processing your personal data in line with GDPR regulations, 
            and confirming that you have full rights to use all provided content.
            EOD
        );

        return (object) [
            'disclaimer' => $disclaimer,
            'edit'       => $this->wpService->__('Edit'),
            'submit'     => $this->wpService->__('Submit'),
            'previous'   => $this->wpService->__('Previous'),
            'next'       => $this->wpService->__('Save and go to next step', 'api-event-manager'),
            'of'         => $this->wpService->__('of'),
            'step'       => $this->wpService->__('Step'),
            'completed'  => $this->wpService->__('Completed')
        ];
    }

    public function template(): string
    {
        return 'frontend-form.blade.php';
    }

    public function script(): void
    {
        $this->acfService->formHead();
        $this->acfService->enqueueUploader();
    }

    public function style(): void
    {
        $this->wpService->enqueueStyle('event-manager-frontend-form');
    }

    /**
     * Retrieves the form ID or "new_post".
     *
     * This method returns the form ID by retrieving the value of the specified query parameter.
     * If the query parameter is not set, the method will return a string representing create_new_post.
     *
     * @return string|int The form ID. Or "new_post" if the query parameter is not set.
     */
    private function getFormId(): string|int
    {
        return $this->wpService->getQueryVar($this->formIdQueryParam, 'new_post');
    }

    /**
     * Retrieves the form step.
     *
     * This method returns the form step by retrieving the value of the specified query parameter.
     *
     * @return int The form step.
     */
    public function getForm($step, $self): void
    {
        //Message when form is sent.
        $htmlUpdatedMessage = $self->renderView('partials.message', [
            'text' => '%s',
            'icon' => ['name' => 'info'],
            'type' => 'sucess'
        ]);

        $htmlSubmitButton = $self->renderView(
            'partials.button-wrapper',
            ['step' => $step, 'lang' => $self->getLang()]
        );

        $this->acfService->form([
            'post_id'               => $this->getFormId(),
            'return'                => $step->nav->next ?? false, // Add form result page here
            'post_title'            => $step->properties->includePostTitle,
            'post_content'          => false,
            'field_groups'          => is_array($step->group) ? $step->group :  [
                $step->group
            ],
            'form_attributes'       => ['class' => 'acf-form js-form-validation js-form-validation'],
            'uploader'              => 'basic',
            'updated_message'       => $this->wpService->__(
                "The event has been submitted for review. You will be notified when the event has been published."
            ),
            'html_updated_message'  => $htmlUpdatedMessage,
            'html_submit_button'    => $htmlSubmitButton,
            'new_post'              => [
                'post_type'   => $self->formPostType,
                'post_status' => $self->formPostStatus
            ],
            'instruction_placement' => 'label',
            'submit_value'          => $this->wpService->__('Create Event')
        ]);
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
            $this->blade->errorHandler($e)->print();
        }

        return false;
    }

    /**
     * Registers multiple query variables for the form in order to be able to access them in get_query_var.
     *
     * This method takes an array of registered query variables and adds
     * the form step, form ID, and form token keys to it.
     *
     * @param array $registeredQueryVars The array of registered query variables.
     * @return array The updated array of registered query variables.
     */
    public function registerFormQueryVars(array $registeredQueryVars): array
    {
        return array_merge(
            $registeredQueryVars,
            [
                $this->formStepQueryParam,
                $this->formIdQueryParam,
                $this->formTokenQueryParam
            ]
        );
    }

    /**
     * Adds the field groups to the select field.
     *
     * This method takes a field and adds the field groups to the select field.
     *
     * @param array $field The field to add the field groups to.
     * @return array The updated field.
     */
    public function addOptionsToGroupSelect($field)
    {
        if($this->isInEditMode() === true) {
            return $field;
        }

        $field['choices'] = array();

        // Get all field groups, filter out all that are connected to a post type.
        $groups = $this->acfService->getFieldGroups();
        $groups = array_filter($groups, function ($item) {
            return isset($item['location'][0][0]['param']) && $item['location'][0][0]['param'] === 'post_type';
        });

        // Add groups to the select field
        if (is_array($groups) && !empty($groups)) {
            foreach ($groups as $group) {
                $field['choices'][$group['key']] = function ($name, $postTypeName) {
                    $postTypeName = $this->wpService->getPostTypeObject($postTypeName);
                    return (!empty($postTypeName->label) ? "$postTypeName->label: " : "") . $name;
                };
            }
        }
        return $field;
    }
}
