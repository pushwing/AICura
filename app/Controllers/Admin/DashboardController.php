<?php

namespace App\Controllers\Admin;

class DashboardController extends BaseAdminController
{
    public function index(): string
    {
        return $this->render('admin/dashboard');
    }
}
