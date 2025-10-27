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
                <span class="current-locale"><?php echo e(__($availableLocales[$currentLocale])); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $availableLocales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locale => $langKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <button class="dropdown-item <?php echo e($locale === $currentLocale ? 'active' : ''); ?>"
                                wire:click="switchLanguage('<?php echo e($locale); ?>')"
                                type="button">
                            <!--[if BLOCK]><![endif]--><?php if($locale === 'ar'): ?>
                                🇸🇦
                            <?php elseif($locale === 'en'): ?>
                                🇺🇸
                            <?php elseif($locale === 'tr'): ?>
                                🇹🇷
                            <?php elseif($locale === 'fr'): ?>
                                🇫🇷
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php echo e(__($langKey)); ?>

                            <!--[if BLOCK]><![endif]--><?php if($locale === $currentLocale): ?>
                                <i class="fas fa-check ms-2"></i>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </button>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </ul>
        </div>
    </div>
</div>
<?php /**PATH D:\Laragon\laragon\www\massar1.02\resources\views/livewire/language-switcher.blade.php ENDPATH**/ ?>