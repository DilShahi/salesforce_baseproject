<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserEventsDateRangeRequest;
use App\Services\AWSBedrockService;
use App\Services\SalesforceConfiguration;
use Carbon\CarbonImmutable;
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

    public function userevent(UserEventsDateRangeRequest $request, string $userId): View|JsonResponse|Response
    {
        if (! preg_match('/^[a-zA-Z0-9]{15,18}$/', $userId)) {
            return response()->json(['error' => 'Invalid user id.'], 400);
        }

        $startDate = $request->validated('start_date');
        $endDate = $request->validated('end_date');
        $hasSubmittedDates = $request->filled(['start_date', 'end_date']);

        if (! $hasSubmittedDates) {
            return view('salesforce.user-events', [
                'userId' => $userId,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'hasSubmittedDates' => false,
            ]);
        }

        $conditions = ["OwnerId='{$userId}'"];

        if ($startDate !== null) {
            $startDateTime = CarbonImmutable::parse($startDate, 'UTC')
                ->startOfDay()
                ->format('Y-m-d\TH:i:s\Z');
            $conditions[] = "StartDateTime >= {$startDateTime}";
        }

        if ($endDate !== null) {
            $endDateTime = CarbonImmutable::parse($endDate, 'UTC')
                ->endOfDay()
                ->format('Y-m-d\TH:i:s\Z');
            $conditions[] = "EndDateTime <= {$endDateTime}";
        }

        $whereClause = implode(' AND ', $conditions);
        $query = "SELECT Id, Subject, StartDateTime, EndDateTime from Event WHERE {$whereClause} ORDER BY StartDateTime DESC";

        $helper = new SalesforceConfiguration;
        $response = $helper->runcommand($query);

        if (request()->boolean('debug')) {
            return $response;
        }

        $participantPayload = $response->getData(true);
        if (($response->getStatusCode() ?? 500) !== 200 || isset($participantPayload['error'])) {
            return response()->view('salesforce.user-events', [
                'error' => $this->salesforceErrorMessage($participantPayload),
                'userId' => $userId,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'hasSubmittedDates' => true,
            ], $response->getStatusCode() ?? 500);
        }

        return view('salesforce.user-events', [
            'events' => $participantPayload['records'] ?? [],
            'userId' => $userId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hasSubmittedDates' => true,
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
        if (str_starts_with($summary, 'Error:')) {
            return response()->view('salesforce.user-events', [
                'error' => $summary,
                'events' => $events,
                'userId' => $userId,
                'hasSubmittedDates' => true,
            ], 422);
        }

        $summaryData = $this->decodeSummaryJson($summary);
        if ($summaryData === null) {
            return response()->view('salesforce.user-events', [
                'error' => 'Summary response is not valid JSON. Please try again.',
                'events' => $events,
                'userId' => $userId,
                'hasSubmittedDates' => true,
            ], 422);
        }

        $chartData = $this->buildChartData($summaryData);
        if ($chartData === null) {
            return response()->view('salesforce.user-events', [
                'error' => 'Summary JSON does not contain categories with counts.',
                'events' => $events,
                'userId' => $userId,
                'hasSubmittedDates' => true,
            ], 422);
        }

        $summaryText = $this->buildSummaryText($summaryData, $chartData['labels'], $chartData['counts']);

        return view('salesforce.user-events-summary', [
            'summary' => $summary,
            'summaryText' => $summaryText,
            'chartLabels' => $chartData['labels'],
            'chartCounts' => $chartData['counts'],
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

    private function salesforceErrorMessage(array $payload): string
    {
        if (isset($payload['error']) && is_string($payload['error'])) {
            return $payload['error'];
        }

        if (isset($payload[0]['message']) && is_string($payload[0]['message'])) {
            return $payload[0]['message'];
        }

        return 'Could not fetch mitoco events';
    }

    private function decodeSummaryJson(string $summary): ?array
    {
        $decoded = json_decode($summary, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/```json\s*(\{[\s\S]*\}|\[[\s\S]*\])\s*```/i', $summary, $matches) === 1) {
            $decodedFromFence = json_decode($matches[1], true);

            return is_array($decodedFromFence) ? $decodedFromFence : null;
        }

        if (preg_match('/(\{[\s\S]*\}|\[[\s\S]*\])/', $summary, $matches) === 1) {
            $decodedFromText = json_decode($matches[1], true);

            return is_array($decodedFromText) ? $decodedFromText : null;
        }

        return null;
    }

    private function buildChartData(array $summaryData): ?array
    {
        $categories = $summaryData['categories'] ?? null;
        if (! is_array($categories) || $categories === []) {
            return null;
        }

        $labels = [];
        $counts = [];

        foreach ($categories as $category) {
            if (! is_array($category)) {
                continue;
            }

            $name = $category['name'] ?? null;
            $count = $category['count'] ?? null;

            if (! is_string($name) || $name === '') {
                continue;
            }

            if (! is_numeric($count)) {
                continue;
            }

            $labels[] = $name;
            $counts[] = (int) $count;
        }

        if ($labels === [] || $counts === []) {
            return null;
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
        ];
    }

    private function buildSummaryText(array $summaryData, array $labels, array $counts): string
    {
        $overview = $summaryData['overview'] ?? null;
        $lines = [];

        if (is_string($overview) && trim($overview) !== '') {
            $lines[] = trim($overview);
        }

        foreach ($labels as $index => $label) {
            $count = $counts[$index] ?? null;
            if (! is_int($count)) {
                continue;
            }

            $lines[] = $label.': '.$count;
        }

        if ($lines === []) {
            return 'Summary generated successfully.';
        }

        return implode("\n", $lines);
    }
}
