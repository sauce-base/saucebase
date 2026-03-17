<?php

namespace Modules\Roadmap\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum RoadmapStatus: string implements HasColor, HasLabel
{
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::PendingApproval => __('Pending Approval'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
            self::InProgress => __('In Progress'),
            self::Completed => __('Completed'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PendingApproval => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::InProgress => 'info',
            self::Completed => 'primary',
            self::Cancelled => 'gray',
        };
    }

    public static function publicStatuses(): array
    {
        return [
            self::Approved,
            self::InProgress,
            self::Completed,
        ];
    }
}
