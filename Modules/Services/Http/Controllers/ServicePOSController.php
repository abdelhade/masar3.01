<?php

namespace Modules\Services\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Services\Models\Service;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Price;

class ServicePOSController extends Controller
{
    /**
     * Get services for POS integration
     */
    public function getServicesForPOS(Request $request)
    {
        try {
            $query = Service::with(['serviceType', 'serviceUnit'])
                ->where('is_active', true);

        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by service type
        if ($request->filled('service_type_id')) {
            $query->where('service_type_id', $request->get('service_type_id'));
        }

        $services = $query->orderBy('name')->get();

        // Transform services to match POS item format
        $posServices = $services->map(function ($service) {
            return [
                'id' => 'service_' . $service->id, // Prefix to distinguish from items
                'name' => $service->name,
                'code' => $service->code,
                'description' => $service->description,
                'price' => $service->price,
                'cost' => $service->cost,
                'is_service' => true,
                'service_id' => $service->id,
                'service_type' => $service->service_type,
                'is_taxable' => $service->is_taxable,
                'categories' => $service->categories->pluck('name')->toArray(),
                'units' => $service->units->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'name' => $unit->name,
                        'u_val' => $unit->pivot->u_val,
                        'cost' => $unit->pivot->cost,
                    ];
                }),
                'prices' => $service->prices->map(function ($price) {
                    return [
                        'id' => $price->id,
                        'name' => $price->name,
                        'unit_id' => $price->pivot->unit_id,
                        'price' => $price->pivot->price,
                        'discount' => $price->pivot->discount,
                        'tax_rate' => $price->pivot->tax_rate,
                    ];
                }),
            ];
        });

            return response()->json($posServices);
        } catch (\Exception $e) {
            Log::error('Error in ServicePOSController@getServicesForPOS: ' . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ في تحميل الخدمات'], 500);
        }
    }

    /**
     * Get a specific service for POS
     */
    public function getServiceForPOS($id)
    {
        $service = Service::with(['serviceType', 'serviceUnit'])
            ->where('is_active', true)
            ->findOrFail($id);

        $posService = [
            'id' => 'service_' . $service->id,
            'name' => $service->name,
            'code' => $service->code,
            'description' => $service->description,
            'price' => $service->price,
            'cost' => $service->cost,
            'is_service' => true,
            'service_id' => $service->id,
            'service_type' => $service->service_type,
            'is_taxable' => $service->is_taxable,
            'categories' => $service->categories->pluck('name')->toArray(),
            'units' => $service->units->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'u_val' => $unit->pivot->u_val,
                    'cost' => $unit->pivot->cost,
                ];
            }),
            'prices' => $service->prices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'name' => $price->name,
                    'unit_id' => $price->pivot->unit_id,
                    'price' => $price->pivot->price,
                    'discount' => $price->pivot->discount,
                    'tax_rate' => $price->pivot->tax_rate,
                ];
            }),
        ];

        return response()->json($posService);
    }

    /**
     * Create a service booking from POS transaction
     */
    public function createBookingFromPOS(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'customer_id' => 'required|exists:acc_head,id',
            'employee_id' => 'nullable|exists:acc_head,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ]);

        $service = Service::findOrFail($request->get('service_id'));
        
        // Calculate end time based on service duration (default 60 minutes)
        $startTime = \Carbon\Carbon::parse($request->get('start_time'));
        $endTime = $startTime->addMinutes(60); // Default 60 minutes

        $booking = \Modules\Services\Models\ServiceBooking::create([
            'service_id' => $service->id,
            'customer_id' => $request->get('customer_id'),
            'employee_id' => $request->get('employee_id'),
            'booking_date' => $request->get('booking_date'),
            'start_time' => $request->get('start_time'),
            'end_time' => $endTime->format('H:i:s'),
            'price' => $service->price,
            'notes' => $request->get('notes'),
            'created_by' => Auth::check() ? Auth::id() : 1,
        ]);

        return response()->json([
            'success' => true,
            'booking' => $booking,
            'message' => 'تم إنشاء حجز الخدمة بنجاح'
        ]);
    }

    /**
     * Get available time slots for a service
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today'
        ]);

        $service = Service::findOrFail($request->get('service_id'));
        $date = $request->get('date');

        // Get existing bookings for the date
        $existingBookings = \Modules\Services\Models\ServiceBooking::where('booking_date', $date)
            ->where('is_cancelled', false)
            ->get();

        // Generate available time slots (assuming 30-minute intervals)
        $slots = [];
        $startHour = 8; // 8 AM
        $endHour = 18; // 6 PM

        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                $time = sprintf('%02d:%02d:00', $hour, $minute);
                $endTime = \Carbon\Carbon::parse($time)->addMinutes(60)->format('H:i:s'); // Default 60 minutes

                // Check if this slot conflicts with existing bookings
                $conflict = $existingBookings->filter(function ($booking) use ($time, $endTime) {
                    return ($time >= $booking->start_time && $time < $booking->end_time) ||
                           ($endTime > $booking->start_time && $endTime <= $booking->end_time) ||
                           ($time <= $booking->start_time && $endTime >= $booking->end_time);
                })->count() > 0;

                if (!$conflict) {
                    $slots[] = [
                        'time' => $time,
                        'end_time' => $endTime,
                        'formatted' => \Carbon\Carbon::parse($time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($endTime)->format('H:i')
                    ];
                }
            }
        }

        return response()->json($slots);
    }
}
