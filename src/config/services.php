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

    // Removed unused service configurations (postmark, ses, resend, slack)
    // Add them back when needed for email or notification services

    'llm' => [
        'base_url' => env('LLM_BASE_URL', 'https://api.openai.com/v1'),
        'api_key' => env('LLM_API_KEY'),
        'model' => env('LLM_MODEL', 'gpt-4o-mini'),
    ],

    'whisper' => [
        'binary' => env('WHISPER_BINARY', '/usr/local/bin/whisper-cli'),
        'model_path' => env('WHISPER_MODEL_PATH', '/opt/whisper-models/ggml-small.en.bin'),
        'chunk_seconds' => env('WHISPER_CHUNK_SECONDS', 1800),
    ],

];
