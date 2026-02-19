<?php

namespace App\Http\Controllers;

use App\Services\SalesforceConfiguration;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function userlist(): JsonResponse
    {
        $sfSetup = new SalesforceConfiguration();
        $query = 'SELECT Id, Name, Username, Email, IsActive FROM User';
        $data = $sfSetup->runcommand($query);
        return $data;
    }
}
