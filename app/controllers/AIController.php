<?php
namespace App\Controllers;

use App\Services\OpenAIService;
use App\Services\OllamaService;

class AIController extends Controller
{
    private $openai;
    private $ollama;
    private $useSelfHosted;

    public function __construct()
    {
        $this->openai = new OpenAIService();
        $this->ollama = new OllamaService();
        $this->useSelfHosted = getenv('USE_SELF_HOSTED_AI') === 'true';
    }

    /**
     * Get AI service (OpenAI or Ollama based on config)
     */
    private function getAIService()
    {
        return $this->useSelfHosted ? $this->ollama : $this->openai;
    }

    /**
     * Get AI recommendations
     */
    public function recommendations()
    {
        $preferences = [
            'interests' => $_POST['interests'] ?? [],
            'budget' => $_POST['budget'] ?? 'medium',
            'duration' => $_POST['duration'] ?? 3,
            'group_size' => $_POST['group_size'] ?? 2,
            'language' => $_POST['language'] ?? 'english'
        ];

        $aiService = $this->getAIService();
        
        if ($this->useSelfHosted) {
            // Use Ollama for recommendations
            $result = $this->ollama->aiSearch(
                implode(', ', $preferences['interests']),
                $this->getDestinations()
            );
        } else {
            $result = $this->openai->recommendDestinations($preferences);
        }

        return $this->json($result);
    }

    /**
     * Get tour guide recommendations
     */
    public function guideRecommendations()
    {
        $requirements = [
            'destination' => $_POST['destination'] ?? '',
            'date' => $_POST['date'] ?? '',
            'duration' => $_POST['duration'] ?? 4,
            'languages' => $_POST['languages'] ?? ['english'],
            'specializations' => $_POST['specializations'] ?? []
        ];

        $availableGuides = $_POST['available_guides'] ?? [];

        $result = $this->openai->recommendTourGuides($requirements, $availableGuides);

        return $this->json($result);
    }

    /**
     * Generate trip itinerary
     */
    public function itinerary()
    {
        $destination = $_POST['destination'] ?? '';
        $duration = $_POST['duration'] ?? 3;
        $interests = $_POST['interests'] ?? [];

        $result = $this->openai->generateItinerary($destination, $duration, $interests);

        return $this->json($result);
    }

    /**
     * Generate content
     */
    public function generateContent()
    {
        $type = $_POST['type'] ?? 'description';
        $name = $_POST['name'] ?? '';
        $features = $_POST['features'] ?? [];

        if ($type === 'description') {
            $result = $this->openai->generateDescription($name, $features);
        } else {
            $result = ['success' => false, 'error' => 'Unknown content type'];
        }

        return $this->json($result);
    }

    /**
     * Analyze sentiment
     */
    public function sentiment()
    {
        $text = $_POST['text'] ?? '';

        $result = $this->openai->analyzeSentiment($text);

        return $this->json($result);
    }

    /**
     * AI chat endpoint
     */
    public function chat()
    {
        $conversationHistory = json_decode($_POST['conversation_history'] ?? '[]', true);
        $userMessage = $_POST['message'] ?? '';
        $context = [
            'user_id' => $_SESSION['user_id'] ?? null,
            'current_page' => $_POST['current_page'] ?? ''
        ];

        if ($this->useSelfHosted) {
            $result = $this->ollama->aiCustomerService($userMessage, $context);
        } else {
            $result = $this->openai->chatConversation($conversationHistory, $userMessage, $context);
        }

        return $this->json($result);
    }

    /**
     * Get destinations for AI search
     */
    private function getDestinations()
    {
        // This would normally come from the Destination model
        // For now, return empty array - to be implemented
        return [];
    }

    /**
     * Translate text
     */
    public function translate()
    {
        $text = $_POST['text'] ?? '';
        $targetLanguage = $_POST['target_language'] ?? 'english';

        $result = $this->openai->translate($text, $targetLanguage);

        return $this->json($result);
    }
}
