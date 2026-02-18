<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class SalesforceController extends Controller
{
    public function getevents(): JsonResponse
    {
        $sfPath = config('services.salesforce.binary_location');
        $aliasName = config('services.salesforce.alias_name');
        $query = "SELECT Subject, StartDateTime, EndDateTime FROM Event LIMIT 10";

        if (empty($sfPath) || empty($aliasName)) {
            Log::error('Salesforce CLI config missing: SF_BINARY_LOCATION or SF_ALIAS_NAME.');
            return response()->json(['error' => 'Salesforce CLI configuration missing.'], 500);
        }

        $homePath = storage_path('sf-home');
        $aliasFile = $homePath . '/.config/sf/alias.json';
        $command = "{$sfPath} data query --query \"{$query}\" --target-org {$aliasName} --json";
        $process = Process::env([
            'HOME' => $homePath,
            'SF_USE_GENERIC_UNIX_KEYCHAIN' => 'true',
            'SF_DISABLE_TELEMETRY' => 'true',
        ])->run($command);
        if ($process->successful()) {
            $data = json_decode($process->output(), true);
            $events = $data['result']['records'] ?? [];
            return response()->json($events);
        }
        Log::error("Salesforce CLI Error: " . $process->errorOutput());

        $debug = request()->boolean('debug');
        if ($debug) {
            $aliasData = [];
            if (is_readable($aliasFile)) {
                $decoded = json_decode((string) file_get_contents($aliasFile), true);
                $aliasData = is_array($decoded['orgs'] ?? null) ? array_keys($decoded['orgs']) : [];
            }

            return response()->json([
                'error' => 'Could not fetch mitoco events',
                'debug' => [
                    'command' => $command,
                    'exit_code' => $process->exitCode(),
                    'stdout' => $process->output(),
                    'stderr' => $process->errorOutput(),
                    'home' => $homePath,
                    'alias_file_exists' => file_exists($aliasFile),
                    'alias_file_readable' => is_readable($aliasFile),
                    'alias_keys' => $aliasData,
                ],
            ], 500);
        }

        return response()->json(['error' => 'Could not fetch mitoco events'], 500);
    }

    public function authenticate(): Response
    {

        return response('Salesforce authenticate endpoint reached.', 200);
    }
}
