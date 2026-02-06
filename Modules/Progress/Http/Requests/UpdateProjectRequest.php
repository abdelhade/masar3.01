<?php

namespace Modules\Progress\Http\Requests;

class UpdateProjectRequest extends StoreProjectRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['items.*.temp_id'] = 'nullable|string';
        $rules['items.*.id'] = 'nullable|integer|exists:project_items,id'; // ✅ للبنود الموجودة
        return $rules;
    }
}
