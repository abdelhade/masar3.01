using System;
using System.IO;
using System.Net;
using System.Text;
using System.Drawing.Printing;
using System.Collections.Generic;
using System.Linq;
using System.Web.Script.Serialization;

namespace KitchenPrintAgent
{
    class PrintAgent
    {
        private static HttpListener listener;
        private static string url = "http://localhost:5000/";
        private static JavaScriptSerializer jsonSerializer = new JavaScriptSerializer();

        static void Main(string[] args)
        {
            Console.OutputEncoding = Encoding.UTF8;
            
            Console.WriteLine("========================================");
            Console.WriteLine("🖨️  وكيل طباعة المطبخ");
            Console.WriteLine("========================================");
            Console.WriteLine();

            // عرض الطابعات المتاحة
            ListAvailablePrinters();
            
            Console.WriteLine("========================================");
            Console.WriteLine("الخادم يعمل على: {0}", url);
            Console.WriteLine("اضغط Ctrl+C للإيقاف");
            Console.WriteLine("========================================");
            Console.WriteLine();

            // بدء الخادم
            StartServer();
        }

        static void ListAvailablePrinters()
        {
            Console.WriteLine("الطابعات المتاحة:");
            foreach (string printer in PrinterSettings.InstalledPrinters)
            {
                Console.WriteLine("  - {0}", printer);
            }
            
            PrinterSettings settings = new PrinterSettings();
            Console.WriteLine("الطابعة الافتراضية: {0}", settings.PrinterName);
            Console.WriteLine();
        }

