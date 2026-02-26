<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'salesforce' => [
        'login_url' => env('SF_LOGIN_URL', 'https://login.salesforce.com'),
        'client_id' => env('SF_CLIENT_ID'),
        'client_secret' => env('SF_CLIENT_SECRET'),
        'redirect_uri' => env('SF_REDIRECT_URI'),
        'scopes' => env('SF_SCOPES', 'refresh_token api'),
        'api_version' => env('SF_API_VERSION', 'v59.0'),
        'binary_location' => env('SF_BINARY_LOCATION'),
        'alias_name' => env('SF_ALIAS_NAME'),
    ],

    'awsbedrock' => [
        'accessKey' => env('AWS_ACCESS_KEY_ID'),
        'secretAccessKey' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'modelId' => env('BEDROCK_MODEL_ID'),
        'max_tokens' => env('BEDROCK_MAX_TOKENS', 4096),
        'temperature' => env('BEDROCK_TEMPERATURE', 1),
        'top_k' => env('BEDROCK_TOP_K', 250),
        'connect_timeout' => env('BEDROCK_CONNECT_TIMEOUT', 5),
        'request_timeout' => env('BEDROCK_REQUEST_TIMEOUT', 20),
        'retries' => env('BEDROCK_RETRIES', 1),
        'max_events' => env('BEDROCK_MAX_EVENTS', 80),
        'max_prompt_chars' => env('BEDROCK_MAX_PROMPT_CHARS', 120000),
    ],

];
