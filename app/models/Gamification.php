<?php
/**
 * MyWisata Application - Gamification Model
 * 
 * Handles gamification database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Gamification extends Model {
    
    /**
     * Get user gamification profile
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserProfile($userId) {
        $sql = "SELECT u.*, up.points, up.level, up.experience,
                (SELECT COUNT(*) FROM user_badges WHERE user_id = :user_id) as badge_count,
                (SELECT COUNT(*) FROM user_achievements WHERE user_id = :user_id) as achievement_count
                FROM users u
                LEFT JOIN user_points up ON u.id = up.user_id
                WHERE u.id = :user_id";
        
        $profile = $this->db->query($sql, ['user_id' => $userId])->fetch();
        
        if (!$profile || !$profile['points']) {
            // Initialize user points if not exists
            $this->initializeUserPoints($userId);
            $profile = $this->db->query($sql, ['user_id' => $userId])->fetch();
        }
        
        return $profile;
    }
    
    /**
     * Get user points
     * 
     * @param int $userId User ID
     * @return int
     */
    public function getUserPoints($userId) {
        $sql = "SELECT points FROM user_points WHERE user_id = :user_id";
        $result = $this->db->query($sql, ['user_id' => $userId])->fetch();
        
        return $result ? $result['points'] : 0;
    }
    
    /**
     * Award points to user
     * 
     * @param int $userId User ID
     * @param string $action Action type
     * @param int $itemId Optional item ID
     * @return int Points awarded
     */
    public function awardPoints($userId, $action, $itemId = null) {
        $points = $this->getPointsForAction($action);
        
        if ($points === 0) {
            return 0;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Update user points
            $sql = "INSERT INTO user_points (user_id, points, level, experience, updated_at)
                    VALUES (:user_id, :points, 1, :points, NOW())
                    ON DUPLICATE KEY UPDATE 
                    points = points + :points,
                    experience = experience + :points,
                    updated_at = NOW()";
            
            $this->db->query($sql, [
                'user_id' => $userId,
                'points' => $points
            ]);
            
            // Log point history
            $sql = "INSERT INTO point_history (user_id, action, item_id, points, created_at)
                    VALUES (:user_id, :action, :item_id, :points, NOW())";
            
            $this->db->query($sql, [
                'user_id' => $userId,
                'action' => $action,
                'item_id' => $itemId,
                'points' => $points
            ]);
            
            // Update level based on experience
            $this->updateUserLevel($userId);
            
            $this->db->commit();
            return $points;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to award points', [
                'user_id' => $userId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Get points for action
     * 
     * @param string $action Action type
     * @return int
     */
    private function getPointsForAction($action) {
        $pointMap = [
            'booking' => 100,
            'review' => 50,
            'favorite' => 10,
            'share' => 20,
            'login_daily' => 5,
            'complete_profile' => 100,
            'invite_friend' => 200,
            'itinerary_create' => 30,
            'itinerary_complete' => 100
        ];
        
        return $pointMap[$action] ?? 0;
    }
    
    /**
     * Update user level based on experience
     * 
     * @param int $userId User ID
     */
    private function updateUserLevel($userId) {
        $sql = "SELECT experience FROM user_points WHERE user_id = :user_id";
        $result = $this->db->query($sql, ['user_id' => $userId])->fetch();
        
        if (!$result) {
            return;
        }
        
        $experience = $result['experience'];
        $level = $this->calculateLevel($experience);
        
        $sql = "UPDATE user_points SET level = :level WHERE user_id = :user_id";
        $this->db->query($sql, ['level' => $level, 'user_id' => $userId]);
    }
    
    /**
     * Calculate level from experience
     * 
     * @param int $experience Experience points
     * @return int Level
     */
    private function calculateLevel($experience) {
        // Level formula: level = floor(sqrt(experience / 100)) + 1
        return floor(sqrt($experience / 100)) + 1;
    }
    
    /**
     * Initialize user points
     * 
     * @param int $userId User ID
     */
    private function initializeUserPoints($userId) {
        $sql = "INSERT INTO user_points (user_id, points, level, experience, created_at)
                VALUES (:user_id, 0, 1, 0, NOW())";
        $this->db->query($sql, ['user_id' => $userId]);
    }
    
    /**
     * Get user badges
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserBadges($userId) {
        $sql = "SELECT ub.*, b.name, b.description, b.icon, b.category
                FROM user_badges ub
                LEFT JOIN badges b ON ub.badge_id = b.id
                WHERE ub.user_id = :user_id
                ORDER BY ub.earned_at DESC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Get all badges
     * 
     * @return array
     */
    public function getAllBadges() {
        $sql = "SELECT * FROM badges ORDER BY category, required_points";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Check and award badges
     * 
     * @param int $userId User ID
     * @return array New badges awarded
     */
    public function checkAndAwardBadges($userId) {
        $userPoints = $this->getUserPoints($userId);
        $allBadges = $this->getAllBadges();
        $userBadges = $this->getUserBadges($userId);
        $userBadgeIds = array_column($userBadges, 'badge_id');
        
        $newBadges = [];
        
        foreach ($allBadges as $badge) {
            if (in_array($badge['id'], $userBadgeIds)) {
                continue;
            }
            
            if ($userPoints >= $badge['required_points']) {
                $this->awardBadge($userId, $badge['id']);
                $newBadges[] = $badge;
            }
        }
        
        return $newBadges;
    }
    
    /**
     * Award badge to user
     * 
     * @param int $userId User ID
     * @param int $badgeId Badge ID
     * @return bool
     */
    public function awardBadge($userId, $badgeId) {
        $sql = "INSERT INTO user_badges (user_id, badge_id, earned_at)
                VALUES (:user_id, :badge_id, NOW())
                ON DUPLICATE KEY UPDATE earned_at = NOW()";
        
        return $this->db->query($sql, ['user_id' => $userId, 'badge_id' => $badgeId]);
    }
    
    /**
     * Get user achievements
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserAchievements($userId) {
        $sql = "SELECT ua.*, a.name, a.description, a.icon, a.type
                FROM user_achievements ua
                LEFT JOIN achievements a ON ua.achievement_id = a.id
                WHERE ua.user_id = :user_id
                ORDER BY ua.completed_at DESC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Get leaderboard
     * 
     * @param int $limit Limit
     * @return array
     */
    public function getLeaderboard($limit = 10) {
        $sql = "SELECT u.id, u.name, u.avatar, up.points, up.level, up.experience
                FROM users u
                LEFT JOIN user_points up ON u.id = up.user_id
                ORDER BY up.points DESC, up.experience DESC
                LIMIT :limit";
        
        return $this->db->query($sql, ['limit' => $limit])->fetchAll();
    }
    
    /**
     * Get available rewards
     * 
     * @return array
     */
    public function getAvailableRewards() {
        $sql = "SELECT * FROM rewards 
                WHERE is_active = 1 
                AND (available_until IS NULL OR available_until > NOW())
                ORDER BY points_required ASC";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Redeem reward
     * 
     * @param int $userId User ID
     * @param int $rewardId Reward ID
     * @return bool
     */
    public function redeemReward($userId, $rewardId) {
        try {
            $this->db->beginTransaction();
            
            // Get user points
            $userPoints = $this->getUserPoints($userId);
            
            // Get reward details
            $sql = "SELECT * FROM rewards WHERE id = :id AND is_active = 1";
            $reward = $this->db->query($sql, ['id' => $rewardId])->fetch();
            
            if (!$reward) {
                $this->db->rollBack();
                return false;
            }
            
            // Check if user has enough points
            if ($userPoints < $reward['points_required']) {
                $this->db->rollBack();
                return false;
            }
            
            // Deduct points
            $sql = "UPDATE user_points 
                    SET points = points - :points, updated_at = NOW() 
                    WHERE user_id = :user_id";
            $this->db->query($sql, [
                'points' => $reward['points_required'],
                'user_id' => $userId
            ]);
            
            // Log redemption
            $sql = "INSERT INTO user_rewards (user_id, reward_id, points_spent, redeemed_at)
                    VALUES (:user_id, :reward_id, :points_spent, NOW())";
            $this->db->query($sql, [
                'user_id' => $userId,
                'reward_id' => $rewardId,
                'points_spent' => $reward['points_required']
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to redeem reward', [
                'user_id' => $userId,
                'reward_id' => $rewardId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
