<?php

namespace App\Http\Controllers;

use App\Services\AWSBedrockService;
use App\Services\SalesforceConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class UserController extends Controller
{
    public function userlist(): View|JsonResponse|Response
    {
        $sfSetup = new SalesforceConfiguration;
        $query = 'SELECT Id, Name, Username, Email, IsActive FROM User';
        $response = $sfSetup->runcommand($query);
        if (request()->boolean('debug')) {
            return $response;
        }

        $payload = $response->getData(true);
        if (($response->getStatusCode() ?? 500) !== 200 || isset($payload['error'])) {
            return response()->view('salesforce.userlist', [
                'error' => $payload['error'] ?? 'Could not fetch salesforce users',
            ], $response->getStatusCode() ?? 500);
        }

        return view('salesforce.userlist', [
            'users' => $payload,
        ]);
    }

    public function userevent(string $userId): View|JsonResponse|Response
    {
        $participantResponse = $this->participantEventsResponse($userId);

        if (request()->boolean('debug')) {
            return $participantResponse;
        }

        $participantPayload = $participantResponse->getData(true);
        if (($participantResponse->getStatusCode() ?? 500) !== 200 || isset($participantPayload['error'])) {
            return response()->view('salesforce.user-events', [
                'error' => $participantPayload['error'] ?? 'Could not fetch mitoco events',
            ], $participantResponse->getStatusCode() ?? 500);
        }

        $bedrock = new AWSBedrockService;
        $summary = $bedrock->summarizeEvents($participantPayload);

        return view('salesforce.user-events', [
            'events' => $participantPayload,
            'userId' => $userId,
            'summary' => $summary
        ]);
    }

    // public function eventssummary(string $userId): View|JsonResponse|Response
    // {
    //     $participantResponse = $this->participantEventsResponse($userId);

    //     if (request()->boolean('debug')) {
    //         return $participantResponse;
    //     }

    //     $participantPayload = $participantResponse->getData(true);
    //     if (($participantResponse->getStatusCode() ?? 500) !== 200 || isset($participantPayload['error'])) {
    //         return response()->view('salesforce.user-events', [
    //             'error' => $participantPayload['error'] ?? 'Could not fetch mitoco events',
    //         ], $participantResponse->getStatusCode() ?? 500);
    //     }

    //     $bedrock = new AWSBedrockService;
    //     $summary = $bedrock->summarizeEvents($participantPayload);

    //     return view('salesforce.user-events', [
    //         'events' => $participantPayload,
    //         'summary' => $summary,
    //         'userId' => $userId,
    //     ]);
    // }

    private function participantEventsResponse(string $userId): JsonResponse|Response
    {
        if (! preg_match('/^[a-zA-Z0-9]{15,18}$/', $userId)) {
            return response()->json(['error' => 'Invalid user id.'], 400);
        }

        $sfSetup = new SalesforceConfiguration;
        $participantQuery = "SELECT Id, Subject, StartDateTime, EndDateTime FROM Event WHERE Id IN (SELECT EventId FROM EventRelation WHERE RelationId = '{$userId}') ORDER BY StartDateTime DESC LIMIT 50";

        return $sfSetup->runcommand($participantQuery);
    }
}
