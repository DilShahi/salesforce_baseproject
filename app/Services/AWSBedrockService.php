<?php

namespace App\Services;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Exception\AwsException;
use Throwable;

class AWSBedrockService
{
    protected BedrockRuntimeClient $client;

    public function __construct()
    {
        $clientConfiguration = [
            'region' => config('services.awsbedrock.region'),
            'version' => 'latest',
            'retries' => max((int) config('services.awsbedrock.retries', 0), 0),
            'http' => [
                'connect_timeout' => $this->resolveConnectTimeout(),
                'timeout' => $this->resolveRequestTimeout(),
            ],
        ];

        $accessKey = config('services.awsbedrock.accessKey');
        $secretAccessKey = config('services.awsbedrock.secretAccessKey');

        if (is_string($accessKey) && $accessKey !== '' && is_string($secretAccessKey) && $secretAccessKey !== '') {
            $clientConfiguration['credentials'] = [
                'key' => $accessKey,
                'secret' => $secretAccessKey,
            ];
        }

        $this->client = new BedrockRuntimeClient($clientConfiguration);
    }

    private function resolveRequestTimeout(): float
    {
        $configuredTimeout = (float) config('services.awsbedrock.request_timeout', 20);
        $maxExecutionTime = (int) ini_get('max_execution_time');

        if ($maxExecutionTime <= 0) {
            return $configuredTimeout;
        }

        $safeTimeout = max($maxExecutionTime - 5, 1);

        return min($configuredTimeout, (float) $safeTimeout);
    }

    private function resolveConnectTimeout(): float
    {
        $configuredConnectTimeout = (float) config('services.awsbedrock.connect_timeout', 5);
        $safeRequestTimeout = $this->resolveRequestTimeout();

        return min($configuredConnectTimeout, max($safeRequestTimeout - 1, 1));
    }

    public function invokeClaude(string $prompt, ?string $systemPrompt = null): string
    {
        $payload = [
            'anthropic_version' => 'bedrock-2023-05-31',
            'max_tokens' => (int) config('services.awsbedrock.max_tokens', 4096),
            'system' => $systemPrompt ?? 'You are given event data JSON. Return only valid JSON with this shape: {"overview":"short text","categories":[{"name":"category name","count":number,"events":[{"subject":"event subject","startDateTime":"ISO datetime or empty string","endDateTime":"ISO datetime or empty string"}]}]}. Ensure count matches events length for each category. Do not include markdown, code fences, or extra keys.',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                    ],
                ],
            ],
            'temperature' => (float) config('services.awsbedrock.temperature', 1),
            'top_k' => (int) config('services.awsbedrock.top_k', 250),
        ];
        try {
            $result = $this->client->invokeModel([
                'modelId' => config('services.awsbedrock.modelId'),
                'contentType' => 'application/json',
                'accept' => 'application/json',
                'body' => json_encode($payload),
            ]);
            $response = json_decode($result->get('body'), true);

            return $response['content'][0]['text'] ?? 'No response content';
        } catch (AwsException $e) {
            return 'Error: '.($e->getAwsErrorMessage() ?: $e->getMessage());
        } catch (Throwable $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    public function summarizeEvents(array $events): string
    {
        $limitedEvents = $this->sanitizeEventsForSummary($events);
        if ($limitedEvents === []) {
            return 'Error: No events available for summary.';
        }

        $prompt = json_encode($limitedEvents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($prompt === false) {
            return 'Error: Failed to encode events.';
        }

        $maxPromptCharacters = (int) config('services.awsbedrock.max_prompt_chars', 120000);
        if (mb_strlen($prompt) > $maxPromptCharacters) {
            return 'Error: Event data is too large to summarize. Please select a smaller date range.';
        }

        return $this->invokeClaude($prompt);
    }

    private function sanitizeEventsForSummary(array $events): array
    {
        $maximumEvents = max((int) config('services.awsbedrock.max_events', 80), 1);
        $limitedEvents = array_slice($events, 0, $maximumEvents);

        return array_values(array_map(function ($event): array {
            $eventData = is_array($event) ? $event : [];

            return [
                'Id' => $eventData['Id'] ?? null,
                'Subject' => $eventData['Subject'] ?? '',
                'StartDateTime' => $eventData['StartDateTime'] ?? null,
                'EndDateTime' => $eventData['EndDateTime'] ?? null,
            ];
        }, $limitedEvents));
    }
}
