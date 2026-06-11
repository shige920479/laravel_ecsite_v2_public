<?php
namespace App\Services\Admin;

use App\Models\Admin;

interface AdminServiceInterface
{
    public function store(array $data): string;
    public function update(Admin $admin, array $inputs, array $roles): Admin;
}