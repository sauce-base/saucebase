<?php

namespace Modules\Roadmap\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum RoadmapType: string implements HasColor, HasLabel
{
    case Feature = 'feature';
    case Bug = 'bug';
    case Improvement = 'improvement';

    public function getLabel(): string
    {
        return match ($this) {
            self::Feature => __('Feature'),
            self::Bug => __('Bug'),
            self::Improvement => __('Improvement'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Feature => 'primary',
            self::Bug => 'danger',
            self::Improvement => 'warning',
        };
    }
}
