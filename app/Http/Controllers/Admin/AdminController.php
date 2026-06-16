<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Dashboard\AdminDashboardRequest;
use App\Services\Admin\Dashboard\AdminDashboardService;

class AdminController extends Controller
{
    public function __construct(protected AdminDashboardService $dashboard) {}

    public function homeAdmin(AdminDashboardRequest $request)
    {
        $this->authorize('viewAdminDashboard');

        return view('admin.homeAdmin', $this->dashboard->data($request->filters()));
    }
}
