<?php

namespace Modules\Inquiries\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Inquiries\Models\Inquiry;

class EngineerAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // جيب الـ inquiry ID من الـ route
        $inquiryId = $request->route('inquiry') ?? $request->route('id');

        if ($inquiryId) {
            $inquiry = Inquiry::with('assignedEngineers')->find($inquiryId);

            if ($inquiry) {
                // تأكد إن اليوزر ده مكلف بالاستفسار
                // لو مش مكلف، ارفض الوصول
                $isAssigned = $inquiry->assignedEngineers->contains('id', $user->id);

                if (!$isAssigned) {
                    abort(403, 'ليس لديك صلاحية للوصول لهذا الاستفسار. أنت غير مكلف به.');
                }
            }
        }

        return $next($request);
    }
}
