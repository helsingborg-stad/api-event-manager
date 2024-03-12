@paper(['padding' => 4])
@if (!$hideTitle && !empty($postTitle))
    @typography([
        'element' => 'h4',
        'variant' => 'h2',
        'classList' => ['module-title']
    ])
      {{ $postTitle }}
    @endtypography
  @endif

  {!! $formStart() !!}
  {!! $form() !!}
  {!! $formEnd() !!}
@endpaper