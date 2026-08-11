<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Pages\SettingsPage;
use App\Settings\GeneralSettings as GeneralSettingsData;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class GeneralSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = 1;

    protected static string $settings = GeneralSettingsData::class;

    public static function getNavigationLabel(): string
    {
        return __('General');
    }

    public function getTitle(): string
    {
        return __('General Settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make(__('Application Settings'))
                ->description(__('Configure the platform identity.'))
                ->icon(Heroicon::OutlinedInformationCircle)
                ->iconColor('info')
                ->schema([
                    TextInput::make('site_name')
                        ->label(__('Site name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('site_tagline')
                        ->label(__('Site tagline'))
                        ->helperText(__('Shown as the home page title, before the site name.'))
                        ->maxLength(60),
                    Textarea::make('site_description')
                        ->label(__('Site description'))
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
