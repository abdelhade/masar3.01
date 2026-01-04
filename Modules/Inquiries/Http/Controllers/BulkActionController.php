<?php

declare(strict_types=1);

namespace Modules\Inquiries\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BulkActionController extends Controller
{
    public function handle(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required',
            'model' => 'required|string',
            'action' => 'required|string|in:delete',
        ]);

        $ids = $request->ids;
        $modelClass = $request->model;
        $action = $request->action;

        // Sanitize model class (handle double backslashes which might come from JS/escaping)
        $modelClass = str_replace('\\\\', '\\', $modelClass);

        // Security check: Ensure the model belongs to the Inquiries module or is allowed
        if (!str_starts_with($modelClass, 'Modules\\Inquiries\\Models\\')) {
            return response()->json(['success' => false, 'message' => __('Invalid model namespace: :model', ['model' => $modelClass])], 403);
        }

        if (!class_exists($modelClass)) {
            return response()->json(['success' => false, 'message' => __('Model not found')], 404);
        }

        try {
            if ($action === 'delete') {
                // Check permissions dynamically based on model name
                $modelName = class_basename($modelClass);

                // Map model names to permission names if they don't follow the "delete ModelNames" pattern
                $permissionMap = [
                    'ProjectSize' => 'delete Project Size',
                    'InquirieRole' => 'delete Inquiries Roles',
                    'ProjectDocument' => 'delete Documents',
                    'InquirySource' => 'delete Inquiries Source',
                    'PricingStatus' => 'delete Pricing Statuses',
                    'WorkType' => 'delete Work Types',
                ];

                // Guess permission name or use map
                $permissionName = $permissionMap[$modelName] ?? ("delete " . ($modelName === 'Inquiry' ? 'Inquiries' : $modelName . 's'));

                if (!auth()->user()->can($permissionName)) {
                    return response()->json(['success' => false, 'message' => __('Unauthorized')], 403);
                }

                if ($modelName === 'Inquiry') {
                    // For Inquiries, allow deleting if assigned, created by them, OR has force delete permission
                    $query = $modelClass::whereIn('id', $ids);

                    if (!auth()->user()->can('force_delete_inquiries')) {
                        $query->where(function ($q) {
                            // Check assigned engineers
                            $q->whereHas('assignedEngineers', function ($sub) {
                                $sub->where('users.id', auth()->id());
                            })
                                // OR check creator (for drafts)
                                ->orWhere('created_by', auth()->id());
                        });

                        $authorizedIds = $query->pluck('id')->toArray();

                        if (empty($authorizedIds)) {
                            return response()->json(['success' => false, 'message' => 'No authorized items to delete'], 403);
                        }

                        $modelClass::whereIn('id', $authorizedIds)->delete();
                        $deletedCount = count($authorizedIds);
                    } else {
                        $modelClass::whereIn('id', $ids)->delete();
                        $deletedCount = count($ids);
                    }
                }


                return response()->json([
                    'success' => true,
                    'message' => __('Selected :count items deleted successfully', ['count' => $deletedCount])
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Error: :error', ['error' => $e->getMessage()])
            ], 500);
        }

        return response()->json(['success' => false, 'message' => __('Action not supported')], 400);
    }
}
