@if (!$hideTitle && !empty($postTitle))
  <h2 class="event-form-title">{{ $postTitle }}</hjson>
@endif



@paper(['padding' => 4])
  {!! $formStart() !!}
  {!! $form() !!}
  {!! $formEnd() !!}
@endpaper