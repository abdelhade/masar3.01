<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار تكامل ZATCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-result {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .loading { background-color: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">🧪Massar  اختبار تكامل ZATCA</h1>

        <!-- معلومات النظام -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>معلومات النظام</h5>
            </div>
            <div class="card-body">
                <p><strong>البيئة:</strong> <span id="environment">{{ config('zatca.mode') }}</span></p>
                <p><strong>اسم الشركة:</strong> {{ config('zatca.company.name') }}</p>
                <p><strong>الرقم الضريبي:</strong> {{ config('zatca.company.vat_number') }}</p>
            </div>
        </div>

        <!-- أزرار الاختبار -->
        <div class="row mb-4">
            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="testConnection()">اختبار الاتصال</button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-success w-100" onclick="createTestInvoice()">إنشاء فاتورة</button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-info w-100" onclick="generateXML()">إنتاج XML</button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-warning w-100" onclick="generateQR()">إنتاج QR</button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary w-100" onclick="submitInvoice()">إرسال ZATCA</button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-danger w-100" onclick="runAllTests()">اختبار شامل</button>
            </div>
        </div>

        <!-- نتائج الاختبار -->
        <div class="card">
            <div class="card-header">
                <h5>نتائج الاختبار</h5>
                <button class="btn btn-sm btn-outline-secondary float-end" onclick="clearResults()">مسح النتائج</button>
            </div>
            <div class="card-body">
                <div id="test-results"></div>
            </div>
        </div>

        <!-- معلومات الفاتورة الحالية -->
        <div class="card mt-4" id="invoice-info" style="display: none;">
            <div class="card-header">
                <h5>معلومات الفاتورة الحالية</h5>
            </div>
            <div class="card-body" id="invoice-details">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentInvoiceId = null;

        function addResult(message, type = 'success') {
            const resultsDiv = document.getElementById('test-results');
            const timestamp = new Date().toLocaleTimeString('ar-SA');

            const resultDiv = document.createElement('div');
            resultDiv.className = `test-result ${type}`;
            resultDiv.innerHTML = `<strong>[${timestamp}]</strong> ${message}`;

            resultsDiv.appendChild(resultDiv);
            resultsDiv.scrollTop = resultsDiv.scrollHeight;
        }

        function clearResults() {
            document.getElementById('test-results').innerHTML = '';
        }

        async function makeApiCall(url, method = 'GET', data = null) {
            try {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                };

                if (data) {
                    options.body = JSON.stringify(data);
                }

                const response = await fetch(url, options);
                return await response.json();
            } catch (error) {
                return { success: false, error: error.message };
            }
        }

        async function testConnection() {
            addResult('🔄 جاري اختبار الاتصال مع ZATCA...', 'loading');

            const result = await makeApiCall('/api/zatca/test-connection');

            if (result.success) {
                addResult(`✅ الاتصال يعمل بنجاح - Status Code: ${result.status_code}`, 'success');
            } else {
                addResult(`❌ فشل الاتصال: ${result.error}`, 'error');
            }
        }

        async function createTestInvoice() {
            addResult('🔄 جاري إنشاء فاتورة تجريبية...', 'loading');

            const result = await makeApiCall('/api/zatca/create-test-invoice', 'POST');

            if (result.success) {
                currentInvoiceId = result.invoice.id;
                addResult(`✅ تم إنشاء فاتورة تجريبية بنجاح - رقم: ${result.invoice.invoice_number}`, 'success');
                updateInvoiceInfo();
            } else {
                addResult(`❌ فشل في إنشاء الفاتورة: ${result.error}`, 'error');
            }
        }

        async function generateXML() {
            if (!currentInvoiceId) {
                addResult('❌ يجب إنشاء فاتورة أولاً', 'error');
                return;
            }

            addResult('🔄 جاري إنتاج XML...', 'loading');

            const result = await makeApiCall('/api/zatca/generate-xml', 'POST', {
                invoice_id: currentInvoiceId
            });

            if (result.success) {
                addResult(`✅ تم إنتاج XML بنجاح - الطول: ${result.xml.length} حرف`, 'success');
                updateInvoiceInfo();
            } else {
                addResult(`❌ فشل في إنتاج XML: ${result.error}`, 'error');
            }
        }

        async function generateQR() {
            if (!currentInvoiceId) {
                addResult('❌ يجب إنشاء فاتورة أولاً', 'error');
                return;
            }

            addResult('🔄 جاري إنتاج QR Code...', 'loading');

            const result = await makeApiCall('/api/zatca/generate-qr', 'POST', {
                invoice_id: currentInvoiceId
            });

            if (result.success) {
                addResult(`✅ تم إنتاج QR Code بنجاح - الطول: ${result.qr_code.length} حرف`, 'success');
                updateInvoiceInfo();
            } else {
                addResult(`❌ فشل في إنتاج QR Code: ${result.error}`, 'error');
            }
        }

        async function submitInvoice() {
            if (!currentInvoiceId) {
                addResult('❌ يجب إنشاء فاتورة أولاً', 'error');
                return;
            }

            addResult('🔄 جاري إرسال الفاتورة إلى ZATCA...', 'loading');

            const result = await makeApiCall('/api/zatca/submit-invoice', 'POST', {
                invoice_id: currentInvoiceId
            });

            if (result.success) {
                addResult(`✅ تم إرسال الفاتورة بنجاح إلى ZATCA`, 'success');
                if (result.response && result.response.validationResults) {
                    addResult(`📊 حالة التحقق: ${result.response.validationResults.status}`, 'success');
                }
                updateInvoiceInfo();
            } else {
                addResult(`❌ فشل في إرسال الفاتورة: ${result.error}`, 'error');
            }
        }

        async function runAllTests() {
            addResult('🚀 بدء الاختبار الشامل...', 'loading');

            await testConnection();
            await new Promise(resolve => setTimeout(resolve, 1000));

            await createTestInvoice();
            await new Promise(resolve => setTimeout(resolve, 1000));

            await generateXML();
            await new Promise(resolve => setTimeout(resolve, 1000));

            await generateQR();
            await new Promise(resolve => setTimeout(resolve, 1000));

            // إرسال فقط في البيئة التجريبية
            if (document.getElementById('environment').textContent === 'sandbox') {
                await submitInvoice();
            } else {
                addResult('⚠️ تخطي الإرسال (البيئة ليست sandbox)', 'loading');
            }

            addResult('🎉 اكتمل الاختبار الشامل!', 'success');
        }

        async function updateInvoiceInfo() {
            if (!currentInvoiceId) return;

            const result = await makeApiCall(`/api/zatca/invoice-status/${currentInvoiceId}`);

            if (result.success) {
                const invoice = result.invoice;
                document.getElementById('invoice-info').style.display = 'block';
                document.getElementById('invoice-details').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> ${invoice.id}</p>
                            <p><strong>رقم الفاتورة:</strong> ${invoice.invoice_number}</p>
                            <p><strong>اسم العميل:</strong> ${invoice.customer_name}</p>
                            <p><strong>إجمالي المبلغ:</strong> ${invoice.total_amount} ريال</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>حالة ZATCA:</strong> <span class="badge bg-info">${invoice.zatca_status}</span></p>
                            <p><strong>XML:</strong> ${invoice.has_xml ? '✅ متوفر' : '❌ غير متوفر'}</p>
                            <p><strong>QR Code:</strong> ${invoice.has_qr ? '✅ متوفر' : '❌ غير متوفر'}</p>
                            <p><strong>عدد العناصر:</strong> ${invoice.items_count}</p>
                        </div>
                    </div>
                `;
            }
        }

        // تشغيل اختبار الاتصال عند تحميل الصفحة
        window.onload = function() {
            addResult('🌟 مرحباً بك في أداة اختبار ZATCA', 'success');
            testConnection();
        };
    </script>
</body>
</html>
