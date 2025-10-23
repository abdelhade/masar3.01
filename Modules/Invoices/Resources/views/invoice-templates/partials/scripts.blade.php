@push('scripts')
    <script>
        $(document).ready(function() {
            $('.invoice-type-checkbox').each(function() {
                const typeId = $(this).val();
                if ($(this).is(':checked')) {
                    $('#default_' + typeId).show();
                }
            });

            $('.invoice-type-checkbox').on('change', function() {
                const typeId = $(this).val();
                if ($(this).is(':checked')) {
                    $('#default_' + typeId).slideDown();
                } else {
                    $('#default_' + typeId).slideUp();
                    $('#default_switch_' + typeId).prop('checked', false);
                }
            });
        });
    </script>
@endpush
