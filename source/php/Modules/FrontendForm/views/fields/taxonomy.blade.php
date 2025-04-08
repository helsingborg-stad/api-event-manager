@if (!empty($field['terms']))
    @foreach($field['terms'] as $term)
        @option($term)
        @endoption
    @endforeach
@endif