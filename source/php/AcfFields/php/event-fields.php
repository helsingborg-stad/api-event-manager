<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_65a115157a046',
    'title' => __('Event Fields', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_66106dc161830',
            'label' => __('Image', 'api-event-manager'),
            'name' => '_thumbnail_id',
            'aria-label' => '',
            'type' => 'image',
            'instructions' => __('Enhance your event by including a high-resolution image in <strong>JPG format</strong>. As the initial point of contact, the image plays a pivotal role in capturing users\' attention, making it essential for attracting attendees.', 'api-event-manager'),
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 1,
            'return_format' => 'array',
            'library' => 'uploadedTo',
            'min_width' => '',
            'min_height' => '',
            'min_size' => '',
            'max_width' => '',
            'max_height' => '',
            'max_size' => '',
            'mime_types' => 'jpg, jpeg',
            'preview_size' => 'medium',
        ),
        1 => array(
            'key' => 'field_65fd4c7a55b91',
            'label' => __('Event Type', 'api-event-manager'),
            'name' => 'type',
            'aria-label' => '',
            'type' => 'select',
            'instructions' => __('What category do you feel accurately reflects your event\'s purpose? This gives a brief overview of the event\'s content. If you\'re unsure, you can opt for the generic label \'event\'.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
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
            'multiple' => 0,
            'allow_null' => 0,
            'ui' => 0,
            'ajax' => 0,
            'placeholder' => '',
        ),
        2 => array(
            'key' => 'field_65a6206610d45',
            'label' => __('Short description', 'api-event-manager'),
            'name' => 'description',
            'aria-label' => '',
            'type' => 'textarea',
            'instructions' => __('Enter a brief, enticing description of your event. Make it irresistible - an event not to be missed!', 'api-event-manager'),
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'maxlength' => '',
            'rows' => '',
            'placeholder' => '',
            'new_lines' => '',
        ),
        3 => array(
            'key' => 'field_65a115151a872',
            'label' => __('About', 'api-event-manager'),
            'name' => 'about',
            'aria-label' => '',
            'type' => 'textarea',
            'instructions' => __('Provide essential information about your event, including featured speakers or performers, activities, and any other pertinent details.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'maxlength' => '',
            'rows' => 12,
            'placeholder' => '',
            'new_lines' => '',
        ),
        4 => array(
            'key' => 'field_65f14f7826756',
            'label' => __('Accessability information', 'api-event-manager'),
            'name' => 'accessabilityInformation',
            'aria-label' => '',
            'type' => 'textarea',
            'instructions' => __('Include details on event accessibility for all attendees, covering wheelchair access, parking, elevators, sign language interpreters, and other accommodations.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'maxlength' => '',
            'rows' => '',
            'placeholder' => '',
            'new_lines' => '',
        ),
        5 => array(
            'key' => 'field_65a5319a9d01d',
            'label' => __('Status', 'api-event-manager'),
            'name' => 'eventStatus',
            'aria-label' => '',
            'type' => 'select',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 1,
            'is_privately_hidden' => 0,
            'choices' => array(
                'https://schema.org/EventScheduled' => __('Schemalagt', 'api-event-manager'),
                'https://schema.org/EventRescheduled' => __('Omplanerat', 'api-event-manager'),
                'https://schema.org/EventCancelled' => __('Inställt', 'api-event-manager'),
                'https://schema.org/EventPostponed' => __('Framskjutet', 'api-event-manager'),
            ),
            'default_value' => __('https://schema.org/EventScheduled', 'api-event-manager'),
            'return_format' => 'value',
            'multiple' => 0,
            'allow_null' => 0,
            'ui' => 0,
            'ajax' => 0,
            'placeholder' => '',
        ),
        6 => array(
            'center_lat' => 59.1083083,
            'center_lng' => 13.8208008,
            'zoom' => 6,
            'height' => 400,
            'return_format' => 'raw',
            'allow_map_layers' => 0,
            'max_markers' => 1,
            'layers' => array(
                0 => 'OpenStreetMap.Mapnik',
            ),
            'key' => 'field_65a245c3a4062',
            'label' => __('Location', 'api-event-manager'),
            'name' => 'location',
            'aria-label' => '',
            'type' => 'open_street_map',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'leaflet_map' => '{"lat":59.1083083,"lng":13.8208008,"zoom":6,"layers":["OpenStreetMap.Mapnik"],"markers":[]}',
        ),
        7 => array(
            'key' => 'field_65a4f6af50302',
            'label' => __('Organizer', 'api-event-manager'),
            'name' => 'organization',
            'aria-label' => '',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                374 => __('Organization A', 'api-event-manager'),
                375 => __('Organization B', 'api-event-manager'),
            ),
            'default_value' => false,
            'return_format' => 'value',
            'multiple' => 0,
            'allow_null' => 0,
            'ui' => 0,
            'ajax' => 0,
            'placeholder' => '',
        ),
        8 => array(
            'key' => 'field_65a52a6374b0c',
            'label' => __('Audience', 'api-event-manager'),
            'name' => 'audience',
            'aria-label' => '',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                370 => __('Audience A', 'api-event-manager'),
                371 => __('Audience B', 'api-event-manager'),
                372 => __('Audience C', 'api-event-manager'),
                373 => __('Audience D', 'api-event-manager'),
            ),
            'default_value' => false,
            'return_format' => 'value',
            'multiple' => 0,
            'allow_null' => 0,
            'ui' => 0,
            'ajax' => 0,
            'placeholder' => '',
        ),
        9 => array(
            'key' => 'field_65a66d8cadeef',
            'label' => __('Occasions', 'api-event-manager'),
            'name' => 'occasions',
            'aria-label' => '',
            'type' => 'repeater',
            'instructions' => __('When is the event scheduled to take place? You have the option to establish a recurring schedule or specify up to 10 distinct dates and times.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'layout' => 'block',
            'pagination' => 0,
            'min' => 1,
            'max' => 10,
            'collapsed' => '',
            'button_label' => __('<span class="c-icon c-icon--material material-symbols-outlined material-symbols-outlined--filled c-icon--size-md" material-symbol="arrow_right_alt" role="img" aria-label="Icon: Undefined" alt="Icon: Repeat" data-nosnippet="" translate="no" aria-hidden="true">add</span> Add occation', 'api-event-manager'),
            'rows_per_page' => 20,
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_65a6809ba99e7',
                    'label' => __('Repeat', 'api-event-manager'),
                    'name' => 'repeat',
                    'aria-label' => '',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        'no' => __('No', 'api-event-manager'),
                        'byWeek' => __('Weekly occasion', 'api-event-manager'),
                        'byMonth' => __('Monthly occasion', 'api-event-manager'),
                    ),
                    'default_value' => false,
                    'return_format' => 'value',
                    'multiple' => 0,
                    'allow_null' => 0,
                    'ui' => 0,
                    'ajax' => 0,
                    'placeholder' => '',
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
                1 => array(
                    'key' => 'field_65a686d8af87f',
                    'label' => __('Every', 'api-event-manager'),
                    'name' => 'weeksInterval',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        0 => array(
                            0 => array(
                                'field' => 'field_65a6809ba99e7',
                                'operator' => '==',
                                'value' => 'byWeek',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 1,
                    'min' => 1,
                    'max' => '',
                    'placeholder' => '',
                    'step' => '',
                    'prepend' => '',
                    'append' => __('week/weeks', 'api-event-manager'),
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
                2 => array(
                    'key' => 'field_65a6886e30f9d',
                    'label' => __('Every', 'api-event-manager'),
                    'name' => 'monthsInterval',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        0 => array(
                            0 => array(
                                'field' => 'field_65a6809ba99e7',
                                'operator' => '==',
                                'value' => 'byMonth',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 1,
                    'min' => 1,
                    'max' => '',
                    'placeholder' => '',
                    'step' => '',
                    'prepend' => '',
                    'append' => __('week/weeks', 'api-event-manager'),
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
                3 => array(
                    'key' => 'field_65a68707af880',
                    'label' => __('Day', 'api-event-manager'),
                    'name' => 'weekDays',
                    'aria-label' => '',
                    'type' => 'checkbox',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        0 => array(
                            0 => array(
                                'field' => 'field_65a6809ba99e7',
                                'operator' => '==',
                                'value' => 'byWeek',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        'https://schema.org/Monday' => __('Mon', 'api-event-manager'),
                        'https://schema.org/Tuesday' => __('Tue', 'api-event-manager'),
                        'https://schema.org/Wednesday' => __('Wed', 'api-event-manager'),
                        'https://schema.org/Thursday' => __('Thu', 'api-event-manager'),
                        'https://schema.org/Friday' => __('Fri', 'api-event-manager'),
                        'https://schema.org/Saturday' => __('Sat', 'api-event-manager'),
                        'https://schema.org/Sunday' => __('Sun', 'api-event-manager'),
                    ),
                    'default_value' => array(
                    ),
                    'return_format' => 'value',
                    'allow_custom' => 0,
                    'layout' => 'horizontal',
                    'toggle' => 0,
                    'save_custom' => 0,
                    'custom_choice_button_text' => 'Lägg till nytt val',
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
                4 => array(
                    'key' => 'field_65a689f5a4918',
                    'label' => __('Day', 'api-event-manager'),
                    'name' => 'monthDay',
                    'aria-label' => '',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        0 => array(
                            0 => array(
                                'field' => 'field_65a6809ba99e7',
                                'operator' => '==',
                                'value' => 'byMonth',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        'day' => __('Day', 'api-event-manager'),
                        'first' => __('First', 'api-event-manager'),
                        'second' => __('Second', 'api-event-manager'),
                        'third' => __('Third', 'api-event-manager'),
                        'fourth' => __('Fourth', 'api-event-manager'),
                        'last' => __('Last', 'api-event-manager'),
                    ),
                    'default_value' => __('day', 'api-event-manager'),
                    'return_format' => 'value',
                    'multiple' => 0,
                    'allow_null' => 0,
                    'ui' => 0,
                    'ajax' => 0,
                    'placeholder' => '',
                    'allow_custom' => 0,
                    'search_placeholder' => '',
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
                5 => array(
                    'key' => 'field_65a68a6ea4919',
                    'label' => __('By number', 'api-event-manager'),
                    'name' => 'monthDayNumber',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        0 => array(
                            0 => array(
                                'field' => 'field_65a689f5a4918',
                                'operator' => '==',
                                'value' => 'day',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 1,
                    'min' => 1,
                    'max' => 31,
                    'placeholder' => '',
                    'step' => '',
                    'prepend' => '',
                    'append' => '',
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
                6 => array(
                    'key' => 'field_65a681f9a99e9',
                    'label' => __('Date', 'api-event-manager'),
                    'name' => 'date',
                    'aria-label' => '',
                    'type' => 'date_picker',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'display_format' => 'Y-m-d',
                    'return_format' => 'Y-m-d',
                    'first_day' => 1,
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
                7 => array(
                    'key' => 'field_6602d3163612a',
                    'label' => __('Until Date', 'api-event-manager'),
                    'name' => 'untilDate',
                    'aria-label' => '',
                    'type' => 'date_picker',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        0 => array(
                            0 => array(
                                'field' => 'field_65a6809ba99e7',
                                'operator' => '!=',
                                'value' => 'no',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'display_format' => 'Y-m-d',
                    'return_format' => 'Y-m-d',
                    'first_day' => 1,
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
                8 => array(
                    'key' => 'field_65a68507a99eb',
                    'label' => __('Start time', 'api-event-manager'),
                    'name' => 'startTime',
                    'aria-label' => '',
                    'type' => 'time_picker',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'display_format' => 'H:i:s',
                    'return_format' => 'H:i:s',
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
                9 => array(
                    'key' => 'field_65a6852da99ec',
                    'label' => __('End time', 'api-event-manager'),
                    'name' => 'endTime',
                    'aria-label' => '',
                    'type' => 'time_picker',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'display_format' => 'H:i:s',
                    'return_format' => 'H:i:s',
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
                10 => array(
                    'key' => 'field_65b89293153f9',
                    'label' => __('Booking page URL', 'api-event-manager'),
                    'name' => 'url',
                    'aria-label' => '',
                    'type' => 'url',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'parent_repeater' => 'field_65a66d8cadeef',
                ),
            ),
        ),
        10 => array(
            'key' => 'field_6613fdbd6090e',
            'label' => __('Prices', 'api-event-manager'),
            'name' => 'pricesList',
            'aria-label' => '',
            'type' => 'repeater',
            'instructions' => __('You have the option to offer multiple pricing tiers to accommodate attendees of different age groups. By not adding any ticket variations, your event will show up as free of charge.', 'api-event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'layout' => 'block',
            'pagination' => 0,
            'min' => 0,
            'max' => 0,
            'collapsed' => '',
            'button_label' => __('<span class="c-icon c-icon--material material-symbols-outlined material-symbols-outlined--filled c-icon--size-md" material-symbol="arrow_right_alt" role="img" aria-label="Icon: Undefined" alt="Icon: Repeat" data-nosnippet="" translate="no" aria-hidden="true">add</span> Add Ticket / Variation', 'api-event-manager'),
            'rows_per_page' => 20,
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_6613fdcc6090f',
                    'label' => __('Price Label', 'api-event-manager'),
                    'name' => 'priceLabel',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => __('Standard Ticket', 'api-event-manager'),
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'parent_repeater' => 'field_6613fdbd6090e',
                ),
                1 => array(
                    'key' => 'field_6613fdea60910',
                    'label' => __('Price', 'api-event-manager'),
                    'name' => 'price',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 100,
                    'min' => '',
                    'max' => '',
                    'placeholder' => '',
                    'step' => '',
                    'prepend' => '',
                    'append' => __('$', 'api-event-manager'),
                    'parent_repeater' => 'field_6613fdbd6090e',
                ),
            ),
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
    'position' => 'acf_after_title',
    'style' => 'default',
    'label_placement' => 'left',
    'instruction_placement' => 'label',
    'hide_on_screen' => array(
        0 => 'permalink',
        1 => 'the_content',
        2 => 'excerpt',
        3 => 'discussion',
        4 => 'comments',
        5 => 'format',
        6 => 'categories',
        7 => 'tags',
        8 => 'send-trackbacks',
    ),
    'active' => true,
    'description' => 'We are nearly there! Just let us know some important last minute details, and you are ready to go!',
    'show_in_rest' => 0,
));
}