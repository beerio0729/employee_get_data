<x-filament::icon-button
    :badge="$unreadNotificationsCount ?: null"
    color="{{ $unreadNotificationsCount >= 1 ? 'warning' : 'gray' }}"
    :icon="\Filament\Support\Icons\Heroicon::OutlinedBell"
    :icon-alias="\Filament\View\PanelsIconAlias::TOPBAR_OPEN_DATABASE_NOTIFICATIONS_BUTTON"
    icon-size="lg"
    :label="__('filament-panels::layout.actions.open_database_notifications.label')"
    class="fi-topbar-database-notifications-btn {{ $unreadNotificationsCount >= 1 ? 'bell-shake' : '' }}" />

<style>
    @keyframes bell-shake {
        0% {transform: rotate(0);}
        10% {transform: rotate(15deg);}
        20% {transform: rotate(-15deg);}
        30% { transform: rotate(10deg);}
        40% {transform: rotate(-10deg);}
        50% {transform: rotate(5deg);}
        60% {transform: rotate(-5deg);}
        100% {transform: rotate(0);}
    }

    .bell-shake {
        animation: bell-shake 1.2s ease-in-out infinite;
        transform-origin: top center;
    }
</style>