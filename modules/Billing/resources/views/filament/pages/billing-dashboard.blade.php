<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Stats cards with filter anchored above-right on desktop --}}
        <div class="relative">
            {{-- Filter bar: normal flow on mobile, absolute into header row on desktop --}}
            <div class="mb-4 flex justify-end sm:absolute sm:right-0 sm:-top-16 sm:mb-0">
                <div class="flex flex-wrap items-center gap-2 sm:flex-nowrap">
                    <x-filament::input.wrapper class="w-44 shrink-0">
                        <x-filament::input.select wire:model.live="preset">
                            @foreach ($this->getPresetOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>

                    @if ($preset === 'custom')
                        <x-filament::input.wrapper class="w-36 shrink-0">
                            <x-filament::input type="date" wire:model.live="customStart" />
                        </x-filament::input.wrapper>
                        <span class="shrink-0 text-sm text-gray-500 dark:text-gray-400">→</span>
                        <x-filament::input.wrapper class="w-36 shrink-0">
                            <x-filament::input type="date" wire:model.live="customEnd" />
                        </x-filament::input.wrapper>
                    @endif
                </div>
            </div>

            @livewire(\Modules\Billing\Filament\Widgets\BillingSaasStatsWidget::class)
        </div>

        @livewire(\Modules\Billing\Filament\Widgets\RevenueChartWidget::class)
        @livewire(\Modules\Billing\Filament\Widgets\SubscriptionsChartWidget::class)
        @livewire(\Modules\Billing\Filament\Widgets\ConversionChartWidget::class)

    </div>
</x-filament-panels::page>
