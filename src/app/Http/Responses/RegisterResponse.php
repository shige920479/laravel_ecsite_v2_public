<?php
namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as ContractsRegisterResponse;

class RegisterResponse implements ContractsRegisterResponse
{
    public function toResponse($request)
    {
        $user = $request->user();

        // メール認証を使う場合は有効化
        // if (! $user->hasVerifiedEmail()) {
        //     return redirect('/email/verify');
        // }
        return redirect(config('fortify.home'));
    }
}