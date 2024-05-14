@button([
    'text' => $lang->submit ?? 'Submit',
    'color' => 'primary',
    'style' => 'filled',
    'type' => 'submit',
    'icon' => 'calendar_clock',
    'reversePositions' => true,
    'classList' => $classList ?? [
        'u-width--100',
    ]
])
@endbutton