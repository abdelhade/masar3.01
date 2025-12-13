<?php

declare(strict_types=1);

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Routing\Controller;

class OnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Onboardings')->only(['index']);
        $this->middleware('can:create Onboardings')->only(['create', 'store']);
        $this->middleware('can:edit Onboardings')->only(['update', 'edit']);
        $this->middleware('can:delete Onboardings')->only(['destroy']);
    }

    public function index()
    {
        return view('recruitment::onboardings.manage');
    }

    public function show($id)
    {
        $onboarding = \Modules\Recruitment\Models\Onboarding::with(['contract', 'branch'])->findOrFail($id);

        return view('recruitment::onboardings.show', compact('onboarding'));
    }
}
