<?php
namespace App\Services;

/**
 * Ollama Service
 * 
 * Service for interacting with Ollama (self-hosted LLM)
 * Provides AI capabilities without commercial API costs
 * 
 * @package App\Services
 */
class OllamaService {
    private $baseUrl;
    private $model;
    private $timeout;
    private $systemPrompt;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->baseUrl = getenv('OLLAMA_BASE_URL') ?: 'http://localhost:11434';
        $this->model = getenv('OLLAMA_MODEL') ?: 'llama2';
        $this->timeout = getenv('OLLAMA_TIMEOUT') ?: 60;
        $this->systemPrompt = getenv('OLLAMA_SYSTEM_PROMPT') ?: 'You are a helpful assistant for MyWisata, a tour guide application in Indonesia.';
    }
    
    /**
     * Check if Ollama is available
     * 
     * @return bool
     */
    public function isAvailable() {
        try {
            $ch = curl_init($this->baseUrl . '/api/tags');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200;
        } catch (\Exception $e) {
            error_log("Ollama availability check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate chat completion
     * 
     * @param array $messages Chat messages
     * @param array $options Additional options
     * @return array Response
     */
    public function chat($messages, $options = []) {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'error' => 'Ollama service is not available'
            ];
        }
        
        $temperature = $options['temperature'] ?? 0.7;
        $numPredict = $options['num_predict'] ?? 1000;
        $stream = $options['stream'] ?? false;
        
        // Add system prompt if not present
        $hasSystemPrompt = false;
        foreach ($messages as $msg) {
            if (isset($msg['role']) && $msg['role'] === 'system') {
                $hasSystemPrompt = true;
                break;
            }
        }
        
        if (!$hasSystemPrompt) {
            array_unshift($messages, [
                'role' => 'system',
                'content' => $this->systemPrompt
            ]);
        }
        
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => $stream,
            'options' => [
                'temperature' => $temperature,
                'num_predict' => $numPredict
            ]
        ];
        
        try {
            $ch = curl_init($this->baseUrl . '/api/chat');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("Ollama API error: " . $response);
                return [
                    'success' => false,
                    'error' => 'API request failed'
                ];
            }
            
            $result = json_decode($response, true);
            
            return [
                'success' => true,
                'message' => $result['message']['content'] ?? '',
                'done' => $result['done'] ?? true,
                'model' => $result['model'] ?? $this->model
            ];
        } catch (\Exception $e) {
            error_log("Ollama chat error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Request failed'
            ];
        }
    }
    
    /**
     * Generate text completion
     * 
     * @param string $prompt Text prompt
     * @param array $options Additional options
     * @return array Response
     */
    public function generate($prompt, $options = []) {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'error' => 'Ollama service is not available'
            ];
        }
        
        $temperature = $options['temperature'] ?? 0.7;
        $numPredict = $options['num_predict'] ?? 500;
        
        $payload = [
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => $temperature,
                'num_predict' => $numPredict
            ]
        ];
        
        try {
            $ch = curl_init($this->baseUrl . '/api/generate');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("Ollama generate error: " . $response);
                return [
                    'success' => false,
                    'error' => 'API request failed'
                ];
            }
            
            $result = json_decode($response, true);
            
            return [
                'success' => true,
                'response' => $result['response'] ?? '',
                'done' => $result['done'] ?? true
            ];
        } catch (\Exception $e) {
            error_log("Ollama generate error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Request failed'
            ];
        }
    }
    
    /**
     * Get available models
     * 
     * @return array Models
     */
    public function getModels() {
        if (!$this->isAvailable()) {
            return [];
        }
        
        try {
            $ch = curl_init($this->baseUrl . '/api/tags');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return [];
            }
            
            $result = json_decode($response, true);
            return $result['models'] ?? [];
        } catch (\Exception $e) {
            error_log("Ollama get models error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Pull a model
     * 
     * @param string $modelName Model name
     * @return array Response
     */
    public function pullModel($modelName) {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'error' => 'Ollama service is not available'
            ];
        }
        
        try {
            $ch = curl_init($this->baseUrl . '/api/pull');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['name' => $modelName]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes for model download
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'error' => 'Model pull failed'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Model pulled successfully'
            ];
        } catch (\Exception $e) {
            error_log("Ollama pull model error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Request failed'
            ];
        }
    }
    
    /**
     * AI Search - Search destinations using AI
     * 
     * @param string $query Search query
     * @param array $destinations Available destinations
     * @return array Search results
     */
    public function aiSearch($query, $destinations) {
        if (!$this->isAvailable()) {
            // Fallback to simple text matching
            return $this->fallbackSearch($query, $destinations);
        }
        
        $systemPrompt = "You are a travel search assistant for MyWisata. Given a user query and a list of destinations, return the most relevant destinations as a JSON array of destination IDs. Only return the JSON array, nothing else.";
        
        $destinationsText = json_encode($destinations);
        
        $userPrompt = "User query: \"{$query}\"\n\nAvailable destinations:\n{$destinationsText}\n\nReturn the IDs of the most relevant destinations as a JSON array.";
        
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];
        
        $response = $this->chat($messages, ['num_predict' => 500]);
        
        if (!$response['success']) {
            return $this->fallbackSearch($query, $destinations);
        }
        
        try {
            $ids = json_decode($response['message'], true);
            if (!is_array($ids)) {
                return $this->fallbackSearch($query, $destinations);
            }
            
            $results = [];
            foreach ($ids as $id) {
                foreach ($destinations as $dest) {
                    if ($dest['id'] == $id) {
                        $results[] = $dest;
                        break;
                    }
                }
            }
            
            return $results;
        } catch (\Exception $e) {
            return $this->fallbackSearch($query, $destinations);
        }
    }
    
    /**
     * Fallback search (simple text matching)
     * 
     * @param string $query Search query
     * @param array $destinations Available destinations
     * @return array Search results
     */
    private function fallbackSearch($query, $destinations) {
        $query = strtolower($query);
        $results = [];
        
        foreach ($destinations as $dest) {
            $name = strtolower($dest['name'] ?? '');
            $description = strtolower($dest['description'] ?? '');
            $tags = strtolower($dest['tags'] ?? '');
            
            if (strpos($name, $query) !== false || 
                strpos($description, $query) !== false || 
                strpos($tags, $query) !== false) {
                $results[] = $dest;
            }
        }
        
        return $results;
    }
    
    /**
     * AI Customer Service - Answer common questions
     * 
     * @param string $question User question
     * @param array $context Context information
     * @return array Response
     */
    public function aiCustomerService($question, $context = []) {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'AI service is currently unavailable. Please contact support via email or phone.',
                'fallback' => true
            ];
        }
        
        $systemPrompt = "You are a customer service assistant for MyWisata, a tour guide application in Indonesia. Help users with booking, destinations, tour guides, and general inquiries. Be friendly, helpful, and concise. Respond in Indonesian unless the user asks in English.";
        
        $contextText = !empty($context) ? "Context: " . json_encode($context) : '';
        
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $contextText . "\n\nQuestion: " . $question]
        ];
        
        return $this->chat($messages, ['num_predict' => 500]);
    }
    
    /**
     * Set system prompt
     * 
     * @param string $prompt System prompt
     */
    public function setSystemPrompt($prompt) {
        $this->systemPrompt = $prompt;
    }
    
    /**
     * Set model
     * 
     * @param string $model Model name
     */
    public function setModel($model) {
        $this->model = $model;
    }
    
    /**
     * Get current model
     * 
     * @return string Model name
     */
    public function getModel() {
        return $this->model;
    }
}
