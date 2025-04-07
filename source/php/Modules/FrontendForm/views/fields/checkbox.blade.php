@dump($field)
{{-- TODO: How should we use checkbox? --}}
<div>
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
</div>