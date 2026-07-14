<?php

namespace App\Middlewares;

class GuestOnly
{
    public function __construct(
        private \App\Models\Activity $activity,
        private \Core\Response $response,
        private \Core\Session $session
    ) {}

    public function handle()
    {
        $authToken = $this->session->get('auth.token');

        if ($authToken === null) {
            return;
        }

        if ($this->activity->verify($authToken)) {
            $this->response->redirect('/');
        }

        $this->activity->revoke($authToken);
        $this->session->destroy();
    }
}
