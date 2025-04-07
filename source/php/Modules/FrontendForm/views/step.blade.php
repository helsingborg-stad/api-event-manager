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
        @includeIf('fields.' . $field['type'], ['field' => $field])
    @endforeach
@endif