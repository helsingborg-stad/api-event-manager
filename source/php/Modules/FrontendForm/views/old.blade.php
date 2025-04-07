@if (!$hideTitle && !empty($postTitle))
  @typography([
      'element' => 'h4',
      'variant' => 'h2',
      'classList' => ['module-title']
  ])
    {{ $postTitle }}
  @endtypography
@endif

@if($empty !== false)
  {!! $empty !!}
@elseif($error !== false)
  {!! $error !!}
@endif

@if($error === false && $empty === false)

  <!-- Progress bar -->
  @progressBar([
    'value' => $state->percentageCompleted,
    'classList' => [
      'u-margin__bottom--4'
    ],
    'attributeList' => [
      'view-transition-name' => 'form-progress-bar'
    ]
  ])
  @endprogressBar

  @foreach ($steps as $stepKey => $step)
    @paper([
      'id' => 'form-step-' . $step->step, 
      'padding' => 4, 
      'classList' => ['u-margin__bottom--4'], 
      'attributeList' => ['view-transition-name' => 'form-step-' . $step->step]
    ])
      <div class="u-display--flex u-align-content--center">
        <div class="u-flex-grow--1">
        @if($step->state->isPassed)
          <div class="u-display--inline-flex u-color__bg--default u-rounded u-padding__y--05 u-padding__left--1 u-padding__right--2 u-margin__bottom--1">
            @icon([
                'icon' => 'check',
                'size' => 'md'
            ])
            @endicon
            @typography([
                'element' => 'span',
                'variant' => 'meta',
                'classList' => ['u-margin__left--1']
            ])
              {{ $lang->completed }}
            @endtypography
          </div>
        @endif
      
        @typography([
            'element' => 'div',
            'variant' => 'meta',
            'classList' => [
              'c-typography__iteration'
          ],
          'attributeList' => ['view-transition-name' => 'form-step-stepdata-' . $step->step]
        ])
          {{ $lang->step }} {{ $step->step }} {{ $lang->of }} {{ $state->totalSteps }}
        @endtypography

        @if ($step->title)
          @typography([
            'element' => 'h5',
            'variant' => 'h3',
            'classList' => ['u-margin--0'],
            'attributeList' => ['view-transition-name' => 'form-step-title-' . $step->step]
          ])
            {{ $step->title }}
          @endtypography
        @endif

        <div class="form-step-content" view-transition-name="form-step-content">
          @if ($step->description && $step->state->isCurrent)
            @typography([
              'element' => 'p',
              'variant' => 'body',
              'classList' => ['u-margin--0']
            ])
              {!! $step->description !!}
            @endtypography
          @endif

          @if($step->state->isCurrent)
            <div class="u-margin__top--4">
              {!! $form($step) !!}
            </div>
          @endif
        </div>
      </div>

        @if($step->state->isPassed)
          <div class="u-margin--auto">
            @button([
              'href' => $step->nav->current ?? '#',
              'text' => $lang->edit,
              'color' => 'default',
              'style' => 'basic',
              'icon' => 'edit',
              'reversePositions' => true,
              'classList' => [],
              'size' => 'sm'
            ])
            @endbutton
          </div>
        @endif
      </div>
    @endpaper
  @endforeach

  @if($summary->isEnabled)
    @paper(['padding' => 4, 'classList' => ['u-margin__bottom--4']])
      @if ($summary->title)
        @typography([
          'element' => 'h5',
          'variant' => 'h3',
          'classList' => ['u-margin--0']
        ])
          {{ $summary->title }}
        @endtypography

        @if ($summary->lead)
          @typography([
            'element' => 'p',
            'variant' => 'body',
            'classList' => ['u-margin--0']
          ])
            {!! $summary->lead !!}
          @endtypography
        @endif
      @endif
    @endpaper
  @endif

  @typography([
      'element' => 'p',
      'variant' => 'meta',
      'classList' => ['u-margin__top--4']
  ])
    {!! $lang->disclaimer !!}
  @endtypography
  

@endif