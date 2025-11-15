<?php

namespace Modules\Services\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceBooking;
use Modules\Services\Http\Requests\ServiceBookingRequest;
use Illuminate\Http\RedirectResponse;

class ServiceBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = ServiceBooking::with(['service', 'customer', 'employee']);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('booking_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('booking_date', '<=', $request->get('date_to'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            switch ($status) {
                case 'pending':
                    $query->pending();
                    break;
                case 'completed':
                    $query->completed();
                    break;
                case 'cancelled':
                    $query->cancelled();
                    break;
            }
        }

        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->get('service_id'));
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->get('service_id'));
        }

        $bookings = $query->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        $services = Service::where('is_active', true)->orderBy('name')->get();
        $customers = \Modules\\Accounts\\Models\\AccHead::where('isdeleted', 0)
            ->where('aname', 'like', '%Ø¹Ù…ÙŠÙ„%')
            ->orderBy('aname')
            ->get();

            return view('services::bookings.index', compact('bookings', 'services', 'customers'));
        } catch (\Exception $e) {
            Log::error('Error in ServiceBookingController@index: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ø­Ø¯Ø« Ø®Ø·Ø£ ÙÙŠ ØªØ­Ù…ÙŠÙ„ Ø§Ù„ØµÙØ­Ø©: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $booking = new ServiceBooking();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $customers = \Modules\\Accounts\\Models\\AccHead::where('isdeleted', 0)
            ->where('aname', 'like', '%Ø¹Ù…ÙŠÙ„%')
            ->orderBy('aname')
            ->get();
        $employees = \Modules\\Accounts\\Models\\AccHead::where('isdeleted', 0)
            ->where('aname', 'like', '%Ù…ÙˆØ¸Ù%')
            ->orderBy('aname')
            ->get();

        return view('services::bookings.create', compact('booking', 'services', 'customers', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        // Calculate end time based on service duration (default 60 minutes)
        if (isset($data['service_id'])) {
            $service = Service::find($data['service_id']);
            if ($service) {
                $startTime = \Carbon\Carbon::parse($data['start_time']);
                $endTime = $startTime->addMinutes(60); // Default 60 minutes
                $data['end_time'] = $endTime->format('H:i:s');
                $data['price'] = $service->price;
            }
        }

        ServiceBooking::create($data);

        return redirect()->route('services.bookings.index')
            ->with('success', 'ØªÙ… Ø¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ø­Ø¬Ø² Ø¨Ù†Ø¬Ø§Ø­');
    }

    /**
     * Show the specified resource.
     */
    public function show(ServiceBooking $booking)
    {
        $booking->load(['service', 'customer', 'employee']);
        
        return view('services::bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceBooking $booking)
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $customers = \Modules\\Accounts\\Models\\AccHead::where('isdeleted', 0)
            ->where('aname', 'like', '%Ø¹Ù…ÙŠÙ„%')
            ->orderBy('aname')
            ->get();
        $employees = \Modules\\Accounts\\Models\\AccHead::where('isdeleted', 0)
            ->where('aname', 'like', '%Ù…ÙˆØ¸Ù%')
            ->orderBy('aname')
            ->get();

        return view('services::bookings.edit', compact('booking', 'services', 'customers', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceBookingRequest $request, ServiceBooking $booking): RedirectResponse
    {
        $data = $request->validated();
        
        // Calculate end time based on service duration if service changed (default 60 minutes)
        if (isset($data['service_id']) && $data['service_id'] != $booking->service_id) {
            $service = Service::find($data['service_id']);
            if ($service) {
                $startTime = \Carbon\Carbon::parse($data['start_time']);
                $endTime = $startTime->addMinutes(60); // Default 60 minutes
                $data['end_time'] = $endTime->format('H:i:s');
                $data['price'] = $service->price;
            }
        }

        $booking->update($data);

        return redirect()->route('services.bookings.index')
            ->with('success', 'ØªÙ… ØªØ­Ø¯ÙŠØ« Ø§Ù„Ø­Ø¬Ø² Ø¨Ù†Ø¬Ø§Ø­');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceBooking $booking): RedirectResponse
    {
        if ($booking->is_completed) {
            return redirect()->route('services.bookings.index')
                ->with('error', 'Ù„Ø§ ÙŠÙ…ÙƒÙ† Ø­Ø°Ù Ø­Ø¬Ø² Ù…ÙƒØªÙ…Ù„');
        }

        $booking->delete();

        return redirect()->route('services.bookings.index')
            ->with('success', 'ØªÙ… Ø­Ø°Ù Ø§Ù„Ø­Ø¬Ø² Ø¨Ù†Ø¬Ø§Ø­');
    }

    /**
     * Mark booking as completed.
     */
    public function complete(ServiceBooking $booking): RedirectResponse
    {
        $booking->update(['is_completed' => true]);

        return redirect()->route('services.bookings.index')
            ->with('success', 'ØªÙ… ØªØ£ÙƒÙŠØ¯ Ø¥ÙƒÙ…Ø§Ù„ Ø§Ù„Ø­Ø¬Ø²');
    }

    /**
     * Cancel booking.
     */
    public function cancel(Request $request, ServiceBooking $booking): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);

        $booking->update([
            'is_cancelled' => true,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->get('cancellation_reason')
        ]);

        return redirect()->route('services.bookings.index')
            ->with('success', 'ØªÙ… Ø¥Ù„ØºØ§Ø¡ Ø§Ù„Ø­Ø¬Ø²');
    }

    /**
     * Get available time slots for a service on a specific date.
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today'
        ]);

        $service = Service::find($request->get('service_id'));
        $date = $request->get('date');

        // Get existing bookings for the date
        $existingBookings = ServiceBooking::where('booking_date', $date)
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

