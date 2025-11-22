<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\Attendance;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Employee;
use Livewire\Attributes\Computed;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public string $search_employee_name = '';
    public string $search_employee_id = '';
    public string $search_fingerprint_name = '';
    public ?string $date_from = null;
    public ?string $date_to = null;

    // CRUD state
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showImportModal = false;
    public $editId = null;
    public $deleteId = null;
    
    // Excel import
    public $excelFile = null;
    public $isReadingFile = false;
    public $isFileRead = false;
    public $importProgress = 0;
    public $importTotalRows = 0;
    public $importPreviewData = [];
    public $importSuccessCount = 0;
    public $importFailedCount = 0;
    public $importErrors = [];
    public $form = [
        'employee_id' => '',
        'employee_attendance_finger_print_id' => '',
        'employee_attendance_finger_print_name' => '',
        'type' => 'check_in',
        'date' => '',
        'time' => '',
        'status' => 'pending',
        'notes' => '',
    ];

    public function mount()
    {
        $this->search_employee_name = '';
        $this->search_employee_id = '';
        $this->search_fingerprint_name = '';
        $this->date_from = null;
        $this->date_to = null;
    }

    public function updatedSearchEmployeeName()
    {
        $this->resetPage();
    }
    public function updatedSearchEmployeeId()
    {
        $this->resetPage();
    }
    public function updatedSearchFingerprintName()
    {
        $this->resetPage();
    }
    public function updatedDateFrom()
    {
        $this->resetPage();
    }
    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search_employee_name = '';
        $this->search_employee_id = '';
        $this->search_fingerprint_name = '';
        $this->date_from = null;
        $this->date_to = null;
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Attendance::query();

        if ($this->search_employee_name) {
            $query->whereHas('employee', function ($q) {
                $q->where('name', 'like', '%' . $this->search_employee_name . '%');
            });
        }
        if ($this->search_employee_id) {
            $query->where('employee_id', $this->search_employee_id);
        }
        if ($this->search_fingerprint_name) {
            $query->where('employee_attendance_finger_print_name', 'like', '%' . $this->search_fingerprint_name . '%');
        }
        if ($this->date_from && $this->date_to) {
            $query->whereBetween('date', [$this->date_from, $this->date_to]);
        } elseif ($this->date_from) {
            $query->where('date', '>=', $this->date_from);
        } elseif ($this->date_to) {
            $query->where('date', '<=', $this->date_to);
        }

        return [
            'attendances' => $query
                ->with(['employee', 'user'])
                ->latest()
                ->paginate(10),
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }
    /**
     * Store new attendance record.
     */
    public function store(): void
    {
        $this->validate([
            'form.employee_id' => 'required|exists:employees,id',
            'form.employee_attendance_finger_print_id' => 'required|integer',
            'form.employee_attendance_finger_print_name' => 'required|string',
            'form.type' => 'required|in:check_in,check_out',
            'form.date' => 'required|date',
            'form.time' => 'required',
            'form.status' => 'required|in:pending,approved,rejected',
            'form.notes' => 'nullable|string',
        ]);
        
        $data = $this->form;
        $data['user_id'] = Auth::id();
        
        Attendance::create($data);
        $this->showCreateModal = false;
        $this->resetForm();
        session()->flash('success', __('hr.attendance_created_successfully'));
    }
    /**
     * Open edit modal and load attendance data.
     *
     * @param int $id
     */
    public function edit(int $id): void
    {
        $attendance = Attendance::findOrFail($id);
        if ($attendance->status === 'approved') {
            session()->flash('error', __('hr.cannot_edit_approved_attendance'));
            return;
        }
        $this->editId = $id;
        $this->form = [
            'employee_id' => $attendance->employee_id,
            'employee_attendance_finger_print_id' => $attendance->employee_attendance_finger_print_id,
            'employee_attendance_finger_print_name' => $attendance->employee_attendance_finger_print_name,
            'type' => $attendance->type,
            'date' => $attendance->date ? Carbon::parse($attendance->date)->format('Y-m-d') : '',
            'time' => $attendance->time,
            'status' => $attendance->status,
            'notes' => $attendance->notes ?? '',
        ];
        $this->showEditModal = true;
    }

    /**
     * Update attendance record.
     */
    public function update(): void
    {
        $attendance = Attendance::findOrFail($this->editId);
        if ($attendance->status === 'approved') {
            session()->flash('error', __('hr.cannot_edit_approved_attendance'));
            $this->showEditModal = false;
            return;
        }
        $this->validate([
            'form.employee_id' => 'required|exists:employees,id',
            'form.employee_attendance_finger_print_id' => 'required|integer',
            'form.employee_attendance_finger_print_name' => 'required|string',
            'form.type' => 'required|in:check_in,check_out',
            'form.date' => 'required|date',
            'form.time' => 'required',
            'form.status' => 'required|in:pending,approved,rejected',
            'form.notes' => 'nullable|string',
        ]);
        
        $data = $this->form;
        
        $attendance->update($data);
        $this->showEditModal = false;
        $this->resetForm();
        session()->flash('success', __('hr.attendance_updated_successfully'));
    }

    /**
     * Delete attendance record.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $attendance = Attendance::findOrFail($id);
        if ($attendance->status === 'approved') {
            session()->flash('error', __('hr.cannot_delete_approved_attendance'));
            return;
        }
        $attendance->delete();
        session()->flash('success', __('hr.attendance_deleted_successfully'));
    }
    public function resetForm()
    {
        $this->form = [
            'employee_id' => '',
            'employee_attendance_finger_print_id' => '',
            'employee_attendance_finger_print_name' => '',
            'type' => 'check_in',
            'date' => now()->format('Y-m-d'),
            'time' => '',
            'status' => 'pending',
            'notes' => '',
        ];
        $this->editId = null;
        $this->deleteId = null;
    }
    /**
     * Get employees list for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Employee>
     */
    #[Computed]
    public function employees()
    {
        return Employee::orderBy('name')->get();
    }
    public function updatedFormEmployeeId($value)
    {
        $employee = Employee::find($value);
        $this->form['employee_attendance_finger_print_id'] = $employee?->finger_print_id ?? '';
        $this->form['employee_attendance_finger_print_name'] = $employee?->finger_print_name ?? '';
    }

    public function openImportModal()
    {
        $this->showImportModal = true;
        $this->resetImportState();
    }

    public function resetImportState()
    {
        $this->isReadingFile = false;
        $this->isFileRead = false;
        $this->importProgress = 0;
        $this->importTotalRows = 0;
        $this->importPreviewData = [];
        $this->importSuccessCount = 0;
        $this->importFailedCount = 0;
        $this->importErrors = [];
        $this->excelFile = null;
    }

    /**
     * Determine attendance type (check_in or check_out) based on time and shift ranges
     */
    private function determineAttendanceType(Employee $employee, string $time): string
    {
        // If employee has no shift, default to check_in
        if (!$employee->shift) {
            return 'check_in';
        }

        $shift = $employee->shift;
        
        // Parse time strings as time-only (using today's date for comparison)
        $today = Carbon::today();
        $attendanceTime = Carbon::createFromTimeString($today->format('Y-m-d') . ' ' . $time);

        // First, check if time is within explicit check-in range
        if ($shift->beginning_check_in && $shift->ending_check_in) {
            $beginningCheckIn = Carbon::createFromTimeString($today->format('Y-m-d') . ' ' . $shift->beginning_check_in);
            $endingCheckIn = Carbon::createFromTimeString($today->format('Y-m-d') . ' ' . $shift->ending_check_in);
            
            // Handle night shifts where check-in range might cross midnight
            if ($endingCheckIn->lt($beginningCheckIn)) {
                // Range crosses midnight
                if ($attendanceTime->gte($beginningCheckIn) || $attendanceTime->lte($endingCheckIn)) {
                    return 'check_in';
                }
            } else {
                // Normal range within same day
                if ($attendanceTime->between($beginningCheckIn, $endingCheckIn)) {
                    return 'check_in';
                }
            }
        }

        // Check if time is within explicit check-out range
        if ($shift->beginning_check_out && $shift->ending_check_out) {
            $beginningCheckOut = Carbon::createFromTimeString($today->format('Y-m-d') . ' ' . $shift->beginning_check_out);
            $endingCheckOut = Carbon::createFromTimeString($today->format('Y-m-d') . ' ' . $shift->ending_check_out);
            
            // Handle night shifts where check-out range might cross midnight
            if ($endingCheckOut->lt($beginningCheckOut)) {
                // Range crosses midnight
                if ($attendanceTime->gte($beginningCheckOut) || $attendanceTime->lte($endingCheckOut)) {
                    return 'check_out';
                }
            } else {
                // Normal range within same day
                if ($attendanceTime->between($beginningCheckOut, $endingCheckOut)) {
                    return 'check_out';
                }
            }
        }

        // If no specific range matches, use a heuristic:
        // Calculate distance from shift start and end times to determine type
        if ($shift->start_time && $shift->end_time) {
            $shiftStart = Carbon::createFromTimeString($today->format('Y-m-d') . ' ' . $shift->start_time);
            $shiftEnd = Carbon::createFromTimeString($today->format('Y-m-d') . ' ' . $shift->end_time);
            
            // Check if this is a night shift (crosses midnight)
            $isNightShift = $shiftEnd->lt($shiftStart);
            
            if ($isNightShift) {
                // Night shift: end time is next day
                // Create comparison times for distance calculation
                $shiftEndNextDay = $shiftEnd->copy()->addDay();
                
                // Calculate distances in minutes (considering night shift)
                $distanceFromStart = abs($attendanceTime->diffInMinutes($shiftStart));
                
                // For night shifts, check distance to both today's end and next day's end
                $distanceFromEndToday = abs($attendanceTime->diffInMinutes($shiftEnd));
                $distanceFromEndNextDay = abs($attendanceTime->diffInMinutes($shiftEndNextDay));
                $distanceFromEnd = min($distanceFromEndToday, $distanceFromEndNextDay);
                
                // For night shifts:
                // - If time is very early (before shift start), it's likely check_in
                // - If time is very late (after next day's shift end), it's likely check_out
                // - Otherwise, compare distances
                if ($attendanceTime->lt($shiftStart)) {
                    // Very early (before shift start), likely check_in
                    return 'check_in';
                }
                
                if ($attendanceTime->gt($shiftEndNextDay)) {
                    // Very late (after shift end next day), likely check_out
                    return 'check_out';
                }
            } else {
                // Regular day shift
                $distanceFromStart = abs($attendanceTime->diffInMinutes($shiftStart));
                $distanceFromEnd = abs($attendanceTime->diffInMinutes($shiftEnd));
                
                // Check if time is before shift start or after shift end
                // Times before shift start are usually check_in (early arrival)
                if ($attendanceTime->lt($shiftStart)) {
                    // Check if it's very early (before 6 AM) - might be previous day's check_out
                    $sixAM = Carbon::createFromTimeString($today->format('Y-m-d') . ' 06:00:00');
                    if ($attendanceTime->lt($sixAM)) {
                        // Very early time (before 6 AM), likely previous day's check_out
                        return 'check_out';
                    }
                    // Otherwise, it's likely check_in (early arrival)
                    return 'check_in';
                }
                
                // Times after shift end are usually check_out (late departure)
                if ($attendanceTime->gt($shiftEnd)) {
                    // Check if it's very late (after 11 PM) - might be check_out from current day
                    $elevenPM = Carbon::createFromTimeString($today->format('Y-m-d') . ' 23:00:00');
                    if ($attendanceTime->gt($elevenPM)) {
                        // Very late time (after 11 PM), definitely check_out
                        return 'check_out';
                    }
                    // Check if it's closer to shift end than to next day's shift start
                    // If after 8 PM and shift end is around 5-6 PM, it's likely check_out
                    $eightPM = Carbon::createFromTimeString($today->format('Y-m-d') . ' 20:00:00');
                    if ($attendanceTime->gt($eightPM)) {
                        return 'check_out';
                    }
                    // Otherwise, it's likely check_out (late departure)
                    return 'check_out';
                }
            }
            
            // If time is within shift duration (or ambiguous):
            // - If closer to start, it's check_in
            // - If closer to end, it's check_out
            return $distanceFromStart < $distanceFromEnd ? 'check_in' : 'check_out';
        }
        
        // If shift has only start_time, check proximity to it
        if ($shift->start_time) {
            $shiftStart = Carbon::createFromTimeString($today->format('Y-m-d') . ' ' . $shift->start_time);
            $distanceFromStart = abs($attendanceTime->diffInHours($shiftStart));
            
            // If time is close to start (within 2 hours), it's check_in
            if ($distanceFromStart <= 2) {
                return 'check_in';
            }
            
            // Otherwise, default to check_out
            return 'check_out';
        }

        // Default to check_in if no shift info
        return 'check_in';
    }

    public function importExcel()
    {
        try {
            $this->validate([
                'excelFile' => 'required|file|mimes:xls,xlsx,csv|max:10240', // 10MB max
            ]);

            if (!$this->excelFile) {
                session()->flash('error', __('الملف غير موجود'));
                return;
            }
            
            $file = $this->excelFile;

            // Load spreadsheet from temporary uploaded file
            $filePath = $file->getRealPath();
            
            // Load spreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            // Get headers from first row
            $headers = [];
            $headerRow = 1;
            foreach ($sheet->getRowIterator($headerRow, $headerRow) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $colIndex = 0;
                foreach ($cellIterator as $cell) {
                    $headers[$colIndex] = trim($cell->getValue() ?? '');
                    $colIndex++;
                }
                break;
            }

            // Find AC-No., Name, and Time column indices (all required)
            // Note: Time column contains both date and time combined
            $acNoIndex = null;
            $nameIndex = null;
            $timeIndex = null;

            foreach ($headers as $index => $header) {
                $headerLower = strtolower(trim($header));
                if (str_contains($headerLower, 'ac-no') || str_contains($headerLower, 'ac no') || str_contains($headerLower, 'acno')) {
                    $acNoIndex = $index;
                }
                if (str_contains($headerLower, 'name') || str_contains($headerLower, 'اسم')) {
                    $nameIndex = $index;
                }
                if (str_contains($headerLower, 'time') || str_contains($headerLower, 'وقت')) {
                    $timeIndex = $index;
                }
            }

            if ($acNoIndex === null) {
                session()->flash('error', __('hr.column_not_found', ['column' => 'AC-No.']));
                return;
            }

            if ($nameIndex === null) {
                session()->flash('error', __('hr.column_not_found', ['column' => 'Name']));
                return;
            }

            if ($timeIndex === null) {
                session()->flash('error', __('hr.time_column_required'));
                return;
            }

            // Reset state
            $this->isReadingFile = true;
            $this->isFileRead = false;
            $this->importPreviewData = [];
            $this->importSuccessCount = 0;
            $this->importFailedCount = 0;
            $this->importErrors = [];
            $this->importTotalRows = max(0, $highestRow - 1); // Exclude header row
            $this->importProgress = 0;

            $previewData = [];
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            // Process data rows (skip header row)
            for ($row = 2; $row <= $highestRow; $row++) {
                // Update progress
                $this->importProgress = $row - 1; // Current row minus header
                try {
                    $acNo = $sheet->getCellByColumnAndRow($acNoIndex + 1, $row)->getValue();
                    $name = $sheet->getCellByColumnAndRow($nameIndex + 1, $row)->getValue();
                    $dateTimeValue = $sheet->getCellByColumnAndRow($timeIndex + 1, $row)->getValue();
                    
                    // Skip empty rows (all required fields must be present)
                    if (empty($acNo) || empty($name) || empty($dateTimeValue)) {
                        continue;
                    }

                    // Parse date and time from combined column (format: "03/01/2025 04:20 ص" or "03/01/2025 04:20 م")
                    $date = null;
                    $time = null;
                    
                    try {
                        $dateTimeString = trim($dateTimeValue);
                        
                        // Handle Excel date serial number (if numeric)
                        if (is_numeric($dateTimeString)) {
                            $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateTimeString);
                            $date = $dateTime->format('Y-m-d');
                            $time = $dateTime->format('H:i:s');
                        } else {
                            // Handle string format: "03/01/2025 04:20 ص" or "03/01/2025 04:20 م"
                            // Also handle corrupted encoding: "03/01/2025 04:20 Õ" (Õ = ص) or "03/01/2025 11:42 ã" (ã = م)
                            // Remove any extra spaces and normalize
                            $dateTimeString = preg_replace('/\s+/', ' ', $dateTimeString);
                            
                            // Check if it contains Arabic AM/PM indicators (ص/م)
                            // Also check for corrupted encoding symbols (Õ = ص, ã = م)
                            $isAM = false;
                            $isPM = false;
                            
                            // Check for AM indicators (including corrupted encoding)
                            if (str_contains($dateTimeString, 'ص') || 
                                str_contains($dateTimeString, 'Õ') || 
                                str_contains($dateTimeString, 'صباحاً') || 
                                str_contains($dateTimeString, 'AM') || 
                                str_contains($dateTimeString, 'am')) {
                                $isAM = true;
                                // Replace all variations including corrupted encoding
                                $dateTimeString = str_replace(['ص', 'Õ', 'صباحاً', 'AM', 'am'], '', $dateTimeString);
                            } 
                            // Check for PM indicators (including corrupted encoding)
                            elseif (str_contains($dateTimeString, 'م') || 
                                    str_contains($dateTimeString, 'ã') || 
                                    str_contains($dateTimeString, 'مساءً') || 
                                    str_contains($dateTimeString, 'PM') || 
                                    str_contains($dateTimeString, 'pm')) {
                                $isPM = true;
                                // Replace all variations including corrupted encoding
                                $dateTimeString = str_replace(['م', 'ã', 'مساءً', 'PM', 'pm'], '', $dateTimeString);
                            }
                            
                            $dateTimeString = trim($dateTimeString);
                            
                            // Try to parse as "DD/MM/YYYY HH:MM" format
                            // Match pattern: "03/01/2025 04:20"
                            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}):(\d{1,2})/', $dateTimeString, $matches)) {
                                $day = (int)$matches[1];
                                $month = (int)$matches[2];
                                $year = (int)$matches[3];
                                $hour = (int)$matches[4];
                                $minute = (int)$matches[5];
                                
                                // Handle AM/PM conversion
                                if ($isPM && $hour < 12) {
                                    $hour += 12;
                                } elseif ($isAM && $hour == 12) {
                                    $hour = 0;
                                }
                                
                                // Format date and time
                                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $time = sprintf('%02d:%02d:00', $hour, $minute);
                            } else {
                                // Try Carbon parse as fallback
                                $parsedDateTime = Carbon::parse($dateTimeString);
                                $date = $parsedDateTime->format('Y-m-d');
                                $time = $parsedDateTime->format('H:i:s');
                            }
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        $errors[] = __('hr.row_error_datetime_invalid', [
                            'row' => $row,
                            'error' => $e->getMessage(),
                            'value' => $dateTimeValue
                        ]);
                        continue;
                    }

                    // Normalize name: trim and normalize spaces
                    $normalizedName = trim($name);
                    $normalizedName = preg_replace('/\s+/', ' ', $normalizedName); // Normalize multiple spaces to single space
                    
                    // Find employee by both finger_print_id AND finger_print_name (both must match)
                    // Try exact match first
                    $employee = Employee::where('finger_print_id', (int)$acNo)
                        ->where('finger_print_name', $normalizedName)
                        ->first();
                    
                    // If not found, try with trimmed database values (case-insensitive search)
                    if (!$employee) {
                        $employee = Employee::where('finger_print_id', (int)$acNo)
                            ->whereRaw('TRIM(finger_print_name) = ?', [trim($normalizedName)])
                            ->first();
                    }
                    
                    // If still not found, try like search (partial match) as last resort
                    if (!$employee) {
                        $employee = Employee::where('finger_print_id', (int)$acNo)
                            ->where('finger_print_name', 'LIKE', '%' . trim($normalizedName) . '%')
                            ->first();
                    }
                    
                    if (!$employee) {
                        $failedCount++;
                        
                        // Get employees with same finger_print_id for debugging
                        $similarEmployees = Employee::where('finger_print_id', (int)$acNo)
                            ->select('finger_print_name', 'name')
                            ->get();
                        
                        $errorMsg = __('hr.row_error_employee_not_found', [
                            'row' => $row,
                            'acNo' => $acNo,
                            'name' => $normalizedName
                        ]);
                        
                        if ($similarEmployees->count() > 0) {
                            $names = $similarEmployees->pluck('finger_print_name')->filter()->unique()->implode(', ');
                            if ($names) {
                                $errorMsg .= __('hr.found_in_database', ['names' => $names]);
                            } else {
                                $regularNames = $similarEmployees->pluck('name')->filter()->unique()->implode(', ');
                                if ($regularNames) {
                                    $errorMsg .= __('hr.employees_with_same_fingerprint', ['names' => $regularNames]);
                                }
                            }
                        } else {
                            $errorMsg .= __('hr.no_employee_with_fingerprint', ['acNo' => $acNo]);
                        }
                        
                        $errors[] = $errorMsg;
                        continue;
                    }

                    // Determine type (check_in or check_out) based on time and shift ranges
                    $type = $this->determineAttendanceType($employee, $time);

                    // Check if attendance record already exists for this employee, date, and time
                    $existingAttendance = Attendance::where('employee_id', $employee->id)
                        ->where('employee_attendance_finger_print_id', (int)$acNo)
                        ->where('date', $date)
                        ->where('time', $time)
                        ->first();

                    if ($existingAttendance) {
                        $failedCount++;
                        $errorMsg = __('hr.row_error_duplicate_record', ['row' => $row]);
                        $errors[] = $errorMsg;
                        continue;
                    }

                    // Store in preview data instead of creating immediately
                    $previewData[] = [
                        'employee_id' => $employee->id,
                        'employee_attendance_finger_print_id' => (int)$acNo,
                        'employee_attendance_finger_print_name' => trim($name),
                        'employee_name' => $employee->name,
                        'type' => $type,
                        'date' => $date,
                        'time' => $time,
                        'row_number' => $row,
                    ];

                    $successCount++;

                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = __('hr.row_error_general', ['row' => $row, 'error' => $e->getMessage()]);
                }
            }

            // Store preview data and results
            $this->importPreviewData = $previewData;
            $this->importSuccessCount = $successCount;
            $this->importFailedCount = $failedCount;
            $this->importErrors = $errors;
            $this->isReadingFile = false;
            $this->isFileRead = true;

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isReadingFile = false;
            session()->flash('error', __('Validation error: :errors', ['errors' => implode(', ', $e->errors()['excelFile'] ?? [])]));
        } catch (\Exception $e) {
            $this->isReadingFile = false;
            session()->flash('error', __('hr.import_file_error', ['error' => $e->getMessage()]));
        }
    }

    public function confirmImport()
    {
        try {
            if (empty($this->importPreviewData)) {
                session()->flash('error', __('hr.no_data_to_import'));
                return;
            }

            $savedCount = 0;
            $failedCount = 0;

            foreach ($this->importPreviewData as $data) {
                try {
                    // Check again if record exists (double check before saving)
                    $existingAttendance = Attendance::where('employee_id', $data['employee_id'])
                        ->where('employee_attendance_finger_print_id', $data['employee_attendance_finger_print_id'])
                        ->where('date', $data['date'])
                        ->where('time', $data['time'])
                        ->first();

                    if ($existingAttendance) {
                        $failedCount++;
                        continue;
                    }

                    // Create attendance record
                    Attendance::create([
                        'employee_id' => $data['employee_id'],
                        'employee_attendance_finger_print_id' => $data['employee_attendance_finger_print_id'],
                        'employee_attendance_finger_print_name' => $data['employee_attendance_finger_print_name'],
                        'type' => $data['type'],
                        'date' => $data['date'],
                        'time' => $data['time'],
                        'status' => 'pending',
                        'user_id' => Auth::id(),
                    ]);

                    $savedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                }
            }

            // Flash results
            if ($savedCount > 0) {
                session()->flash('success', __('hr.import_saved_successfully', ['count' => $savedCount]));
            }
            if ($failedCount > 0) {
                session()->flash('error', __('hr.import_failed_count', ['count' => $failedCount]));
            }

            // Reset and close modal
            $this->resetImportState();
            $this->showImportModal = false;
            $this->resetPage();

        } catch (\Exception $e) {
            session()->flash('error', __('hr.import_save_error', ['error' => $e->getMessage()]));
        }
    }
}; ?>

