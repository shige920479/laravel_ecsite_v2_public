<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAccountRequest;
use Auth;
use Illuminate\Http\Request;

class UserAccountController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        return view('auth.user.edit', ['user' => $user]);
    }

    public function update(UpdateAccountRequest $request)
    {
        $user = $request->user();
        $user->fill($request->validated());
        if ($user->isClean()) {
            return back()->with([
                'status' => 'alert',
                'message' => '登録内容に変更がありません、ご確認願います',
            ]);
        }
        $user->save();

        return to_route('mypage.account.edit', ['user' => $user])->with([
            'status' => 'info',
            'message' => 'アカウント情報を更新しました'
        ]);
    }
}
