<?php

namespace Modules\App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class ExcelImporter extends Component
{
    public $model;
    public $columnMapping;
    public $validationRules;
    public $apiBaseUrl;
    public $buttonText;
    public $buttonSize;

    public function __construct(
        $model,
        $columnMapping = [],
        $validationRules = [],
        $apiBaseUrl = '/excel-import',
        $buttonText = 'استيراد Excel',
        $buttonSize = 'small'
    ) {
        $this->model = $model;
        $this->columnMapping = is_string($columnMapping) ? json_decode($columnMapping, true) : $columnMapping;
        $this->validationRules = is_string($validationRules) ? json_decode($validationRules, true) : $validationRules;
        $this->apiBaseUrl = $apiBaseUrl;
        $this->buttonText = $buttonText;
        $this->buttonSize = $buttonSize;
    }

    public function render(): View|string
    {
        return view('app::components.excelimporter');
    }
}
