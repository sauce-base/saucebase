<?php

namespace Modules\Settings\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GeneralSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('settings::filament.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('General');
    }

    public function getTitle(): string
    {
        return __('General Settings');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Application Settings'))
                ->description(__('Configure general application settings. More options will be available in future updates.'))
                ->icon(Heroicon::OutlinedInformationCircle)
                ->iconColor('info')
                ->schema([]),
        ]);
    }
}
