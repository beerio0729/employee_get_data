@php
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\View\Components\StatsOverviewWidgetComponent\StatComponent\DescriptionComponent;
use Filament\Widgets\View\Components\StatsOverviewWidgetComponent\StatComponent\StatsOverviewWidgetStatChartComponent;
use Illuminate\View\ComponentAttributeBag;

$chartColor = $getChartColor() ?? 'gray';
$descriptionColor = $getDescriptionColor() ?? 'gray';
$descriptionIcon = $getDescriptionIcon();
$descriptionIconPosition = $getDescriptionIconPosition();
$url = $getUrl();
$tag = $url ? 'a' : 'div';
$chartDataChecksum = $generateChartDataChecksum();
$colorVar = "--{$descriptionColor}-100";

@endphp

<{!! $tag !!}
    @if ($url)
    {{ \Filament\Support\generate_href_html($url, $shouldOpenUrlInNewTab()) }}
    @endif
    {{
         $getExtraAttributeBag()
        ->class([
            'fi-wi-stats-overview-stat',
        ])
    }}
    style="background: linear-gradient(to right, var({{ $colorVar }}), transparent, transparent);">
    <div class="stat-main-flex">
        <div class="fi-wi-stats-overview-stat-content">
            @if ($label = $getLabel())
            <div class="fi-wi-stats-overview-stat-label-ctn">
                {{ \Filament\Support\generate_icon_html($getIcon()) }}

                <span class="fi-wi-stats-overview-stat-label">
                    {{ $getLabel() }}
                </span>
            </div>
            @endif
            <{!! $tag !!}
                class="stat-value"
                style="color: var(--{{$descriptionColor}}-700);">
                {{ $getValue() }}
            </{!! $tag !!}>
        </div>

        <div class="stat-icon">
            <div {{ (new ComponentAttributeBag)->color(DescriptionComponent::class, $descriptionColor)->class(['fi-wi-stats-overview-stat-description']) }}>
                @if ($descriptionIcon && in_array($descriptionIconPosition, [IconPosition::After, 'after']))
                {{ \Filament\Support\generate_icon_html(
                    $descriptionIcon, 
                    attributes: (new \Illuminate\View\ComponentAttributeBag),
                    size: Filament\Support\Enums\IconSize::ThreeExtraLarge,
                )}}
            </div>
            @endif
        </div>

    </div>
    @if ($description = $getDescription())
    <div {{ (new ComponentAttributeBag)->color(DescriptionComponent::class, $descriptionColor)->class(['fi-wi-stats-overview-stat-description']) }}>
        @if ($descriptionIcon && in_array($descriptionIconPosition, [IconPosition::Before, 'before']))
        {{ \Filament\Support\generate_icon_html($descriptionIcon, attributes: (new \Illuminate\View\ComponentAttributeBag)) }}
        @endif

        <span>
            {{ $description }}
        </span>
    </div>
    @endif

    @if ($progress = $getProgress())
    {{-- เพิ่มการใช้ progress bar --}}
    <div class="progress-stat-container">
        <{!! $tag !!}
            class="progress-stat-bar"
            style="width:{{ $progress }}%; background-color: var(--{{$descriptionColor}}-500); height:10px;"
        >
        </{!! $tag !!}>
    </div>
    @endif
</{!! $tag !!}>

<style>
    /***********ปรับ pregrees bar ที่เราสร้างมาเอง****************/
    .stat-main-flex {
        display: flex;
        justify-content: space-between;
    }

    .stat-icon {
        display: flex;
        justify-content: flex-end;
        align-items: flex-start;
    }

    .stat-value {
        font-size: var(--text-3xl);
        line-height: var(--tw-leading, var(--text-3xl--line-height));
        --tw-font-weight: var(--font-weight-semibold);
        font-weight: var(--font-weight-semibold);
        --tw-tracking: var(--tracking-tight);
        letter-spacing: var(--tracking-tight);
    }

    .progress-stat-bar {
        border-radius: 8px;
    }

    .progress-stat-container {
        background-color: #7a7a7a49 !important;
        margin-top: 10px;
        border-radius: 8px;
    }
</style>