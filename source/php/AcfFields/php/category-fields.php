<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_66436fdd0f112',
    'title' => __('Category', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_65fd4c7a55b91',
            'label' => __('Event Type', 'api-event-manager'),
            'name' => 'type',
            'aria-label' => '',
            'type' => 'radio',
            'instructions' => __('What category do you feel accurately reflects your event\'s purpose? This gives a brief overview of the event\'s content. If you\'re unsure, you can opt for the generic label \'event\'.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'choices' => array(
                'Event' => __('Event', 'api-event-manager'),
                'BusinessEvent' => __('Business Event', 'api-event-manager'),
                'ChildrensEvent' => __('Childrens Event', 'api-event-manager'),
                'ComedyEvent' => __('Comedy Event', 'api-event-manager'),
                'CourseInstance' => __('CourseInstance', 'api-event-manager'),
                'DanceEvent' => __('Dance Event', 'api-event-manager'),
                'DeliveryEvent' => __('Delivery Event', 'api-event-manager'),
                'EducationEvent' => __('Education Event', 'api-event-manager'),
                'EventSeries' => __('Event Series', 'api-event-manager'),
                'ExhibitionEvent' => __('Exhibition Event', 'api-event-manager'),
                'Festival' => __('Festival', 'api-event-manager'),
                'FoodEvent' => __('Food Event', 'api-event-manager'),
                'Hackathon' => __('Hackathon', 'api-event-manager'),
                'LiteraryEvent' => __('Literary Event', 'api-event-manager'),
                'MusicEvent' => __('Music Event', 'api-event-manager'),
                'PublicationEvent' => __('Publication Event', 'api-event-manager'),
                'SaleEvent' => __('Sale Event', 'api-event-manager'),
                'ScreeningEvent' => __('Screening Event', 'api-event-manager'),
                'SocialEvent' => __('Social Event', 'api-event-manager'),
                'SportsEvent' => __('Sports Event', 'api-event-manager'),
                'TheaterEvent' => __('Theater Event', 'api-event-manager'),
                'VisualArtsEvent' => __('VisualArts Event', 'api-event-manager'),
            ),
            'default_value' => __('Event', 'api-event-manager'),
            'return_format' => 'value',
            'allow_null' => 0,
            'other_choice' => 0,
            'layout' => 'horizontal',
            'save_other_choice' => 0,
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'event',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'left',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
    'show_in_rest' => 1,
    'acfe_display_title' => '',
    'acfe_autosync' => '',
    'acfe_form' => 0,
    'acfe_meta' => '',
    'acfe_note' => '',
));
}