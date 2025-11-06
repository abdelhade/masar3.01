<?php

namespace Modules\Inquiries\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Inquiries\Models\{Contact, Inquiry};
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Inquiries\Models\UserInquiryPreference;

class InquiriesController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:View Inquiries')->only(['index', 'show', 'drafts']);
        $this->middleware('can:Create Inquiries')->only(['create', 'store']);
        $this->middleware('can:Edit Inquiries')->only(['edit', 'update']);
        $this->middleware('can:Delete Inquiries')->only('destroy');
    }
    public function index(Request $request)
    {
        $user = Auth::user();

        // جلب أو إنشاء تفضيلات اليوزر
        $preferences = UserInquiryPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'visible_columns' => UserInquiryPreference::getDefaultColumns(),
                'filters' => []
            ]
        );

        // بناء الـ Query
        $query = Inquiry::with([
            'project',
            'city',
            'town',
            'contacts.roles',
            'workType',
            'inquirySource'
        ]);

        // تطبيق الفلاتر
        $filters = $request->get('filters', $preferences->filters ?? []);

        if (!empty($filters)) {
            foreach ($filters as $column => $value) {
                if (empty($value)) continue;

                switch ($column) {
                    case 'project':
                        $query->whereHas(
                            'project',
                            fn($q) => $q->where('name', 'like', "%{$value}%")
                        );
                        break;

                    case 'client':
                        $query->whereHas('contacts', function ($q) use ($value) {
                            $q->where('cname', 'like', "%{$value}%")
                                ->whereHas('roles', function ($roleQuery) {
                                    $roleQuery->where('name', 'Client');
                                });
                        });
                        break;

                    case 'main_contractor':
                        $query->whereHas('contacts', function ($q) use ($value) {
                            $q->where('cname', 'like', "%{$value}%")
                                ->whereHas('roles', function ($roleQuery) {
                                    $roleQuery->where('name', 'Main Contractor');
                                });
                        });
                        break;

                    case 'consultant':
                        $query->whereHas('contacts', function ($q) use ($value) {
                            $q->where('cname', 'like', "%{$value}%")
                                ->whereHas('roles', function ($roleQuery) {
                                    $roleQuery->where('name', 'Consultant');
                                });
                        });
                        break;

                    case 'owner':
                        $query->whereHas('contacts', function ($q) use ($value) {
                            $q->where('cname', 'like', "%{$value}%")
                                ->whereHas('roles', function ($roleQuery) {
                                    $roleQuery->where('name', 'Owner');
                                });
                        });
                        break;

                    case 'assigned_engineer':
                        $query->whereHas('contacts', function ($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%")
                                ->whereHas('roles', function ($roleQuery) {
                                    $roleQuery->where('name', 'Engineer');
                                });
                        });
                        break;

                    case 'contact_type':
                        $query->whereHas('contacts', function ($q) use ($value) {
                            $q->where('type', $value);
                        });
                        break;

                    case 'status':
                    case 'status_for_kon':
                    case 'kon_title':
                    case 'quotation_state':
                    case 'client_priority':
                    case 'kon_priority':
                        $query->where($column, $value);
                        break;

                    case 'inquiry_date':
                    case 'req_submittal_date':
                    case 'project_start_date':
                        if (isset($value['from'])) {
                            $query->whereDate($column, '>=', $value['from']);
                        }
                        if (isset($value['to'])) {
                            $query->whereDate($column, '<=', $value['to']);
                        }
                        break;

                    case 'work_type':
                        $query->where('work_type_id', $value);
                        break;

                    case 'inquiry_source':
                        $query->where('inquiry_source_id', $value);
                        break;

                    case 'project_difficulty':
                        $query->where('project_difficulty', $value);
                        break;

                    case 'tender_number':
                        $query->where('tender_number', 'like', "%{$value}%");
                        break;
                }
            }
        }

        // الترتيب
        $sortColumn = $request->get('sort', $preferences->sort_column ?? 'created_at');
        $sortDirection = $request->get('direction', $preferences->sort_direction ?? 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', $preferences->per_page ?? 25);
        $inquiries = $query->paginate($perPage);

        // جلب البيانات للفلاتر
        $filterData = $this->getFilterData();

        // الأعمدة المتاحة والمرئية
        $availableColumns = UserInquiryPreference::getAvailableColumns();
        $visibleColumns = $preferences->visible_columns ?? UserInquiryPreference::getDefaultColumns();

        return view('inquiries::inquiries.index', compact(
            'inquiries',
            'preferences',
            'filterData',
            'availableColumns',
            'visibleColumns',
            'filters'
        ));
    }

    // حفظ التفضيلات
    public function savePreferences(Request $request)
    {
        $user = Auth::user();

        UserInquiryPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'visible_columns' => $request->visible_columns ?? [],
                'filters' => $request->filters ?? [],
                'sort_column' => $request->sort_column ?? 'created_at',
                'sort_direction' => $request->sort_direction ?? 'desc',
                'per_page' => $request->per_page ?? 25,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => __('Preferences saved successfully')
        ]);
    }

    // إعادة تعيين التفضيلات
    public function resetPreferences()
    {
        $user = Auth::user();

        UserInquiryPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'visible_columns' => UserInquiryPreference::getDefaultColumns(),
                'filters' => [],
                'sort_column' => 'created_at',
                'sort_direction' => 'desc',
                'per_page' => 25,
            ]
        );

        Alert::success(__('Preferences reset successfully'));
        return redirect()->route('inquiries.index');
    }

    private function getFilterData()
    {
        // جلب Contacts بناءً على أدوارهم
        $clients = Contact::whereHas('roles', function ($q) {
            $q->where('name', 'Client');
        })->distinct()->select('id', 'name')->get();

        $mainContractors = Contact::whereHas('roles', function ($q) {
            $q->where('name', 'Main Contractor');
        })->distinct()->select('id', 'name')->get();

        $consultants = Contact::whereHas('roles', function ($q) {
            $q->where('name', 'Consultant');
        })->distinct()->select('id', 'name')->get();

        $owners = Contact::whereHas('roles', function ($q) {
            $q->where('name', 'Owner');
        })->distinct()->select('id', 'name')->get();

        $engineers = Contact::whereHas('roles', function ($q) {
            $q->where('name', 'Engineer');
        })->distinct()->select('id', 'name')->get();

        return [
            'statuses' => \Modules\Inquiries\Enums\InquiryStatus::cases(),
            'quotation_states' => \Modules\Inquiries\Enums\QuotationStateEnum::cases(),
            'status_for_kon' => \Modules\Inquiries\Enums\StatusForKon::cases(),
            'kon_titles' => \Modules\Inquiries\Enums\KonTitle::cases(),
            'client_priorities' => \Modules\Inquiries\Enums\ClientPriorityEnum::cases(),
            'kon_priorities' => \Modules\Inquiries\Enums\KonPriorityEnum::cases(),
            'work_types' => \Modules\Inquiries\Models\WorkType::whereNull('parent_id')->get(),
            'inquiry_sources' => \Modules\Inquiries\Models\InquirySource::whereNull('parent_id')->get(),
            'difficulties' => [1 => __('Easy'), 2 => __('Medium'), 3 => __('Hard'), 4 => __('Very Hard')],
            // إضافة بيانات العملاء
            'clients' => $clients,
            'main_contractors' => $mainContractors,
            'consultants' => $consultants,
            'owners' => $owners,
            'engineers' => $engineers,
        ];
    }

    public function create()
    {
        return view('inquiries::inquiries.create');
    }

    public function store(Request $request) {}

    public function drafts()
    {
        $drafts = Inquiry::where('is_draft', true)
            ->with(['city', 'town', 'projectSize', 'workType'])
            ->latest('last_draft_saved_at')
            ->paginate(15);

        return view('inquiries::drafts.index', compact('drafts'));
    }

    public function show($id)
    {
        $inquiry = Inquiry::with([
            'project',
            'workType',
            'workTypes',
            'inquirySource',
            'contacts.roles',
            'city',
            'town',
            'projectDocuments',
            'submittalChecklists',
            'workConditions',
            'comments.user',
            'media',
            'projectSize',
            'quotationUnits.type',
            'creator'
        ])
            ->withCount('comments')
            ->findOrFail($id);

        // Build work type hierarchy path
        $workTypePath = [];
        $currentWorkType = $inquiry->workType;
        while ($currentWorkType) {
            $workTypePath[] = $currentWorkType->name;
            $currentWorkType = $currentWorkType->parent;
        }
        $workTypePath = array_reverse($workTypePath);

        // Build all selected work types with their hierarchy
        $allWorkTypes = [];
        if ($inquiry->workTypes->isNotEmpty()) {
            foreach ($inquiry->workTypes as $workType) {
                $hierarchyPath = json_decode($workType->pivot->hierarchy_path, true);

                if ($hierarchyPath && is_array($hierarchyPath)) {
                    $pathNames = [];
                    foreach ($hierarchyPath as $stepId) {
                        $wt = \Modules\Inquiries\Models\WorkType::find($stepId);
                        if ($wt) {
                            $pathNames[] = $wt->name;
                        }
                    }

                    $allWorkTypes[] = [
                        'work_type' => $workType,
                        'hierarchy_path' => $pathNames,
                        'description' => $workType->pivot->description ?? '',
                        'order' => $workType->pivot->order ?? 0
                    ];
                }
            }
        }

        // Build inquiry source hierarchy path
        $inquirySourcePath = [];
        $currentInquirySource = $inquiry->inquirySource;
        while ($currentInquirySource) {
            $inquirySourcePath[] = $currentInquirySource->name;
            $currentInquirySource = $currentInquirySource->parent;
        }
        $inquirySourcePath = array_reverse($inquirySourcePath);

        // تنظيم Contacts حسب الأدوار
        $contactsByRole = [
            'client' => null,
            'main_contractor' => null,
            'consultant' => null,
            'owner' => null,
            'engineer' => null,
        ];

        $roleMap = [
            'Client' => 'client',
            'Main Contractor' => 'main_contractor',
            'Consultant' => 'consultant',
            'Owner' => 'owner',
            'Engineer' => 'engineer',
        ];

        foreach ($inquiry->contacts as $contact) {
            $roleId = $contact->pivot->role_id;
            $role = \Modules\Inquiries\Models\InquirieRole::find($roleId);

            if ($role && isset($roleMap[$role->name])) {
                $contactsByRole[$roleMap[$role->name]] = $contact;
            }
        }

        // تنظيم Quotation Types & Units
        $quotationData = [];
        foreach ($inquiry->quotationUnits as $unit) {
            $typeId = $unit->pivot->quotation_type_id;
            $type = \Modules\Inquiries\Models\QuotationType::find($typeId);

            if ($type) {
                if (!isset($quotationData[$type->id])) {
                    $quotationData[$type->id] = [
                        'type' => $type,
                        'units' => []
                    ];
                }
                $quotationData[$type->id]['units'][] = $unit;
            }
        }

        return view('inquiries::inquiries.show', compact(
            'inquiry',
            'workTypePath',
            'allWorkTypes',
            'inquirySourcePath',
            'contactsByRole',
            'quotationData'
        ));
    }

    public function edit($id)
    {
        return view('inquiries::inquiries.edit', compact('id'));
    }

    public function update(Request $request, $id) {}

    public function destroy($id)
    {
        try {
            $inquiry = Inquiry::findOrFail($id);
            $inquiry->clearMediaCollection();
            $inquiry->submittalChecklists()->detach();
            $inquiry->workConditions()->detach();
            $inquiry->projectDocuments()->detach();
            $inquiry->delete();

            Alert::toast(__('Inquiry deleted successfully'), 'success');
            return redirect()->route('inquiries.index')->with('success', __('Inquiry deleted successfully'));
        } catch (Exception $e) {
            Alert::toast(__('Inquiry not found'), 'error');
            return redirect()->route('inquiries.index')->with('error', __('Inquiry not found'));
        }
    }
}
