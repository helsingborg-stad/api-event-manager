@notice([
    'type' => $type ?? 'success',
    'message' => (object) [
        'title' => $title ?? '',
        'text' => $text ?? ''
    ],
    'icon' => $icon ?? [
        'name' => 'check'
    ],
    'classList' => [
        'u-margin__bottom--4'
    ]
])
@endnotice