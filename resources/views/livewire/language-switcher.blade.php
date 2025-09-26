<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\App;

new class extends Component {
    public string $currentLocale;
    public array $availableLocales = [
        'ar' => 'language.arabic',
        'en' => 'language.english',
        'tr' => 'language.turkish',
        'fr' => 'language.french',
    ];

    public function mount()
    {
        $this->currentLocale = request()->cookie('locale', App::getLocale());
        App::setLocale($this->currentLocale);
    }

    public function switchLanguage($locale)
    {
        if (array_key_exists($locale, $this->availableLocales)) {
            App::setLocale($locale);
            $this->currentLocale = $locale;

            cookie()->queue(cookie('locale', $locale, 60 * 24 * 365));

            $this->redirect(url()->previous());
        }
    }
};
?>

<div>
    <div class="language-switcher" style="font-family: 'Cairo', sans-serif;">
        <div class="dropdown">
            <button class="btn btn-link dropdown-toggle d-flex align-items-center gap-2"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                <i class="fas fa-globe"></i>
                <span class="current-locale">{{ __($availableLocales[$currentLocale]) }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach($availableLocales as $locale => $langKey)
                    <li>
                        <button class="dropdown-item {{ $locale === $currentLocale ? 'active' : '' }}"
                                wire:click="switchLanguage('{{ $locale }}')"
                                type="button">
                            @if($locale === 'ar')
                                🇸🇦
                            @elseif($locale === 'en')
                                🇺🇸
                            @elseif($locale === 'tr')
                                🇹🇷
                            @elseif($locale === 'fr')
                                🇫🇷
                            @endif
                            {{ __($langKey) }}
                            @if($locale === $currentLocale)
                                <i class="fas fa-check ms-2"></i>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
