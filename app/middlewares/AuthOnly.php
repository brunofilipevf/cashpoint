<?php

namespace App\Middlewares;

class AuthOnly
{
    public function __construct(
        private \App\Models\Activity $activity,
        private \App\Models\Auth $auth,
        private \App\Models\User $user,
        private \Core\Response $response,
        private \Core\Session $session
    ) {}

    public function handle($minHierarchy = 0)
    {
        $authToken = $this->session->get('auth.token');

        if ($authToken === null) {
            $this->response->redirect('/login');
        }

        $authUserId = $this->activity->verify($authToken);

        if (!$authUserId) {
            $this->session->destroy();
            $this->activity->revoke($authToken);
            $this->response->redirect('/login');
        }

        $authUserData = $this->user->find($authUserId);

        if (!$authUserData) {
            $this->session->destroy();
            $this->activity->revoke($authToken);
            $this->response->redirect('/login');
        }

        if ($authUserData['hierarchy'] < $minHierarchy) {
            $this->response->abort(403);
        }

        $this->auth->store($authUserData);
    }
}
