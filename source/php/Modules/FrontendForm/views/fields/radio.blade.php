{{-- TODO: How should we use checkbox? --}}
<div role="radiogroup">
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
</div>