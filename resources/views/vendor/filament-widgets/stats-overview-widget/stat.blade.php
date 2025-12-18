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
    }}>
    <div style="display: flex; justify-content: space-between;">
        <div>
            <div class="fi-wi-stats-overview-stat-content">
                @if ($label = $getLabel())
                <div class="fi-wi-stats-overview-stat-label-ctn">
                    {{ \Filament\Support\generate_icon_html($getIcon()) }}

                    <span class="fi-wi-stats-overview-stat-label">
                        {{ $getLabel() }}
                    </span>
                </div>
                @endif
                <div class="fi-wi-stats-overview-stat-value">
                    {{ $getValue() }}
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
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; align-items: flex-start;">
            <div {{ (new ComponentAttributeBag)->color(DescriptionComponent::class, $descriptionColor)->class(['fi-wi-stats-overview-stat-description']) }}>
                @if ($descriptionIcon && in_array($descriptionIconPosition, [IconPosition::After, 'after']))
                {{ \Filament\Support\generate_icon_html(
                    $descriptionIcon, 
                    attributes: (new \Illuminate\View\ComponentAttributeBag),
                    size: Filament\Support\Enums\IconSize::FourExtraLarge,
                )}}
            </div>
            @endif
        </div>

    </div>


    @if ($progress = $getProgress())
    {{-- เพิ่มการใช้ progress bar --}}
    <div class="progress-stat-container">
        <div class="progress-stat-bar" style="width:{{ $progress }}%; background-color: var(--{{$descriptionColor}}-500); height:10px;"></div>
    </div>
    @endif
</{!! $tag !!}>