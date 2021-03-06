<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_57612f9baa78b',
    'title' => __('Location', 'event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_57d26cffc2f68',
            'label' => __('Address', 'event-manager'),
            'name' => '',
            'type' => 'tab',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => 'left',
            'endpoint' => 0,
        ),
        1 => array(
            'key' => 'field_57612fb7d3ffc',
            'label' => __('Street address', 'event-manager'),
            'name' => 'street_address',
            'type' => 'text',
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
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ),
        2 => array(
            'key' => 'field_57612fc8d3ffd',
            'label' => __('Postal code', 'event-manager'),
            'name' => 'postal_code',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        3 => array(
            'key' => 'field_57612fd0d3ffe',
            'label' => __('City', 'event-manager'),
            'name' => 'city',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => 50,
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ),
        4 => array(
            'key' => 'field_57612fefd3fff',
            'label' => __('Municipial', 'event-manager'),
            'name' => 'municipality',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => 50,
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ),
        5 => array(
            'key' => 'field_57612fadd3ffb',
            'label' => __('Country', 'event-manager'),
            'name' => 'country',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => 50,
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ),
        6 => array(
            'key' => 'field_5832ece755362',
            'label' => __('Map', 'event-manager'),
            'name' => 'geo_map',
            'type' => 'google_map',
            'instructions' => __('The map shows where the site is located and is not editable.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'center_lat' => 'null',
            'center_lng' => 'null',
            'zoom' => 16,
            'height' => '',
        ),
        7 => array(
            'key' => 'field_5a6ed716688b8',
            'label' => __('Coordinates', 'event-manager'),
            'name' => 'manual_coordinates',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => __('Yes, I want to enter the coordinates manually for this location.', 'event-manager'),
            'default_value' => 0,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        8 => array(
            'key' => 'field_5a6edfd13840c',
            'label' => __('Latitude', 'event-manager'),
            'name' => 'manual_latitude',
            'type' => 'number',
            'instructions' => __('Use the ISO 6709 standard when entering data.', 'event-manager'),
            'required' => 1,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_5a6ed716688b8',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'min' => '',
            'max' => '',
            'step' => '',
        ),
        9 => array(
            'key' => 'field_5a6edf9e3840b',
            'label' => __('Longitude', 'event-manager'),
            'name' => 'manual_longitude',
            'type' => 'number',
            'instructions' => __('Use the ISO 6709 standard when entering data.', 'event-manager'),
            'required' => 1,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_5a6ed716688b8',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'min' => '',
            'max' => '',
            'step' => '',
        ),
        10 => array(
            'key' => 'field_57cfba1b54b27',
            'label' => __('Open hours', 'event-manager'),
            'name' => '',
            'type' => 'tab',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => 'left',
            'endpoint' => 0,
        ),
        11 => array(
            'key' => 'field_58a2cba44c79c',
            'label' => __('Open hours', 'event-manager'),
            'name' => 'open_hours',
            'type' => 'repeater',
            'instructions' => __('Add open hours for this location.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'collapsed' => '',
            'min' => 0,
            'max' => 0,
            'layout' => 'block',
            'button_label' => __('Add', 'event-manager'),
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_58a2cbc24c79d',
                    'label' => __('Weekday', 'event-manager'),
                    'name' => 'weekday',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        1 => __('Monday', 'event-manager'),
                        2 => __('Tuesday', 'event-manager'),
                        3 => __('Wednesday', 'event-manager'),
                        4 => __('Thursday', 'event-manager'),
                        5 => __('Friday', 'event-manager'),
                        6 => __('Saturday', 'event-manager'),
                        7 => __('Sunday', 'event-manager'),
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                ),
                1 => array(
                    'key' => 'field_58a2cd794c79e',
                    'label' => __('Closed', 'event-manager'),
                    'name' => 'closed',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 0,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                2 => array(
                    'key' => 'field_58a2cd9e4c79f',
                    'label' => __('Opening', 'event-manager'),
                    'name' => 'opening',
                    'type' => 'time_picker',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        0 => array(
                            0 => array(
                                'field' => 'field_58a2cd794c79e',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'display_format' => 'H:i',
                    'return_format' => 'H:i',
                ),
                3 => array(
                    'key' => 'field_58a2cde54c7a0',
                    'label' => __('Closing', 'event-manager'),
                    'name' => 'closing',
                    'type' => 'time_picker',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => array(
                        0 => array(
                            0 => array(
                                'field' => 'field_58a2cd794c79e',
                                'operator' => '!=',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'display_format' => 'H:i',
                    'return_format' => 'H:i',
                ),
            ),
        ),
        12 => array(
            'key' => 'field_57cfba70697e1',
            'label' => __('Exceptions to opening hours', 'event-manager'),
            'name' => 'open_hour_exceptions',
            'type' => 'repeater',
            'instructions' => __('Add one or more exceptions to this scheme.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'collapsed' => '',
            'min' => 0,
            'max' => 0,
            'layout' => 'table',
            'button_label' => __('Add exception', 'event-manager'),
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_57cfbb60697e2',
                    'label' => __('Date', 'event-manager'),
                    'name' => 'exception_date',
                    'type' => 'date_picker',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '30',
                        'class' => '',
                        'id' => '',
                    ),
                    'display_format' => 'Y-m-d',
                    'return_format' => 'Y-m-d',
                    'first_day' => 1,
                ),
                1 => array(
                    'key' => 'field_57cfbbb9697e3',
                    'label' => __('Exeption information', 'event-manager'),
                    'name' => 'exeption_information',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '70',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            ),
        ),
        13 => array(
            'key' => 'field_59426f53ca437',
            'label' => __('Links', 'event-manager'),
            'name' => '',
            'type' => 'tab',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => 'top',
            'endpoint' => 0,
        ),
        14 => array(
            'key' => 'field_5942707eca439',
            'label' => __('Links', 'event-manager'),
            'name' => 'links',
            'type' => 'repeater',
            'instructions' => __('Add external links to different social media and streaming services.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'collapsed' => '',
            'min' => 0,
            'max' => 0,
            'layout' => 'table',
            'button_label' => __('Add link', 'event-manager'),
            'sub_fields' => array(
                0 => array(
                    'key' => 'field_59427095ca43a',
                    'label' => __('Service', 'event-manager'),
                    'name' => 'service',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        'webpage' => __('Webpage', 'event-manager'),
                        'bambuser' => __('Bambuser', 'event-manager'),
                        'facebook' => __('Facebook', 'event-manager'),
                        'instagram' => __('Instagram', 'event-manager'),
                        'spotify' => __('Spotify', 'event-manager'),
                        'twitter' => __('Twitter', 'event-manager'),
                        'vimeo' => __('Vimeo', 'event-manager'),
                        'youtube' => __('Youtube', 'event-manager'),
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                ),
                1 => array(
                    'key' => 'field_5942725dca43b',
                    'label' => __('Url', 'event-manager'),
                    'name' => 'url',
                    'type' => 'url',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                ),
            ),
        ),
        15 => array(
            'key' => 'field_57fca1e1dc2f0',
            'label' => __('Gallery', 'event-manager'),
            'name' => '',
            'type' => 'tab',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => 'top',
            'endpoint' => 0,
        ),
        16 => array(
            'key' => 'field_57fca1efdc2f1',
            'label' => __('Gallery', 'event-manager'),
            'name' => 'gallery',
            'type' => 'gallery',
            'instructions' => __('Add images to this location. Please upload images that are as large as possible and that are not sensitive to cropping (eg. images with text overlay).', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'min' => '',
            'max' => '',
            'insert' => 'append',
            'library' => 'all',
            'min_width' => '',
            'min_height' => '',
            'min_size' => '',
            'max_width' => '',
            'max_height' => '',
            'max_size' => '',
            'mime_types' => '',
        ),
        17 => array(
            'key' => 'field_59427dee1dee8',
            'label' => __('Organizer', 'event-manager'),
            'name' => '',
            'type' => 'tab',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => 'left',
            'endpoint' => 0,
        ),
        18 => array(
            'key' => 'field_59427caf1dee6',
            'label' => __('Organizer', 'event-manager'),
            'name' => 'organizers',
            'type' => 'post_object',
            'instructions' => __('Add an organizer that manages the location.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'post_type' => array(
                0 => 'organizer',
            ),
            'taxonomy' => array(
            ),
            'allow_null' => 1,
            'multiple' => 0,
            'return_format' => 'id',
            'ui' => 1,
        ),
        19 => array(
            'key' => 'field_5942843f6867f',
            'label' => __('Prices', 'event-manager'),
            'name' => '',
            'type' => 'tab',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'placement' => 'left',
            'endpoint' => 0,
        ),
        20 => array(
            'key' => 'field_594282f5719a1',
            'label' => __('Included in membership cards', 'event-manager'),
            'name' => 'membership_cards',
            'type' => 'post_object',
            'instructions' => __('Add membership cards where entry is included.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'post_type' => array(
                0 => 'membership-card',
            ),
            'taxonomy' => array(
            ),
            'allow_null' => 0,
            'multiple' => 1,
            'return_format' => 'id',
            'ui' => 1,
        ),
        21 => array(
            'key' => 'field_594282f8719a2',
            'label' => __('Default price / Adult', 'event-manager'),
            'name' => 'price_adult',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => __('SEK', 'event-manager'),
            'maxlength' => '',
        ),
        22 => array(
            'key' => 'field_594282fc719a3',
            'label' => __('Price student', 'event-manager'),
            'name' => 'price_student',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => __('SEK', 'event-manager'),
            'maxlength' => '',
        ),
        23 => array(
            'key' => 'field_594282ff719a4',
            'label' => __('Price children', 'event-manager'),
            'name' => 'price_children',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => __('SEK', 'event-manager'),
            'maxlength' => '',
        ),
        24 => array(
            'key' => 'field_59428302719a5',
            'label' => __('Age restriction for children price', 'event-manager'),
            'name' => 'children_age',
            'type' => 'select',
            'instructions' => __('Children price is valid up to this age.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                0 => __('0', 'event-manager'),
                1 => __('1', 'event-manager'),
                2 => __('2', 'event-manager'),
                3 => __('3', 'event-manager'),
                4 => __('4', 'event-manager'),
                5 => __('5', 'event-manager'),
                6 => __('6', 'event-manager'),
                7 => __('7', 'event-manager'),
                8 => __('8', 'event-manager'),
                9 => __('9', 'event-manager'),
                10 => __('10', 'event-manager'),
                11 => __('11', 'event-manager'),
                12 => __('12', 'event-manager'),
                13 => __('13', 'event-manager'),
                14 => __('14', 'event-manager'),
                15 => __('15', 'event-manager'),
                16 => __('16', 'event-manager'),
                17 => __('17', 'event-manager'),
                18 => __('18', 'event-manager'),
                19 => __('19', 'event-manager'),
                20 => __('20', 'event-manager'),
                21 => __('21', 'event-manager'),
                22 => __('22', 'event-manager'),
                23 => __('23', 'event-manager'),
                24 => __('24', 'event-manager'),
                25 => __('25', 'event-manager'),
            ),
            'default_value' => array(
            ),
            'allow_null' => 1,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),
        25 => array(
            'key' => 'field_59428305719a6',
            'label' => __('Price senior', 'event-manager'),
            'name' => 'price_senior',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => __('SEK', 'event-manager'),
            'maxlength' => '',
        ),
        26 => array(
            'key' => 'field_5942830a719a7',
            'label' => __('Age restriction for senior price', 'event-manager'),
            'name' => 'senior_age',
            'type' => 'select',
            'instructions' => __('Senior price is valid from this age.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'choices' => array(
                50 => __('50', 'event-manager'),
                51 => __('51', 'event-manager'),
                52 => __('52', 'event-manager'),
                53 => __('53', 'event-manager'),
                54 => __('54', 'event-manager'),
                55 => __('55', 'event-manager'),
                56 => __('56', 'event-manager'),
                57 => __('57', 'event-manager'),
                58 => __('58', 'event-manager'),
                59 => __('59', 'event-manager'),
                60 => __('60', 'event-manager'),
                61 => __('61', 'event-manager'),
                62 => __('62', 'event-manager'),
                63 => __('63', 'event-manager'),
                64 => __('64', 'event-manager'),
                65 => __('65', 'event-manager'),
                66 => __('66', 'event-manager'),
                67 => __('67', 'event-manager'),
                68 => __('68', 'event-manager'),
                69 => __('69', 'event-manager'),
                70 => __('70', 'event-manager'),
                71 => __('71', 'event-manager'),
                72 => __('72', 'event-manager'),
                73 => __('73', 'event-manager'),
                74 => __('74', 'event-manager'),
                75 => __('75', 'event-manager'),
                76 => __('76', 'event-manager'),
                77 => __('77', 'event-manager'),
                78 => __('78', 'event-manager'),
                79 => __('79', 'event-manager'),
                80 => __('80', 'event-manager'),
                81 => __('81', 'event-manager'),
                82 => __('82', 'event-manager'),
                83 => __('83', 'event-manager'),
                84 => __('84', 'event-manager'),
                85 => __('85', 'event-manager'),
            ),
            'default_value' => array(
            ),
            'allow_null' => 1,
            'multiple' => 0,
            'ui' => 0,
            'ajax' => 0,
            'return_format' => 'value',
            'placeholder' => '',
        ),
        27 => array(
            'key' => 'field_594282ed719a0',
            'label' => __('Minimum age to enter', 'event-manager'),
            'name' => 'age_restriction',
            'type' => 'number',
            'instructions' => __('Enter age if this location is age restricted.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => __('e.g. 15', 'event-manager'),
            'prepend' => '',
            'append' => __('year', 'event-manager'),
            'min' => 0,
            'max' => '',
            'step' => '',
        ),
        28 => array(
            'key' => 'field_59428314719a8',
            'label' => __('Price information', 'event-manager'),
            'name' => 'price_information',
            'type' => 'textarea',
            'instructions' => __('Write additional information about prices.', 'event-manager'),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => 3,
            'new_lines' => 'wpautop',
        ),
    ),
    'location' => array(
        0 => array(
            0 => array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'location',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));
}