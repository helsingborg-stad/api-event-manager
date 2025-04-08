<div role="radiogroup">
    @foreach ($field['choices'] as $choice)
        @option($choice)
        @endoption
    @endforeach
</div>