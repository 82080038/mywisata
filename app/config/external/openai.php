<?php
return [
    'api_key' => getenv('OPENAI_API_KEY') ?: '',
    'model' => getenv('OPENAI_MODEL') ?: 'gpt-4',
    'temperature' => getenv('OPENAI_TEMPERATURE') ?: 0.7,
    'max_tokens' => getenv('OPENAI_MAX_TOKENS') ?: 1000,
    'organization' => getenv('OPENAI_ORGANIZATION') ?: '',
    'endpoint' => 'https://api.openai.com/v1/chat/completions',
    'language' => getenv('OPENAI_LANGUAGE') ?: 'id',
    'locale' => getenv('OPENAI_LOCALE') ?: 'id-ID',
    'system_prompt' => getenv('OPENAI_SYSTEM_PROMPT') ?: 'You are a helpful assistant for the MyWisata application, a tour guide platform for Indonesia. Please respond in Indonesian (Bahasa Indonesia) unless specifically asked to use another language. Be helpful, friendly, and provide accurate information about Indonesian tourism destinations, culture, and travel tips.'
];