<div dir="rtl" style="font-family: 'Cairo', sans-serif;">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show font-family-cairo" role="alert">
            <i class="las la-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show font-family-cairo" role="alert">
            <i class="las la-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-3">
        @can('create Attendances')
            <div class="col-12 d-flex justify-content-end gap-2">
                <button class="btn btn-success font-family-cairo fw-bold" wire:click="openImportModal">
                    <i class="las la-file-excel me-2"></i> {{ __('hr.import_from_excel') }}
                </button>
                <button class="btn btn-primary font-family-cairo fw-bold" wire:click="create">
                    <i class="las la-plus me-2"></i> {{ __('hr.add_attendance') }}
                </button>
            </div>
        @endcan


    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 font-family-cairo fw-bold">{{ __('hr.attendance_records') }}</h5>
                    <div class="row w-100 align-items-center">
                        <div class="col-md-2">
                            <input type="text" 
                                   class="form-control font-family-cairo"
                                   placeholder="{{ __('hr.employee_name') }}"
                                   wire:model.live.debounce.500ms="search_employee_name">
                        </div>
                        <div class="col-md-2">
                            <input type="text" 
                                   class="form-control font-family-cairo"
                                   placeholder="{{ __('hr.employee_number') }}"
                                   wire:model.live.debounce.500ms="search_employee_id">
                        </div>
                        <div class="col-md-2">
                            <input type="text" 
                                   class="form-control font-family-cairo"
                                   placeholder="{{ __('hr.fingerprint_name') }}"
                                   wire:model.live.debounce.500ms="search_fingerprint_name">
                        </div>
                        <div class="col-md-2">
                            <input type="date" 
                                   class="form-control font-family-cairo" 
                                   wire:model.blur="date_from"
                                   placeholder="{{ __('hr.from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" 
                                   class="form-control font-family-cairo" 
                                   wire:model.blur="date_to"
                                   placeholder="{{ __('hr.to_date') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-center mt-2 mt-md-0">
                            <button type="button" 
                                    class="btn btn-outline-secondary font-family-cairo fw-bold w-100"
                                    wire:click="clearFilters">
                                <i class="las la-broom me-1"></i> {{ __('hr.clear_filters') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table
                            class="table table-striped table-hover table-bordered table-light text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.number') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.employee_name') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.employee_number') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.fingerprint_name') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.type') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.date') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.time') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.location') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.project') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.status') }}</th>
                                    <th class="font-family-cairo fw-bold">{{ __('hr.notes') }}</th>
                                    @canany(['edit Attendances', 'delete Attendances'])
                                        <th class="font-family-cairo fw-bold">{{ __('hr.actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->id }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->employee?->name ?? '-' }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->employee_id }}</td>
                                        <td class="font-family-cairo fw-bold">
                                            {{ $attendance->employee_attendance_finger_print_name }}
                                        </td>
                                        <td class="font-family-cairo fw-bold">
                                            {{ $attendance->type == 'check_in' ? __('hr.check_in') : __('hr.check_out') }}
                                        </td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->date->format('Y-m-d') }}
                                        </td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->time }}
                                        </td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->location_address ?? '-' }}</td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->project_code ?? '-' }}</td>
                                        <td class="font-family-cairo fw-bold">
                                            @if ($attendance->status == 'pending')
                                                <span class="badge bg-warning font-family-cairo">{{ __('hr.under_review') }}</span>
                                            @elseif($attendance->status == 'approved')
                                                <span class="badge bg-success font-family-cairo">{{ __('hr.approved') }}</span>
                                            @else
                                                <span class="badge bg-danger font-family-cairo">{{ __('hr.rejected') }}</span>
                                            @endif
                                        </td>
                                        <td class="font-family-cairo fw-bold">{{ $attendance->notes ?? '-' }}</td>
                                        @canany(['edit Attendances', 'delete Attendances'])
                                            <td class="font-family-cairo fw-bold">
                                                @if ($attendance->status !== 'approved')
                                                    <div class="btn-group" role="group">
                                                        @can('edit Attendances')
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-info me-1 font-family-cairo"
                                                                    wire:click="edit({{ $attendance->id }})"
                                                                    title="{{ __('hr.edit') }}">
                                                                {{ __('hr.edit') }}
                                                            </button>
                                                        @endcan
                                                        @can('delete Attendances')
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-danger font-family-cairo"
                                                                    wire:click="delete({{ $attendance->id }})"
                                                                    wire:confirm="{{ __('hr.confirm_delete_attendance') }}"
                                                                    title="{{ __('hr.delete') }}">
                                                                {{ __('hr.delete') }}
                                                            </button>
                                                        @endcan
                                                    </div>
                                                @else
                                                    <span class="text-muted font-family-cairo">{{ __('hr.cannot_edit_delete') }}</span>
                                                @endif
                                            </td>
                                        @endcanany

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->canany(['edit Attendances', 'delete Attendances']) ? '12' : '11' }}" 
                                            class="text-center font-family-cairo fw-bold py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('hr.no_attendance_records') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $attendances->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>


    </div>
    {{-- Create Modal --}}
    @if ($showCreateModal || $showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold">{{ __('hr.add_attendance') }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="store">
                            <div class="mb-3">
                                <label class="form-label font-family-cairo fw-bold">{{ __('hr.employee') }} <span class="text-danger">*</span></label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.blur="form.employee_id" required>
                                    <option class="text-muted font-family-cairo fw-bold font-14" value="">
                                        {{ __('hr.select_employee_option') }}
                                    </option>
                                    @foreach ($this->employees as $employee)
                                        <option class="font-family-cairo fw-bold font-14" value="{{ $employee->id }}">
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.employee_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('رقم البصمة') }}</label>
                                <input type="text" class="form-control font-family-cairo"
                                    value="{{ $this->form['employee_attendance_finger_print_id'] }}" disabled>
                                @error('form.employee_attendance_finger_print_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('اسم البصمة') }}</label>
                                <input type="text" class="form-control font-family-cairo"
                                    value="{{ $this->form['employee_attendance_finger_print_name'] }}" disabled>
                                @error('form.employee_attendance_finger_print_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('النوع') }}</label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.blur="form.type">
                                    <option class="font-family-cairo fw-bold font-14" value="check_in">
                                        {{ __('دخول') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="check_out">
                                        {{ __('خروج') }}
                                    </option>
                                </select>
                                @error('form.type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo">{{ __('التاريخ') }}</label>
                                <input type="date" class="form-control font-family-cairo"
                                    wire:model.blur="form.date">
                                @error('form.date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('الوقت') }}</label>
                                <input type="time" wire:model="form.time"
                                    class="form-control
                                        @error('form.time') <span class=" text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('الحالة') }}</label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.blur="form.status">
                                    <option class="font-family-cairo fw-bold font-14" value="pending">
                                        {{ __('قيد المراجعة') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="approved">
                                        {{ __('معتمد') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="rejected">
                                        {{ __('مرفوض') }}
                                    </option>
                                </select>
                                @error('form.status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label
                                    class="form-label font-family-cairo fw-bold font-14">{{ __('ملاحظات') }}</label>
                                <textarea class="form-control font-family-cairo fw-bold font-14" wire:model.blur="form.notes"></textarea>
                                @error('form.notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary font-family-cairo"
                                    wire:click="$set('showCreateModal', false)">{{ __('إلغاء') }}</button>
                                <button type="submit"
                                    class="btn btn-primary font-family-cairo">{{ __('حفظ') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{-- Edit Modal --}}
    @if ($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold">{{ __('hr.edit_attendance') }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="update">
                            {{-- Same fields as create modal, but bound to form and update --}}
                            <div class="mb-3">
                                <label class="form-label font-family-cairo fw-bold font-14">{{ __('hr.employee') }} <span class="text-danger">*</span></label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.blur="form.employee_id" required>
                                    <option class="text-muted font-family-cairo fw-bold font-14" value="">
                                        {{ __('hr.select_employee_option') }}
                                    </option>
                                    @foreach ($this->employees as $employee)
                                        <option class="font-family-cairo fw-bold font-14"
                                            value="{{ $employee->id }}">
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.employee_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo fw-bold font-14">{{ __('hr.fingerprint_number') }}</label>
                                <input type="text" 
                                       class="form-control font-family-cairo fw-bold font-14"
                                       value="{{ $this->form['employee_attendance_finger_print_id'] }}" 
                                       disabled>
                                @error('form.employee_attendance_finger_print_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo fw-bold font-14">{{ __('hr.fingerprint_name') }}</label>
                                <input type="text" 
                                       class="form-control font-family-cairo fw-bold font-14"
                                       value="{{ $this->form['employee_attendance_finger_print_name'] }}" 
                                       disabled>
                                @error('form.employee_attendance_finger_print_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo fw-bold font-14">{{ __('hr.type') }} <span class="text-danger">*</span></label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.blur="form.type" required>
                                    <option class="font-family-cairo fw-bold font-14" value="check_in">
                                        {{ __('hr.check_in') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="check_out">
                                        {{ __('hr.check_out') }}
                                    </option>
                                </select>
                                @error('form.type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo fw-bold font-14">{{ __('hr.date') }} <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control font-family-cairo fw-bold font-14"
                                       wire:model.blur="form.date" 
                                       required>
                                @error('form.date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo fw-bold font-14">{{ __('hr.time') }} <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control font-family-cairo fw-bold font-14"
                                       wire:model.blur="form.time" 
                                       required>
                                @error('form.time')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo fw-bold font-14">{{ __('hr.status') }} <span class="text-danger">*</span></label>
                                <select class="form-select font-family-cairo fw-bold font-14"
                                    wire:model.blur="form.status" required>
                                    <option class="font-family-cairo fw-bold font-14" value="pending">
                                        {{ __('hr.under_review') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="approved">
                                        {{ __('hr.approved') }}
                                    </option>
                                    <option class="font-family-cairo fw-bold font-14" value="rejected">
                                        {{ __('hr.rejected') }}
                                    </option>
                                </select>
                                @error('form.status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-family-cairo fw-bold font-14">{{ __('hr.notes') }}</label>
                                <textarea class="form-control font-family-cairo fw-bold font-14" 
                                          wire:model.blur="form.notes"
                                          rows="3"></textarea>
                                @error('form.notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary font-family-cairo"
                                    wire:click="$set('showEditModal', false)">{{ __('hr.cancel') }}</button>
                                <button type="submit"
                                    class="btn btn-primary font-family-cairo">{{ __('hr.save_changes') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold">{{ __('hr.confirm_delete') }}</h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('showDeleteModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p class="font-family-cairo">{{ __('hr.confirm_delete_attendance') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary font-family-cairo"
                            wire:click="$set('showDeleteModal', false)">{{ __('hr.cancel') }}</button>
                        <button type="button" class="btn btn-danger font-family-cairo"
                            wire:click="delete">{{ __('hr.delete') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Import Excel Modal --}}
    @if ($showImportModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold">{{ __('hr.import_attendance_from_excel') }}</h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('showImportModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <p class="font-family-cairo mb-2 fw-bold">{{ __('hr.important_notes') }}</p>
                            <ul class="font-family-cairo" style="font-size: 0.9rem;">
                                <li>{{ __('hr.excel_ac_no_column_required') }}</li>
                                <li>{{ __('hr.excel_name_column_required') }}</li>
                                <li>{{ __('hr.excel_time_column_required') }}</li>
                                <li>{{ __('hr.excel_time_column_format') }}</li>
                                <li>{{ __('hr.employee_search_fingerprint') }}</li>
                                <li>{{ __('hr.attendance_type_auto_determined') }}</li>
                                <li>{{ __('hr.date_time_required_processing') }}</li>
                                <li>{{ __('hr.employee_not_found_skipped') }}</li>
                                <li>{{ __('hr.duplicate_records_skipped') }}</li>
                            </ul>
                        </div>
                        <form wire:submit.prevent="importExcel">
                            <div class="mb-3">
                                <label class="form-label font-family-cairo fw-bold">{{ __('hr.excel_file') }}</label>
                                <input type="file" 
                                       class="form-control font-family-cairo" 
                                       accept=".xls,.xlsx,.csv"
                                       wire:model="excelFile"
                                       @if($isReadingFile || $isFileRead) disabled @endif>
                                @error('excelFile')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <small class="text-muted font-family-cairo">{{ __('hr.supported_formats') }}</small>
                            </div>

                            {{-- Progress Section --}}
                            @if($isReadingFile)
                                <div class="mb-3 p-3 bg-light rounded">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                            <span class="visually-hidden">جاري القراءة...</span>
                                        </div>
                                        <span class="font-family-cairo fw-bold">{{ __('hr.reading_file') }}</span>
                                    </div>
                                    @if($importTotalRows > 0)
                                        <div class="progress mb-2" style="height: 25px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                 role="progressbar" 
                                                 style="width: {{ $importTotalRows > 0 ? min(100, ($importProgress / $importTotalRows) * 100) : 0 }}%"
                                                 aria-valuenow="{{ $importProgress }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="{{ $importTotalRows }}">
                                                <span class="font-family-cairo fw-bold text-white">
                                                    {{ $importProgress }} / {{ $importTotalRows }} {{ __('Rows') }}
                                                </span>
                                            </div>
                                        </div>
                                        <small class="text-muted font-family-cairo">
                                            {{ __('hr.processed') }} {{ $importProgress }} {{ __('hr.of') }} {{ $importTotalRows }} {{ __('hr.rows') }}
                                        </small>
                                    @endif
                                </div>
                            @endif

                            {{-- Results Section --}}
                            @if($isFileRead)
                                <div class="mb-3 p-3 rounded" 
                                     style="background-color: {{ $importSuccessCount > 0 ? '#d4edda' : '#f8d7da' }};">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="las la-check-circle me-2" style="font-size: 1.5rem; color: #28a745;"></i>
                                        <span class="font-family-cairo fw-bold" style="font-size: 1.1rem;">
                                            {{ __('hr.file_read_successfully') }}
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-success me-2 font-family-cairo">
                                            {{ __('hr.successful') }} {{ $importSuccessCount }}
                                        </span>
                                        @if($importFailedCount > 0)
                                            <span class="badge bg-danger me-2 font-family-cairo">
                                                {{ __('hr.failed') }} {{ $importFailedCount }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($importFailedCount > 0 && count($importErrors) > 0 && count($importErrors) <= 5)
                                        <div class="mt-2">
                                            <small class="text-danger font-family-cairo">
                                                <strong>{{ __('hr.errors') }}</strong><br>
                                                @foreach(array_slice($importErrors, 0, 5) as $error)
                                                    • {{ $error }}<br>
                                                @endforeach
                                                @if(count($importErrors) > 5)
                                                    ... {{ __('hr.and') }} {{ count($importErrors) - 5 }} {{ __('hr.other_errors') }}
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary font-family-cairo"
                                    wire:click="resetImportState(); $set('showImportModal', false)"
                                    @if($isReadingFile) disabled @endif>
                                    {{ __('hr.cancel') }}
                                </button>
                                
                                @if(!$isFileRead)
                                    <button type="submit" class="btn btn-primary font-family-cairo"
                                        wire:loading.attr="disabled"
                                        @if($isReadingFile || !$excelFile) disabled @endif>
                                        <span wire:loading.remove wire:target="importExcel">
                                            <i class="las la-file-upload me-1"></i> {{ __('hr.read_and_analyze_file') }}
                                        </span>
                                        <span wire:loading wire:target="importExcel">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            {{ __('hr.reading_file') }}
                                        </span>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success font-family-cairo"
                                        wire:click="confirmImport"
                                        wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="confirmImport">
                                            <i class="las la-save me-1"></i> {{ __('hr.confirm_save_data') }}
                                        </span>
                                        <span wire:loading wire:target="confirmImport">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            {{ __('hr.saving_data') }}
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>


</div>


