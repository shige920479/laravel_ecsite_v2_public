<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionClearRequest;
use Illuminate\Http\Request;

class SessionClearController extends Controller
{
    public function clear(SessionClearRequest $request)
    {
        $sessionKeys = config('constants.session_key');
        foreach ($sessionKeys as $key) {
            session()->forget($key);
        }

        return to_route($request->input('route'));
    }
}
