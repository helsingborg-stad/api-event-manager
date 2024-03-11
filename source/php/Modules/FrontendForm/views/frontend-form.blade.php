@if (!$hideTitle && !empty($postTitle))
  <h2 class="event-form-title">{{ $postTitle }}</h2>
@endif



@paper(['padding' => 4])
  {!! $formStart() !!}
  {!! $form() !!}
  {!! $formEnd() !!}
@endpaper