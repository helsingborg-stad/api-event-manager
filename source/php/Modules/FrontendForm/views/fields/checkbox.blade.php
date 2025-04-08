{{-- TODO: How should we use checkbox? --}}
@element([])
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
@endelement