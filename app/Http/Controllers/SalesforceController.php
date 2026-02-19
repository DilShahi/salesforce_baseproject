<?php

namespace App\Http\Controllers;

use App\Services\SalesforceConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class SalesforceController extends Controller
{
    public function getevents(): JsonResponse
    {
        $sfSetup = new SalesforceConfiguration();
        $query = "SELECT Subject, StartDateTime, EndDateTime FROM Event LIMIT 10";
        $data = $sfSetup->runcommand($query);
        return $data;
    }

    public function authenticate(): Response
    {

        return response('Salesforce authenticate endpoint reached.', 200);
    }
}
