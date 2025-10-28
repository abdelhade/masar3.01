<?php

namespace Modules\Inquiries\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Inquiries\Models\Inquiry;
use Modules\Inquiries\Models\UserInquiryPreference;
use RealRashid\SweetAlert\Facades\Alert;

class InquiriesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

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
            'client',
            'mainContractor',
            'consultant',
            'owner',
            'assignedEngineer',
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
                            fn($q) =>
                            $q->where('name', 'like', "%{$value}%")
                        );
                        break;

                    case 'client':
                    case 'main_contractor':
                    case 'consultant':
                    case 'owner':
                    case 'assigned_engineer':
                        $relation = $column === 'main_contractor' ? 'mainContractor' : ($column === 'assigned_engineer' ? 'assignedEngineer' : $column);
                        $query->whereHas(
                            $relation,
                            fn($q) =>
                            $q->where('cname', 'like', "%{$value}%")
                        );
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

                    case 'city':
                        $query->where('city_id', $value);
                        break;

                    case 'town':
                        $query->where('town_id', $value);
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
        $user = auth()->user();

        $preferences = UserInquiryPreference::updateOrCreate(
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
        $user = auth()->user();

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
        return [
            'statuses' => \Modules\Inquiries\Enums\InquiryStatus::cases(),
            'quotation_states' => \Modules\Inquiries\Enums\QuotationStateEnum::cases(),
            'status_for_kon' => \Modules\Inquiries\Enums\StatusForKon::cases(),
            'kon_titles' => \Modules\Inquiries\Enums\KonTitle::cases(),
            'client_priorities' => \Modules\Inquiries\Enums\ClientPriorityEnum::cases(),
            'kon_priorities' => \Modules\Inquiries\Enums\KonPriorityEnum::cases(),
            'cities' => \App\Models\City::select('id', 'title')->get(),
            'towns' => \App\Models\Town::select('id', 'title')->get(),
            'work_types' => \Modules\Inquiries\Models\WorkType::whereNull('parent_id')->get(),
            'inquiry_sources' => \Modules\Inquiries\Models\InquirySource::whereNull('parent_id')->get(),
            'difficulties' => [1 => __('Easy'), 2 => __('Medium'), 3 => __('Hard'), 4 => __('Very Hard')],
        ];
    }

    public function create()
    {
        return view('inquiries::inquiries.create');
    }

    public function store(Request $request) {}

    public function show($id)
    {
        $inquiry = Inquiry::with([
            'project',
            'workType',
            'workTypes',
            'inquirySource',
            'client',
            'mainContractor',
            'consultant',
            'owner',
            'assignedEngineer',
            'city',
            'town',
            'projectDocuments',
            'submittalChecklists',
            'workConditions',
            'comments.user',
            'media'
        ])->findOrFail($id);

        $workTypePath = [];
        $currentWorkType = $inquiry->workType;
        while ($currentWorkType) {
            $workTypePath[] = $currentWorkType->name;
            $currentWorkType = $currentWorkType->parent;
        }
        $workTypePath = array_reverse($workTypePath);

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

        $inquirySourcePath = [];
        $currentInquirySource = $inquiry->inquirySource;
        while ($currentInquirySource) {
            $inquirySourcePath[] = $currentInquirySource->name;
            $currentInquirySource = $currentInquirySource->parent;
        }
        $inquirySourcePath = array_reverse($inquirySourcePath);

        return view('inquiries::inquiries.show', compact(
            'inquiry',
            'workTypePath',
            'allWorkTypes',
            'inquirySourcePath'
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
