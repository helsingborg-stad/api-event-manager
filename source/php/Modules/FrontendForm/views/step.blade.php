@if (!empty($step['fields']))
@dump($step)
    @if($step['title'])
        @typography([
            'element' => 'h2',
        ])
            {{ $step['title'] }}
        @endtypography
    @endif
    @foreach($step['fields'] as $field)
        @includeIf('fields.' . $field['view'], ['field' => $field])
        @dump($field['view'])
    @endforeach
@endif