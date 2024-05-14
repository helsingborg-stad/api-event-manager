@button([
    'text' => $lang->next ?? 'Next',
    'color' => 'primary',
    'style' => 'filled',
    'type' => 'submit',
    'icon' => 'chevron_right',
    'reversePositions' => false,
    'classList' => $classList ?? [
        'u-width--100',
    ]
])
@endbutton