<?php
return [
    'api_key' => getenv('OPENAI_API_KEY') ?: '',
    'model' => getenv('OPENAI_MODEL') ?: 'gpt-4',
    'temperature' => getenv('OPENAI_TEMPERATURE') ?: 0.7,
    'max_tokens' => getenv('OPENAI_MAX_TOKENS') ?: 1000,
    'organization' => getenv('OPENAI_ORGANIZATION') ?: '',
    'endpoint' => 'https://api.openai.com/v1/chat/completions'
];
