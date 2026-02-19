<?php

namespace App\Services;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Exception\AwsException;

class AWSBedrockService
{
    protected BedrockRuntimeClient $client;

    public function __construct()
    {
        $this->client = new BedrockRuntimeClient([
            'region' => config('services.awsbedrock.region'),
            'version' => 'latest',
        ]);
    }

    public function invokeClaude(string $prompt, ?string $systemPrompt = null): string
    {
        $payload = [
            'anthropic_version' => 'bedrock-2023-05-31',
            'max_tokens' => (int) config('services.awsbedrock.max_tokens', 32000),
            'system' => $systemPrompt ?? 'From the given json value get the summary of the events in detail in Japanese.',
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
            return 'Error: ' . $e->getAwsErrorMessage();
        }
    }

    public function summarizeEvents(array $events): string
    {
        $prompt = json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($prompt === false) {
            return 'Error: Failed to encode events.';
        }

        return $this->invokeClaude($prompt);
    }
}
