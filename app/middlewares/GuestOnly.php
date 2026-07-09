<?php

namespace App\Middlewares;

use App\Models\Activity;
use Core\{Response, Session};

class GuestOnly
{
    public static function handle()
    {
        $authToken = Session::get('auth.token');

        if ($authToken === null) {
            return;
        }

        if (Activity::verify($authToken)) {
            Response::redirect('/');
        }

        Activity::revoke($authToken);
        Session::destroy();
    }
}
