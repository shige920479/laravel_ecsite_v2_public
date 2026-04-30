<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    public function userDemoLogin()
    {
        $user = User::where('email', 'user@mail.com')->first();
        Auth::guard('web')->login($user);
        
        return redirect()->intended();
    }

    public function ownerDemoLogin()
    {
        $owner = Owner::where('email', 'owner1@mail.com')->first();
        Auth::guard('web_owner')->login($owner);

        return redirect()->intended('/owner/shop');
    }

    public function adminDemoLogin()
    {
        $admin = Admin::where('email', 'admin@mail.com')->first();
        Auth::guard('web_admin')->login($admin);

        return redirect()->intended('/admin/home');
    }
}
