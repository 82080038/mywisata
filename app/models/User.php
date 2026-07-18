<?php

/**
 * MyWisata Application - User Model
 *
 * Handles user-related database operations.
 *
 * @version 1.0.0
 *
 * @since 2026-06-30
 */
class User extends Model
{
    protected $table = 'users';

    /**
     * Find user by email
     *
     * @param string $email Email address
     *
     * @return array|false
     */
    public function findByEmail($email)
    {
        return $this->findBy(['email' => $email]);
    }

    /**
     * Register new user
     *
     * @param array $data User data
     *
     * @return int User ID
     */
    public function register($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->insert($data);
    }

    /**
     * Verify user credentials
     *
     * @param string $email Email
     * @param string $password Password
     *
     * @return array|false User data if valid, false otherwise
     */
    public function verify($email, $password)
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Check if user is active
            if ($user['status'] !== 'active') {
                return false;
            }

            return $user;
        }

        return false;
    }

    /**
     * Update last login
     *
     * @param int $userId User ID
     */
    public function updateLastLogin($userId)
    {
        $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    /**
     * Update password
     *
     * @param int $userId User ID
     * @param string $newPassword New password
     */
    public function updatePassword($userId, $newPassword)
    {
        $this->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);
    }

    /**
     * Get users by role
     *
     * @param string $role Role
     * @param int $limit Limit
     * @param int $offset Offset
     *
     * @return array
     */
    public function getByRole($role, $limit = null, $offset = 0)
    {
        return $this->getAll(['role' => $role], 'created_at DESC', $limit, $offset);
    }

    /**
     * Update user status
     *
     * @param int $userId User ID
     * @param string $status Status
     */
    public function updateStatus($userId, $status)
    {
        $this->update($userId, ['status' => $status]);
    }

    /**
     * Get user's food allergies and dietary preferences
     *
     * @param int $userId
     * @return array
     */
    public function getFoodPreferences($userId)
    {
        $sql = "SELECT food_allergies, dietary_preferences, allergy_notes FROM {$this->table} WHERE id = :id";
        $user = $this->db->query($sql, ['id' => $userId])->fetch();
        return [
            'allergies' => $this->parseJsonArray($user['food_allergies'] ?? null),
            'preferences' => $this->parseJsonArray($user['dietary_preferences'] ?? null),
            'notes' => $user['allergy_notes'] ?? '',
        ];
    }

    /**
     * Update user's food allergies and dietary preferences
     *
     * @param int $userId
     * @param array $allergies
     * @param array $preferences
     * @param string $notes
     * @return bool
     */
    public function updateFoodPreferences($userId, $allergies, $preferences, $notes = '')
    {
        $sql = "UPDATE {$this->table} 
                SET food_allergies = :allergies, 
                    dietary_preferences = :preferences, 
                    allergy_notes = :notes 
                WHERE id = :id";
        return $this->db->query($sql, [
            'id' => $userId,
            'allergies' => json_encode(array_values($allergies)),
            'preferences' => json_encode(array_values($preferences)),
            'notes' => $notes,
        ]);
    }

    /**
     * Parse JSON array field
     *
     * @param string|null $json
     * @return array
     */
    private function parseJsonArray($json)
    {
        if (empty($json)) {
            return [];
        }
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    /**
     * Get allergy label in Indonesian
     *
     * @param string $key
     * @return string
     */
    public static function allergyLabel($key)
    {
        $labels = [
            'peanut' => 'Kacang',
            'seafood' => 'Makanan Laut (Seafood)',
            'shellfish' => 'Kerang-kerangan',
            'fish' => 'Ikan',
            'milk' => 'Susu / Produk Olahan Susu',
            'lactose' => 'Laktosa',
            'gluten' => 'Gluten (Gandum)',
            'egg' => 'Telur',
            'soy' => 'Kedelai',
            'nuts' => 'Kacang-kacangan',
            'sesame' => 'Wijen',
            'sulfite' => 'Sulfit',
            'msg' => 'MSG (Monosodium Glutamat)',
            'histamine' => 'Histamin',
        ];
        return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Get dietary preference label in Indonesian
     *
     * @param string $key
     * @return string
     */
    public static function preferenceLabel($key)
    {
        $labels = [
            'halal' => 'Halal',
            'kosher' => 'Kosher',
            'vegetarian' => 'Vegetarian',
            'vegan' => 'Vegan',
            'pescatarian' => 'Pescatarian (Tidak makan daging, tapi makan ikan)',
            'no_spicy' => 'Tidak Pedas',
            'no_pork' => 'Tidak Babi',
            'no_beef' => 'Tidak Sapi',
            'no_alcohol' => 'Tanpa Alkohol',
            'low_sugar' => 'Rendah Gula',
            'low_salt' => 'Rendah Garam',
            'diabetic' => 'Diabetes (Rendah Gula)',
            'hypertension' => 'Hipertensi (Rendah Garam)',
        ];
        return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Get all allergy options
     *
     * @return array
     */
    public static function getAllergyOptions()
    {
        return [
            'peanut', 'seafood', 'shellfish', 'fish', 'milk', 'lactose',
            'gluten', 'egg', 'soy', 'nuts', 'sesame', 'sulfite', 'msg', 'histamine'
        ];
    }

    /**
     * Get all dietary preference options
     *
     * @return array
     */
    public static function getPreferenceOptions()
    {
        return [
            'halal', 'kosher', 'vegetarian', 'vegan', 'pescatarian',
            'no_spicy', 'no_pork', 'no_beef', 'no_alcohol',
            'low_sugar', 'low_salt', 'diabetic', 'hypertension'
        ];
    }
}
