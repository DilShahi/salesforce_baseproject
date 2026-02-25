<?php

namespace App\Http\Controllers;

use App\Services\AWSBedrockService;
use App\Services\SalesforceConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class UserController extends Controller
{
    public function userlist(): View|JsonResponse|Response
    {
        $helper = new SalesforceConfiguration;
        $query = 'SELECT Id, Name, Username, Email, IsActive FROM User';
        $response = $helper->runcommand($query);

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
            'users' => $payload['records'] ?? [],
        ]);
    }

    public function userevent(string $userId): View|JsonResponse|Response
    {
        if (! preg_match('/^[a-zA-Z0-9]{15,18}$/', $userId)) {
            return response()->json(['error' => 'Invalid user id.'], 400);
        }

        //  sf data query -o okicom --query "SELECT ID, Subject FROM Event WHERE OwnerId='0052w00000I2QypAAF' ORDER BY StartDateTime DESC"
        // $query = "SELECT Id, Subject, StartDateTime, EndDateTime FROM Event WHERE Id IN (SELECT EventId FROM EventRelation WHERE RelationId = '{$userId}') ORDER BY StartDateTime DESC LIMIT 50";
        $query = "SELECT Id, Subject, StartDateTime, EndDateTime from Event WHERE OwnerId='{$userId}' ORDER BY StartDateTime DESC";

        $helper = new SalesforceConfiguration;
        $response = $helper->runcommand($query);

        if (request()->boolean('debug')) {
            return $response;
        }

        $participantPayload = $response->getData(true);
        if (($response->getStatusCode() ?? 500) !== 200 || isset($participantPayload['error'])) {
            return response()->view('salesforce.user-events', [
                'error' => $participantPayload['error'] ?? 'Could not fetch mitoco events',
            ], $response->getStatusCode() ?? 500);
        }

        return view('salesforce.user-events', [
            'events' => $participantPayload['records'] ?? [],
            'userId' => $userId,
        ]);
    }

    public function eventsummary(Request $request, string $userId): View|JsonResponse|Response
    {
        if (! preg_match('/^[a-zA-Z0-9]{15,18}$/', $userId)) {
            return response()->view('salesforce.user-events', [
                'error' => 'Invalid user id.',
            ], 400);
        }

        $events = $this->eventsFromRequest($request);
        if (! is_array($events)) {
            return response()->view('salesforce.user-events', [
                'error' => 'Invalid events payload.',
            ], 422);
        }

        if ($events === []) {
            return response()->view('salesforce.user-events', [
                'error' => 'No events provided. Please refresh the events page and try again.',
            ], 422);
        }

        $bedrock = new AWSBedrockService;
        $summary = $bedrock->summarizeEvents($events);

        return view('salesforce.user-events-summary', [
            'summary' => $summary,
            'userId' => $userId,
        ]);
    }

    private function eventsFromRequest(Request $request): ?array
    {
        $raw = $request->input('events');
        if ($raw === null) {
            return [];
        }

        if (is_array($raw)) {
            return $raw;
        }

        $decoded = json_decode((string) $raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}
