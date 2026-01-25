<?php

namespace App\Filament\Overrides\Support\Enums;

enum IconSize: string
{
    case ExtraSmall = 'xs';

    case Small = 'sm';

    case Medium = 'md';

    case Large = 'lg';

    case ExtraLarge = 'xl';

    case TwoExtraLarge = '2xl';
    
    case ThreeExtraLarge = '3xl';
    
    case FourExtraLarge = '4xl';
    
    case FiveExtraLarge = '5xl';

    /**
     * @deprecated Use `TwoExtraLarge` instead.
     */
    public const ExtraExtraLarge = self::TwoExtraLarge;
}