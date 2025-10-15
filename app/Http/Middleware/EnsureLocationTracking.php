<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureLocationTracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // إضافة meta tags للـ location tracking في الصفحات المطلوبة
        if ($request->is('hr-management/*') || $request->is('dashboard') || $request->is('/')) {
            $content = $response->getContent();
            
            // إضافة meta tag للـ user ID إذا لم يكن موجود
            if (Auth::check() && !str_contains($content, 'name="user-id"')) {
                $userMeta = '<meta name="user-id" content="' . Auth::id() . '">';
                $content = str_replace('</head>', $userMeta . "\n</head>", $content);
            }
            
            // إضافة script للـ location tracking إذا لم يكن موجود
            if (!str_contains($content, 'location-tracker.js')) {
                $locationScript = '
                <script>
                    // تهيئة تتبع الموقع
                    document.addEventListener("DOMContentLoaded", function() {
                        if (typeof LocationTracker !== "undefined" && !window.locationTracker) {
                            const googleApiKey = "' . config('services.google.maps_api_key') . '";
                            window.locationTracker = new LocationTracker();
                            window.locationTracker.init(googleApiKey);
                            window.locationTracker.restoreTracking();
                        }
                    });
                </script>';
                $content = str_replace('</body>', $locationScript . "\n</body>", $content);
            }
            
            $response->setContent($content);
        }
        
        return $response;
    }
}
