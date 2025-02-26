<?php 

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
    'key' => 'group_66436ffb2f075',
    'title' => __('Price', 'api-event-manager'),
    'fields' => array(
        0 => array(
            'key' => 'field_66605fb51de08',
            'label' => __('Pricing', 'api-event-manager'),
            'name' => 'pricing',
            'aria-label' => '',
            'type' => 'radio',
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
            'choices' => array(
                'free' => __('Free Event', 'api-event-manager'),
                'expense' => __('Entrance Fee', 'api-event-manager'),
            ),
            'default_value' => __('free', 'api-event-manager'),
            'return_format' => 'value',
            'allow_null' => 0,
            'other_choice' => 0,
            'layout' => 'vertical',
            'save_other_choice' => 0,
        ),
        1 => array(
            'key' => 'field_6613fdbd6090e',
            'label' => __('Prices', 'api-event-manager'),
            'name' => 'pricesList',
            'aria-label' => '',
            'type' => 'repeater',
            'instructions' => __('You have the option to offer multiple pricing tiers to accommodate attendees of different age groups. By not adding any ticket variations, your event will show up as free of charge.', 'api-event-manager'),
            'required' => 1,
            'conditional_logic' => array(
                0 => array(
                    0 => array(
                        'field' => 'field_66605fb51de08',
                        'operator' => '==',
                        'value' => 'expense',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'is_publicly_hidden' => 0,
            'is_privately_hidden' => 0,
            'acfe_repeater_stylised_button' => 0,
            'layout' => 'block',
            'pagination' => 0,
            'min' => 1,
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
                    'is_publicly_hidden' => 0,
                    'is_privately_hidden' => 0,
                    'default_value' => 100,
                    'min' => '',
                    'max' => '',
                    'allow_in_bindings' => 1,
                    'placeholder' => '',
                    'step' => '',
                    'prepend' => '',
                    'append' => __('SEK', 'api-event-manager'),
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