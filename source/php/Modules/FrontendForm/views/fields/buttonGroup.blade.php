@element([
    'attributeList' => [
        'role' => 'radiogroup',
    ]
])
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
@endelement