<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('livewire:init', () => {
            window.addEventListener('open-modal', event => {
                let modal = new bootstrap.Modal(document.getElementById(event.detail[0]));
                modal.show();
                // Keep page scrollbar visible even when modal is open
                document.documentElement.style.overflowY = 'scroll';
                document.body.style.overflowY = 'auto';
            });

            window.addEventListener('close-modal', event => {
                let modal = bootstrap.Modal.getInstance(document.getElementById(event.detail[
                    0]));
                if (modal) {
                    modal.hide();
                }
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                // Always restore scrolling
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.documentElement.style.overflowY = 'scroll';
                document.body.style.overflowY = 'auto';
            });
            // Auto-focus functionality
            Livewire.on('auto-focus', function (inputId) {
                // Add a small delay to ensure DOM is updated
                setTimeout(() => {
                    const element = document.getElementById(inputId);
                    if (element) {
                        element.focus();
                    }
                }, 100);
            });

            // منع زر الإدخال (Enter) من حفظ النموذج
            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('keydown', function (e) {
                    // إذا كان الزر Enter وتم التركيز على input وليس textarea أو زر
                    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target
                        .type !== 'submit' && e.target.type !== 'button') {
                        e.preventDefault();
                    }
                });
            });

            // إغلاق المودال عند الضغط على Escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    Livewire.dispatch('closeModal');
                }
            });

            // إغلاق المودال عند النقر خارج المودال
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('modal-backdrop')) {
                    Livewire.dispatch('closeModal');
                }
            });

            // حفظ البيانات عند الضغط على Enter في المودال
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && document.querySelector('.modal.show')) {
                    const modalInput = document.querySelector('.modal.show input[type="text"]');
                    if (modalInput && modalInput === document.activeElement) {
                        e.preventDefault();
                        Livewire.dispatch('saveModalData');
                    }
                }
            });
        });


        // Safety net: when any Bootstrap modal hides, clean body/backdrops
        document.addEventListener('hidden.bs.modal', function () {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.documentElement.style.overflowY = 'scroll';
            document.body.style.overflowY = 'auto';
        });

        document.addEventListener('shown.bs.modal', function () {
            // Ensure body/html keep scrollbar visible while modal shown
            document.documentElement.style.overflowY = 'scroll';
            document.body.style.overflowY = 'auto';
        });
    });
</script>