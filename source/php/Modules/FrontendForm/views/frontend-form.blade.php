@if (!$hideTitle && !empty($postTitle))
  @typography([
      'element' => 'h4',
      'variant' => 'h2',
      'classList' => ['module-title']
  ])
    {{ $postTitle }}
  @endtypography
@endif

@if($error != false)
  {!! $error !!}
@endif

@if($error === false)
  @foreach ($steps as $stepKey => $step)
    @paper(['padding' => 4, 'classList' => ['u-margin__bottom--4']])
      <div class="u-display--flex u-justify-content--space-between">
        
        @if ($step->title)
          @typography([
              'element' => 'h5',
              'variant' => 'h3',
              'classList' => []
          ])
            <span class="c-typography__iteration"> 
              {{ $step->step }}. 
            </span>
            {{ $step->title }}
          @endtypography
        @endif

        @if($step->state->isPassed)
          @icon([
              'icon' => 'check',
              'size' => 'md',
              'classList' => ['c-icon--emblem', 'u-color__bg--success', 'u-color__text--lightest']
          ])
          @endicon
        @endif
      </div>

      @if($step->state->isPassed)
        @button([
            'href' => $step->nav->current ?? '#',
            'text' => $lang->edit,
            'color' => 'default',
            'style' => 'filled',
            'icon' => 'edit',
            'reversePositions' => true,
            'classList' => ['u-margin__bottom--4'],
            'size' => 'sm'
        ])
        @endbutton
      @endif

      @if ($step->description)
        @typography([
            'element' => 'p',
            'variant' => 'body',
            'classList' => ['u-margin--0']
        ])
          {{ $step->description }}
        @endtypography
      @endif

      @if($step->state->isCurrent)
        <div class="u-margin__top--4">
          {!! 
            $form(
              $step->group, 
              $formSettings->postType, 
              $formSettings->postStatus
            ) 
          !!}
        </div>
      @endif

    @endpaper
  @endforeach

  @typography([
      'element' => 'p',
      'variant' => 'meta',
      'classList' => ['u-margin__top--4']
  ])
    {!! $lang->disclaimer !!}
  @endtypography

@endif