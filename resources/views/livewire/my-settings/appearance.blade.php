<?php

declare(strict_types=1);

use Livewire\Volt\Component;

new class extends Component {
    public string $theme = 'default';

    public function mount(): void
    {
        $this->theme = session('theme', 'default');
    }

    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
        session(['theme' => $theme]);
        
        $this->dispatch('theme-changed', theme: $theme);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        {{-- Dark/Light Mode --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">{{ __('Color Mode') }}</h3>
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
            </flux:radio.group>
        </div>

        {{-- Theme Selection --}}
        <div>
            <h3 class="text-lg font-semibold mb-3">{{ __('Theme Style') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Default Theme --}}
                <div 
                    wire:click="setTheme('default')"
                    class="border-2 rounded-lg p-4 cursor-pointer transition-all hover:shadow-lg {{ $theme === 'default' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700' }}"
                >
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-base">{{ __('Default') }}</h4>
                        @if($theme === 'default')
                            <flux:icon.check-circle class="text-primary-500" />
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        {{ __('Classic clean design with solid colors') }}
                    </p>
                    <div class="flex gap-2">
                        <div class="w-8 h-8 rounded bg-blue-500"></div>
                        <div class="w-8 h-8 rounded bg-green-500"></div>
                        <div class="w-8 h-8 rounded bg-red-500"></div>
                        <div class="w-8 h-8 rounded bg-yellow-500"></div>
                    </div>
                </div>

                {{-- Modern Theme (Gradient) --}}
                <div 
                    wire:click="setTheme('modern')"
                    class="border-2 rounded-lg p-4 cursor-pointer transition-all hover:shadow-lg {{ $theme === 'modern' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700' }}"
                >
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-base">{{ __('Modern') }}</h4>
                        @if($theme === 'modern')
                            <flux:icon.check-circle class="text-primary-500" />
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        {{ __('Beautiful gradients with smooth animations') }}
                    </p>
                    <div class="flex gap-2">
                        <div class="w-8 h-8 rounded" style="background: linear-gradient(135deg, #34d3a3 0%, #2ab88d 100%)"></div>
                        <div class="w-8 h-8 rounded" style="background: linear-gradient(135deg, #1ad270 0%, #17b860 100%)"></div>
                        <div class="w-8 h-8 rounded" style="background: linear-gradient(135deg, #ff1a1a 0%, #e61717 100%)"></div>
                        <div class="w-8 h-8 rounded" style="background: linear-gradient(135deg, #ffc01a 0%, #e6a817 100%)"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview Section --}}
        @if($theme === 'modern')
            <div class="mt-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <h4 class="font-semibold mb-3">{{ __('Preview') }}</h4>
                <div class="flex flex-wrap gap-2">
                    <button class="btn btn-primary">{{ __('Primary') }}</button>
                    <button class="btn btn-success">{{ __('Success') }}</button>
                    <button class="btn btn-danger">{{ __('Danger') }}</button>
                    <button class="btn btn-warning">{{ __('Warning') }}</button>
                </div>
            </div>
        @endif
    </x-settings.layout>
</section>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('theme-changed', (event) => {
            // Reload page to apply new theme
            setTimeout(() => {
                window.location.reload();
            }, 300);
        });
    });
</script>

<style>
    /* Modern Theme Preview Styles */
    @if($theme === 'modern')
        .btn-primary {
            background: linear-gradient(135deg, #34d3a3 0%, #2ab88d 100%) !important;
            border: none !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(52, 211, 163, 0.3);
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2ab88d 0%, #239d77 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 211, 163, 0.5);
        }

        .btn-success {
            background: linear-gradient(135deg, #1ad270 0%, #17b860 100%) !important;
            border: none !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(26, 210, 112, 0.3);
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #17b860 0%, #13964d 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 210, 112, 0.5);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff1a1a 0%, #e61717 100%) !important;
            border: none !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(230, 23, 23, 0.3);
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #e61717 0%, #b31212 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(230, 23, 23, 0.5);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc01a 0%, #e6a817 100%) !important;
            border: none !important;
            color: #000000 !important;
            box-shadow: 0 4px 12px rgba(230, 168, 23, 0.3);
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #e6a817 0%, #b38312 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(230, 168, 23, 0.5);
        }
    @endif
</style>
