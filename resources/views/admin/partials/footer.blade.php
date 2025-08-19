<footer class="footer text-center text-sm-start">
    &copy;

    MASAR <span class="text-muted d-none d-sm-inline-block float-end">Crafted with <i
            class="mdi mdi-heart text-danger"></i> by CORE HOUSE </span>



    <script>
        $(document).ready(function() {

            $('.frst').first().focus();
            $(document).on('keydown', function(e) {
                if (e.key === "F1") {
                    e.preventDefault(); // منع المساعدة الافتراضية للمتصفح
                    $('.frst').first().focus();
                }
          
            });


            $('input[type="number"]').on('focus', function() {
                $(this).select(); // تحديد المحتوى عند التركيز
            });

            $('input[type="number"]').on('blur', function() {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) {
                    $(this).val(val.toFixed(2)); // تنسيق لمنزلتين عشريتين
                }
            });
        });
    </script>
    <script>
        //submit by f12
        $(document).on('keydown', function(e) {
            if (e.key === "F12") {
                e.preventDefault();
                $('form').submit();
            }
        });
    </script>
    {{-- 
    <script>
        var options = {
            chart: {
                type: 'line',
                height: 350
            },
            series: [{
                name: 'المبيعات',
                data: [10, 20, 30, 40]
            }]
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    </script> --}}


<script>
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('printbtn')) {
        let targetId = e.target.getAttribute('data-target');
        let content = document.getElementById(targetId).innerHTML;

        let printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write(content);
        printWindow.document.close();
        printWindow.print();
    }
});
</script>

</footer>
