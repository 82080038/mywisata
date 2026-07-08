<?php

/**
 * MyWisata Application - AI Tour Guide Controller
 *
 * Handles AI chat functionality for tour guidance.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class AITourGuideController extends Controller
{
    /**
     * Index - Show AI chat interface
     */
    public function index()
    {
        $data = [
            'title' => 'AI Tour Guide - MyWisata',
        ];

        $this->view('aitourguide/index', $data);
    }

    /**
     * Chat - Process AI chat message
     */
    public function chat()
    {
        if (!$this->isAjax()) {
            $this->redirect('aitourguide');
        }

        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $message = $this->post('message');
        $conversationHistory = $this->post('history', []);
        $userId = Session::get('user_id');

        if (empty($message)) {
            $this->json(['status' => 'error', 'message' => 'Message cannot be empty'], 400);
        }

        // Use AI helper for response
        $result = AIHelper::chat($message, $conversationHistory);

        // Log the conversation
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO ai_conversations (user_id, user_message, ai_response, tokens_used, created_at) 
                    VALUES (:user_id, :user_message, :ai_response, :tokens_used, NOW())",
            [
                'user_id' => $userId, 
                'user_message' => $message, 
                'ai_response' => $result['response'],
                'tokens_used' => $result['tokens_used'],
            ]
        );

        $this->json([
            'status' => 'success', 
            'response' => $result['response'],
            'tokens_used' => $result['tokens_used'],
        ]);
    }

    /**
     * Get recommendations
     */
    public function recommendations()
    {
        if (!$this->isAjax()) {
            $this->redirect('aitourguide');
        }

        $location = $this->post('location');
        $interests = $this->post('interests', []);
        $budget = $this->post('budget');
        $duration = $this->post('duration');
        $groupSize = $this->post('group_size');

        $context = [
            'location' => $location,
            'interests' => is_array($interests) ? $interests : explode(',', $interests),
            'budget' => $budget,
            'duration' => $duration,
            'group_size' => $groupSize,
        ];

        $result = AIHelper::getTourRecommendations($context);

        $this->json([
            'status' => 'success',
            'recommendations' => $result['recommendations'],
            'source' => $result['source'] ?? 'ai',
        ]);
    }

    /**
     * Get destination info
     */
    public function destinationInfo()
    {
        if (!$this->isAjax()) {
            $this->redirect('aitourguide');
        }

        $destinationName = $this->post('destination_name');

        if (empty($destinationName)) {
            $this->json(['status' => 'error', 'message' => 'Destination name required'], 400);
        }

        $result = AIHelper::getDestinationInfo($destinationName);

        $this->json([
            'status' => 'success',
            'description' => $result['description'],
            'source' => $result['source'],
        ]);
    }

    /**
     * Check AI configuration status
     */
    public function status()
    {
        if (!$this->isAjax()) {
            $this->redirect('aitourguide');
        }

        $this->json([
            'status' => 'success',
            'configured' => AIHelper::isConfigured(),
        ]);
    }
}
