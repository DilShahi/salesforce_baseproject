<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class SalesforceConfiguration
{
    private $sfPath;
    private $aliasName;
    private $homePath;
    private $aliasFile;
    public function __construct()
    {
        $this->sfPath = config('services.salesforce.binary_location');
        $this->aliasName = config('services.salesforce.alias_name');
        $this->homePath = storage_path('sf-home');
        $this->aliasFile = $this->homePath . '/.config/sf/alias.json';
    }

    public function runcommand($query)
    {
        if (empty($this->sfPath) || empty($this->aliasName)) {
            Log::error("Salesforce CLI config missing: SF_BINARY LOCATION or SF_ALIAS_NAME.");
            return response()->json(['error' => 'Salesforce CLI configuration missing.'], 500);
        }

        $command = "{$this->sfPath} data query --query \"{$query}\" --target-org {$this->aliasName} --json";
        $process = Process::env([
            'HOME' => $this->homePath,
            'SF_USE_GENERIC_UNIX_KEYCHAIN' => 'true',
            'SF_DISABLE_TELEMETRY' => 'true'
        ])->run($command);
        if ($process->successful()) {
            $data = json_decode($process->output(), true);
            $decodedData = $data['result']['records'] ?? [];
            return response()->json($decodedData);
        }
        Log::error("Salesforce CLI Error:" . $process->errorOutput());
        $debug = request()->boolean('debug');
        if ($debug) {
            $aliasData = [];
            if (is_readable($this->aliasFile)) {
                $decoded = json_decode((string) file_get_contents($this->aliasFile), true);
                $aliasData = is_array($decoded['orgs'] ?? null) ? array_keys($decoded['orgs']) : [];
            }
            return response()->json([
                'error' => "Could not fetch salesforce users",
                'debug' => [
                    'command' => $command,
                    'exit_code' => $process->exitCode(),
                    'stdout' => $process->output(),
                    'stderr' => $process->errorOutput(),
                    'home' => $this->homePath,
                    'alias_file_exists' => file_exists($this->aliasFile),
                    'alias_file_readable' => is_readable($this->aliasFile),
                    'alias_keys' => $aliasData,
                ]
            ], 500);
        }
        return response()->json(['error' => 'Could not fetch salesforce users'], 500);
    }
}
