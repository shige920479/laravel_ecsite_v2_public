<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerAuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.owner.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if(! Auth::guard('web_owner')->attempt($credentials)) {
            return back()->withErrors([
                'email' => 'ログインに失敗しました。再度、お試しください'
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('owner.shop.index'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web_owner')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('owner.login');
    }
}
