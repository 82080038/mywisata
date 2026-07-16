<?php
/**
 * MyWisata Application - Sentiment Analysis Helper
 * 
 * Handles sentiment analysis for reviews using keyword-based approach.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class SentimentAnalysis {
    
    private $positiveWords = [
        'bagus', 'luar biasa', 'hebat', 'mantap', 'keren', 'suka', 'cinta',
        'menyenangkan', 'indah', 'cantik', 'bersih', 'nyaman', 'ramah',
        'profesional', 'terbaik', 'recommended', 'puas', 'senang',
        'good', 'great', 'excellent', 'amazing', 'love', 'beautiful',
        'clean', 'comfortable', 'friendly', 'professional', 'best', 'happy',
        'nice', 'awesome', 'wonderful', 'fantastic', 'perfect'
    ];
    
    private $negativeWords = [
        'buruk', 'jelek', 'mengecewakan', 'sial', 'marah', 'benci',
        'kotor', 'berantakan', 'tidak nyaman', 'kasar', 'mahal',
        'buruk pelayanan', 'lambat', 'tidak recommended', 'kecewa',
        'bad', 'terrible', 'awful', 'hate', 'dirty', 'messy',
        'uncomfortable', 'rude', 'expensive', 'slow', 'disappointed',
        'worst', 'horrible', 'poor', 'not recommended'
    ];
    
    /**
     * Analyze sentiment of text
     * 
     * @param string $text Text to analyze
     * @return array Sentiment analysis result
     */
    public function analyze($text) {
        $text = strtolower($text);
        
        $positiveCount = 0;
        $negativeCount = 0;
        $foundPositive = [];
        $foundNegative = [];
        
        // Check for positive words
        foreach ($this->positiveWords as $word) {
            if (strpos($text, $word) !== false) {
                $positiveCount++;
                $foundPositive[] = $word;
            }
        }
        
        // Check for negative words
        foreach ($this->negativeWords as $word) {
            if (strpos($text, $word) !== false) {
                $negativeCount++;
                $foundNegative[] = $word;
            }
        }
        
        // Calculate sentiment score
        $totalWords = $positiveCount + $negativeCount;
        
        if ($totalWords === 0) {
            $sentiment = 'neutral';
            $score = 0;
        } elseif ($positiveCount > $negativeCount) {
            $sentiment = 'positive';
            $score = ($positiveCount - $negativeCount) / $totalWords;
        } elseif ($negativeCount > $positiveCount) {
            $sentiment = 'negative';
            $score = -($negativeCount - $positiveCount) / $totalWords;
        } else {
            $sentiment = 'neutral';
            $score = 0;
        }
        
        return [
            'sentiment' => $sentiment,
            'score' => round($score, 2),
            'positive_count' => $positiveCount,
            'negative_count' => $negativeCount,
            'found_positive' => $foundPositive,
            'found_negative' => $foundNegative,
            'confidence' => $this->calculateConfidence($totalWords, $score)
        ];
    }
    
    /**
     * Calculate confidence level
     * 
     * @param int $totalWords Total sentiment words found
     * @param float $score Sentiment score
     * @return string
     */
    private function calculateConfidence($totalWords, $score) {
        if ($totalWords === 0) {
            return 'low';
        }
        
        $absScore = abs($score);
        
        if ($absScore >= 0.7) {
            return 'high';
        } elseif ($absScore >= 0.4) {
            return 'medium';
        }
        
        return 'low';
    }
    
    /**
     * Batch analyze reviews
     * 
     * @param array $reviews Array of review texts
     * @return array Analysis results
     */
    public function batchAnalyze($reviews) {
        $results = [];
        $sentimentCounts = [
            'positive' => 0,
            'neutral' => 0,
            'negative' => 0
        ];
        $totalScore = 0;
        
        foreach ($reviews as $review) {
            $analysis = $this->analyze($review);
            $results[] = $analysis;
            
            $sentimentCounts[$analysis['sentiment']]++;
            $totalScore += $analysis['score'];
        }
        
        $averageScore = count($reviews) > 0 ? $totalScore / count($reviews) : 0;
        
        return [
            'individual_results' => $results,
            'summary' => [
                'total_reviews' => count($reviews),
                'sentiment_distribution' => $sentimentCounts,
                'average_score' => round($averageScore, 2),
                'overall_sentiment' => $this->getOverallSentiment($averageScore)
            ]
        ];
    }
    
    /**
     * Get overall sentiment from average score
     * 
     * @param float $averageScore Average sentiment score
     * @return string
     */
    private function getOverallSentiment($averageScore) {
        if ($averageScore >= 0.3) {
            return 'positive';
        } elseif ($averageScore <= -0.3) {
            return 'negative';
        }
        return 'neutral';
    }
    
    /**
     * Analyze and update review sentiment in database
     * 
     * @param int $reviewId Review ID
     * @return bool
     */
    public function analyzeAndUpdateReview($reviewId) {
        $db = Database::getInstance();
        
        // Get review text
        $sql = "SELECT comment FROM reviews WHERE id = :id";
        $result = $db->query($sql, ['id' => $reviewId])->fetch();
        
        if (!$result) {
            return false;
        }
        
        // Analyze sentiment
        $analysis = $this->analyze($result['comment']);
        
        // Update review with sentiment data
        $sql = "UPDATE reviews 
                SET sentiment = :sentiment, 
                    sentiment_score = :score,
                    updated_at = NOW()
                WHERE id = :id";
        
        return $db->query($sql, [
            'sentiment' => $analysis['sentiment'],
            'score' => $analysis['score'],
            'id' => $reviewId
        ]);
    }
    
    /**
     * Get sentiment statistics for an item
     * 
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @return array
     */
    public function getItemSentimentStats($itemType, $itemId) {
        $db = Database::getInstance();
        
        $sql = "SELECT 
                COUNT(*) as total_reviews,
                SUM(CASE WHEN sentiment = 'positive' THEN 1 ELSE 0 END) as positive_count,
                SUM(CASE WHEN sentiment = 'neutral' THEN 1 ELSE 0 END) as neutral_count,
                SUM(CASE WHEN sentiment = 'negative' THEN 1 ELSE 0 END) as negative_count,
                AVG(sentiment_score) as average_score
                FROM reviews 
                WHERE item_type = :item_type AND item_id = :item_id";
        
        $result = $db->query($sql, [
            'item_type' => $itemType,
            'item_id' => $itemId
        ])->fetch();
        
        if (!$result || $result['total_reviews'] == 0) {
            return [
                'total_reviews' => 0,
                'positive_count' => 0,
                'neutral_count' => 0,
                'negative_count' => 0,
                'average_score' => 0,
                'positive_percentage' => 0,
                'neutral_percentage' => 0,
                'negative_percentage' => 0,
                'overall_sentiment' => 'neutral'
            ];
        }
        
        $total = $result['total_reviews'];
        
        return [
            'total_reviews' => $total,
            'positive_count' => $result['positive_count'],
            'neutral_count' => $result['neutral_count'],
            'negative_count' => $result['negative_count'],
            'average_score' => round($result['average_score'], 2),
            'positive_percentage' => round(($result['positive_count'] / $total) * 100, 1),
            'neutral_percentage' => round(($result['neutral_count'] / $total) * 100, 1),
            'negative_percentage' => round(($result['negative_count'] / $total) * 100, 1),
            'overall_sentiment' => $this->getOverallSentiment($result['average_score'])
        ];
    }
    
    /**
     * Add custom sentiment words
     * 
     * @param array $positiveWords Positive words to add
     * @param array $negativeWords Negative words to add
     */
    public function addCustomWords($positiveWords = [], $negativeWords = []) {
        $this->positiveWords = array_merge($this->positiveWords, $positiveWords);
        $this->negativeWords = array_merge($this->negativeWords, $negativeWords);
    }
    
    /**
     * Get sentiment trend over time
     * 
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @param int $days Number of days to analyze
     * @return array
     */
    public function getSentimentTrend($itemType, $itemId, $days = 30) {
        $db = Database::getInstance();
        
        $sql = "SELECT 
                DATE(created_at) as date,
                AVG(sentiment_score) as average_score,
                COUNT(*) as review_count
                FROM reviews 
                WHERE item_type = :item_type 
                AND item_id = :item_id
                AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $results = $db->query($sql, [
            'item_type' => $itemType,
            'item_id' => $itemId,
            'days' => $days
        ])->fetchAll();
        
        return $results;
    }
}
