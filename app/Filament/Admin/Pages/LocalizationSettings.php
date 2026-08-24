<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Pages\SettingsPage;
use App\Settings\LocalizationSettings as LocalizationSettingsData;
use BackedEnum;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class LocalizationSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?int $navigationSort = 2;

    protected static string $settings = LocalizationSettingsData::class;

    public static function getNavigationLabel(): string
    {
        return __('Localization');
    }

    public function getTitle(): string
    {
        return __('Localization Settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make(__('Languages'))
                ->description(__('Choose which languages visitors can switch between. With only one language on, the language selector is hidden.'))
                ->icon(Heroicon::OutlinedLanguage)
                ->iconColor('info')
                ->schema([
                    Grid::make(2)
                        ->schema($this->localeToggles())
                        ->columnSpanFull(),
                    Select::make('default_locale')
                        ->label(__('Default language'))
                        ->extraAttributes(['data-testid' => 'admin-default-locale'])
                        // Sourced from what is currently toggled on, so the default can
                        // never be a language the application no longer offers.
                        ->options(fn (Get $get): array => array_intersect_key(
                            app(LocalizationSettingsData::class)->available(),
                            $this->enabledFromFormState($get),
                        ))
                        ->helperText(__('Used until a visitor picks a language of their own.'))
                        ->required()
                        ->native(false)
                        // The only field that can carry this message: toggles have no
                        // group to hang it on, and turning every language off would leave
                        // the application rendering raw translation keys.
                        ->rule(fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            if ($this->enabledFromFormState($get) === []) {
                                $fail(__('At least one language must be enabled.'));
                            }
                        })
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * One toggle per installed language, matching how every other settings page here
     * renders a boolean.
     *
     * @return list<Toggle>
     */
    private function localeToggles(): array
    {
        $toggles = [];

        foreach (app(LocalizationSettingsData::class)->available() as $code => $name) {
            $toggles[] = Toggle::make("enabled_locales.{$code}")
                ->label($name)
                ->extraAttributes(['data-testid' => "admin-locale-{$code}"])
                ->live();
        }

        return $toggles;
    }

    /**
     * The languages currently switched on in the form, as a code-keyed array.
     *
     * @return array<string, true>
     */
    private function enabledFromFormState(Get $get): array
    {
        return array_filter((array) $get('enabled_locales'));
    }

    /**
     * The setting stores a list of codes, because that is what the application asks it
     * for. A toggle per language needs a code-keyed map of booleans, so the two shapes are
     * translated here at the form boundary rather than distorting either side.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $enabled = (array) ($data['enabled_locales'] ?? []);

        $data['enabled_locales'] = [];

        foreach (array_keys(app(LocalizationSettingsData::class)->available()) as $code) {
            $data['enabled_locales'][$code] = in_array($code, $enabled, true);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['enabled_locales'] = array_keys(array_filter((array) ($data['enabled_locales'] ?? [])));

        return $data;
    }
}
