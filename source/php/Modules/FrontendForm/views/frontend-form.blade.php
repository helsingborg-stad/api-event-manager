@form([])
  @foreach($steps as $step)
    @include('step', ['step' => $step])
  @endforeach
@endform