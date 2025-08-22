<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

new class extends Component {
    public string $currentLocale;
    public array $availableLocales = [
        'ar' => 'language.arabic',
        'en' => 'language.english',
        'tr' => 'language.turkish',
    ];

    public function mount()
    {
        $this->currentLocale = App::getLocale();
    }

    public function switchLanguage($locale)
    {
        if (array_key_exists($locale, $this->availableLocales)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            $this->currentLocale = $locale;

            $this->redirect(request()->header('Referer'));
        }
    }
};
?>

<div class="language-switcher" style="font-family: 'Cairo', sans-serif;">
    <div class="dropdown">
        <button class="btn btn-link dropdown-toggle d-flex align-items-center gap-2"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="fas fa-globe"></i>
            <span class="current-locale">{{ __($availableLocales[$currentLocale]) }}</span>
        </button>
        <ul class="dropdown-menu">
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