        static void StartServer()
        {
            listener = new HttpListener();
            listener.Prefixes.Add(url);
            
            try
            {
                listener.Start();
                Console.WriteLine("✅ الخادم يعمل بنجاح");
                Console.WriteLine();

                while (true)
                {
                    HttpListenerContext context = listener.GetContext();
                    ProcessRequest(context);
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("❌ خطأ: {0}", ex.Message);
                Console.WriteLine();
                Console.WriteLine("ملاحظة: إذا كان الخطأ متعلق بالصلاحيات، قم بتشغيل البرنامج كمسؤول (Run as Administrator)");
            }
        }

        static void ProcessRequest(HttpListenerContext context)
        {
            HttpListenerRequest request = context.Request;
            HttpListenerResponse response = context.Response;

            string responseString = "";
            int statusCode = 200;

            try
            {
                string path = request.Url.AbsolutePath;
                string method = request.HttpMethod;

                Console.WriteLine("[{0}] {1} {2}", DateTime.Now.ToString("HH:mm:ss"), method, path);

                // معالجة المسارات المختلفة
                if (path == "/" && method == "GET")
                {
                    responseString = GetHomePage();
                    response.ContentType = "text/html; charset=utf-8";
                }
                else if (path == "/print" && method == "POST")
                {
                    responseString = HandlePrintRequest(request);
                    response.ContentType = "application/json; charset=utf-8";
                }
                else if (path == "/printers" && method == "GET")
                {
                    responseString = GetPrintersList();
                    response.ContentType = "application/json; charset=utf-8";
                }
                else if (path == "/health" && method == "GET")
                {
                    responseString = GetHealthStatus();
                    response.ContentType = "application/json; charset=utf-8";
                }
                else
                {
                    statusCode = 404;
                    responseString = jsonSerializer.Serialize(new
                    {
                        success = false,
                        message = "المسار غير موجود"
                    });
                    response.ContentType = "application/json; charset=utf-8";
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("❌ خطأ: {0}", ex.Message);
                statusCode = 500;
                responseString = jsonSerializer.Serialize(new
                {
                    success = false,
                    message = string.Format("خطأ في الخادم: {0}", ex.Message)
                });
                response.ContentType = "application/json; charset=utf-8";
            }

            // إرسال الاستجابة
            response.StatusCode = statusCode;
            response.AddHeader("Access-Control-Allow-Origin", "*");
            response.AddHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
            response.AddHeader("Access-Control-Allow-Headers", "Content-Type");
            
            byte[] buffer = Encoding.UTF8.GetBytes(responseString);
            response.ContentLength64 = buffer.Length;
            response.OutputStream.Write(buffer, 0, buffer.Length);
            response.OutputStream.Close();
        }

        static string HandlePrintRequest(HttpListenerRequest request)
        {
            try
            {
                // قراءة البيانات من الطلب
                string body;
                using (StreamReader reader = new StreamReader(request.InputStream, request.ContentEncoding))
                {
                    body = reader.ReadToEnd();
                }

                if (string.IsNullOrEmpty(body))
                {
                    return jsonSerializer.Serialize(new
                    {
                        success = false,
                        message = "لم يتم إرسال بيانات"
                    });
                }

                // تحليل JSON
                Dictionary<string, object> data = jsonSerializer.Deserialize<Dictionary<string, object>>(body);

                if (!data.ContainsKey("printer") || !data.ContainsKey("content"))
                {
                    return jsonSerializer.Serialize(new
                    {
                        success = false,
                        message = "يجب تحديد اسم الطابعة والمحتوى"
                    });
                }

                string printerName = data["printer"].ToString();
                string content = data["content"].ToString();

                Console.WriteLine("  📄 طباعة على: {0}", printerName);

                // تنفيذ الطباعة
                bool success = PrintToRawPrinter(printerName, content);

                if (success)
                {
                    Console.WriteLine("  ✅ تمت الطباعة بنجاح");
                    return jsonSerializer.Serialize(new
                    {
                        success = true,
                        message = "تمت الطباعة بنجاح",
                        printer = printerName,
                        timestamp = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")
                    });
                }
                else
                {
                    Console.WriteLine("  ❌ فشلت الطباعة");
                    return jsonSerializer.Serialize(new
                    {
                        success = false,
                        message = "فشلت الطباعة",
                        printer = printerName
                    });
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("  ❌ خطأ: {0}", ex.Message);
                return jsonSerializer.Serialize(new
                {
                    success = false,
                    message = string.Format("خطأ: {0}", ex.Message)
                });
            }
        }

        static bool PrintToRawPrinter(string printerName, string content)
        {
            try
            {
                // التحقق من وجود الطابعة
                bool printerExists = PrinterSettings.InstalledPrinters.Cast<string>()
                    .Any(p => p.Equals(printerName, StringComparison.OrdinalIgnoreCase));

                if (!printerExists)
                {
                    Console.WriteLine("  ⚠️  الطابعة '{0}' غير موجودة", printerName);
                    return false;
                }

                // إنشاء ملف مؤقت
                string tempFile = Path.Combine(Path.GetTempPath(), string.Format("print_{0}.txt", Guid.NewGuid()));
                File.WriteAllText(tempFile, content, Encoding.UTF8);

                // طباعة الملف باستخدام notepad
                System.Diagnostics.ProcessStartInfo psi = new System.Diagnostics.ProcessStartInfo
                {
                    FileName = "notepad.exe",
                    Arguments = string.Format("/p \"{0}\"", tempFile),
                    CreateNoWindow = true,
                    WindowStyle = System.Diagnostics.ProcessWindowStyle.Hidden,
                    UseShellExecute = false
                };

                // تعيين الطابعة الافتراضية مؤقتاً
                string originalDefaultPrinter = new PrinterSettings().PrinterName;
                SetDefaultPrinter(printerName);

                // تنفيذ الطباعة
                System.Diagnostics.Process process = System.Diagnostics.Process.Start(psi);
                
                // الانتظار قليلاً ثم استعادة الطابعة الافتراضية
                System.Threading.Thread.Sleep(2000);
                SetDefaultPrinter(originalDefaultPrinter);

                // حذف الملف المؤقت
                try
                {
                    System.Threading.Thread.Sleep(1000);
                    File.Delete(tempFile);
                }
                catch { }

                return true;
            }
            catch (Exception ex)
            {
                Console.WriteLine("  ❌ خطأ في الطباعة: {0}", ex.Message);
                return false;
            }
        }

        [System.Runtime.InteropServices.DllImport("winspool.drv", CharSet = System.Runtime.InteropServices.CharSet.Auto, SetLastError = true)]
        static extern bool SetDefaultPrinter(string printerName);

        static string GetPrintersList()
        {
            List<string> printers = new List<string>();
            foreach (string printer in PrinterSettings.InstalledPrinters)
            {
                printers.Add(printer);
            }

            PrinterSettings settings = new PrinterSettings();
            
            return jsonSerializer.Serialize(new
            {
                success = true,
                printers = printers,
                default_printer = settings.PrinterName,
                count = printers.Count
            });
        }

        static string GetHealthStatus()
        {
            return jsonSerializer.Serialize(new
            {
                success = true,
                status = "running",
                timestamp = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")
            });
        }

        static string GetHomePage()
        {
            return @"
<!DOCTYPE html>
<html dir='rtl'>
<head>
    <meta charset='utf-8'>
    <title>وكيل طباعة المطبخ</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .status { color: green; font-weight: bold; font-size: 18px; }
        .endpoint { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; border-right: 4px solid #4CAF50; }
        code { background: #e0e0e0; padding: 2px 8px; border-radius: 3px; font-family: 'Courier New', monospace; }
        .method { display: inline-block; padding: 3px 8px; border-radius: 3px; font-weight: bold; margin-left: 10px; }
        .post { background: #4CAF50; color: white; }
        .get { background: #2196F3; color: white; }
        ol { line-height: 2; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🖨️ وكيل طباعة المطبخ</h1>
        <p class='status'>✅ الخادم يعمل بنجاح</p>
        
        <h2>نقاط النهاية المتاحة:</h2>
        
        <div class='endpoint'>
            <span class='method post'>POST</span>
            <strong>/print</strong><br>
            طباعة محتوى على طابعة محددة<br>
            <code>{""printer"": ""اسم_الطابعة"", ""content"": ""المحتوى""}</code>
        </div>
        
        <div class='endpoint'>
            <span class='method get'>GET</span>
            <strong>/printers</strong><br>
            الحصول على قائمة الطابعات المتاحة
        </div>
        
        <div class='endpoint'>
            <span class='method get'>GET</span>
            <strong>/health</strong><br>
            فحص صحة الخادم
        </div>
        
        <h2>كيفية الاستخدام:</h2>
        <ol>
            <li>تأكد من تثبيت الطابعات في Windows</li>
            <li>استخدم <code>GET /printers</code> لمعرفة أسماء الطابعات المتاحة</li>
            <li>أرسل طلب <code>POST /print</code> مع اسم الطابعة والمحتوى</li>
        </ol>
        
        <h2>اختبار سريع:</h2>
        <button onclick='testPrint()' style='padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>
            🖨️ اختبار الطباعة
        </button>
        <div id='result' style='margin-top: 10px;'></div>
        
        <script>
        async function testPrint() {
            const result = document.getElementById('result');
            result.innerHTML = '⏳ جاري الاختبار...';
            
            try {
                // الحصول على قائمة الطابعات
                const printersResponse = await fetch('/printers');
                const printersData = await printersResponse.json();
                
                if (printersData.printers.length === 0) {
                    result.innerHTML = '❌ لا توجد طابعات متاحة';
                    return;
                }
                
                const printer = printersData.default_printer;
                
                // إرسال طلب طباعة تجريبي
                const printResponse = await fetch('/print', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        printer: printer,
                        content: '================================\n' +
                                '      اختبار الطباعة\n' +
                                '================================\n' +
                                'التاريخ: ' + new Date().toLocaleString('ar-EG') + '\n' +
                                'الطابعة: ' + printer + '\n' +
                                '================================\n'
                    })
                });
                
                const printData = await printResponse.json();
                
                if (printData.success) {
                    result.innerHTML = '✅ تمت الطباعة بنجاح على: ' + printer;
                    result.style.color = 'green';
                } else {
                    result.innerHTML = '❌ فشلت الطباعة: ' + printData.message;
                    result.style.color = 'red';
                }
            } catch (error) {
                result.innerHTML = '❌ خطأ: ' + error.message;
                result.style.color = 'red';
            }
        }
        </script>
    </div>
</body>
</html>";
        }
    }
}
