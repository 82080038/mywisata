<?php
namespace App\Controllers;

use App\Services\OpenAIService;

class AIController extends Controller
{
    private $openai;

    public function __construct()
    {
        $this->openai = new OpenAIService();
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

        $result = $this->openai->recommendDestinations($preferences);

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

        $result = $this->openai->chatConversation($conversationHistory, $userMessage, $context);

        return $this->json($result);
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
