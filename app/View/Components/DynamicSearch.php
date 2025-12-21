<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DynamicSearch extends Component
{
    public $name;

    public $label;

    public $options;

    public $selected;

    public $column;

    public $model;

    public $placeholder;

    public $required;

    public $class;

    public $filters = [];

    public function __construct(
        $name,
        $label,
        $column,
        $model,
        $selected = null,
        $placeholder = 'ابحث...',
        $required = false,
        $class = '',
        $filters = []

    ) {
        $this->name = $name;
        $this->label = $label;
        $this->column = $column;
        $this->model = $model;
        $this->selected = $selected;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->class = $class;
        $this->filters = $filters;

        // جلب البيانات من النموذج
        $modelClass = $this->model;

        // Apply filters if any
        $query = $modelClass::select(['id', $this->column]);
        foreach ($this->filters as $key => $value) {
            if (is_string($value) && (str_contains($value, '%') || str_starts_with($value, 'like:'))) {
                // Support LIKE queries
                $likeValue = str_replace('like:', '', $value);
                $query->where($key, 'like', $likeValue);
            } elseif (is_array($value) && isset($value['operator']) && isset($value['value'])) {
                // Support custom operators like ['operator' => 'like', 'value' => '1103%']
                $query->where($key, $value['operator'], $value['value']);
            } else {
                $query->where($key, $value);
            }
        }
        $this->options = $query->get();
    }

    public function render()
    {
        return view('components.dynamic-search');
    }
}
