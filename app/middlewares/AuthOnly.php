<?php

namespace App\Middlewares;

use App\Models\{Activity, Auth, User};
use Core\{Response, Session};

class AuthOnly
{
    public static function handle($minHierarchy = 0)
    {
        $authToken = Session::get('auth.token');

        if ($authToken === null) {
            Response::redirect('/login');
        }

        $authUserId = Activity::verify($authToken);

        if (!$authUserId) {
            Session::destroy();
            Activity::revoke($authToken);
            Response::redirect('/login');
        }

        $authUserData = User::get($authUserId);

        if (!$authUserData) {
            Session::destroy();
            Activity::revoke($authToken);
            Response::redirect('/login');
        }

        if ($authUserData['hierarchy'] < $minHierarchy) {
            Response::abort(403);
        }

        Auth::store($authUserData);
    }
}
