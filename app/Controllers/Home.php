<?php

namespace App\Controllers;

use App\Models\UserModel;

class Home extends BaseController
{
    public function index()
    {
        if (model(UserModel::class)->countAllResults() === 0) {
            return redirect()->to('/installazione');
        }

        return redirect()->to(service('auth')->check() ? '/oggi' : '/accedi');
    }
}
