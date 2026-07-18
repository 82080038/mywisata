<?php

/**
 * MyWisata Application - Cart Helper
 *
 * Handles multi-service shopping cart functionality.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class Cart
{
    private static $sessionKey = 'cart';

    /**
     * Get cart contents
     *
     * @return array Cart items
     */
    public static function get()
    {
        return Session::get(self::$sessionKey, []);
    }

    /**
     * Add item to cart
     *
     * @param string $type Item type (destination, hotel, restaurant, event, tour_guide)
     * @param int $itemId Item ID
     * @param array $data Additional item data
     * @param int $quantity Quantity
     * @return bool
     */
    public static function add($type, $itemId, $data = [], $quantity = 1)
    {
        $cart = self::get();
        $itemKey = self::generateItemKey($type, $itemId, $data);

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
        } else {
            $cart[$itemKey] = [
                'type' => $type,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'data' => $data,
                'price' => $data['price'] ?? 0,
                'name' => $data['name'] ?? '',
                'added_at' => time(),
            ];
        }

        self::save($cart);

        Logger::info('Item added to cart', [
            'type' => $type,
            'item_id' => $itemId,
            'quantity' => $quantity,
        ]);

        return true;
    }

    /**
     * Remove item from cart
     *
     * @param string $type Item type
     * @param int $itemId Item ID
     * @param array $data Additional item data
     * @return bool
     */
    public static function remove($type, $itemId, $data = [])
    {
        $cart = self::get();
        $itemKey = self::generateItemKey($type, $itemId, $data);

        if (isset($cart[$itemKey])) {
            unset($cart[$itemKey]);
            self::save($cart);

            Logger::info('Item removed from cart', [
                'type' => $type,
                'item_id' => $itemId,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Remove item from cart by key
     *
     * @param string $itemKey Cart item key
     * @return bool
     */
    public static function removeByKey($itemKey)
    {
        $cart = self::get();

        if (isset($cart[$itemKey])) {
            unset($cart[$itemKey]);
            self::save($cart);
            return true;
        }

        return false;
    }

    /**
     * Update item quantity
     *
     * @param string $type Item type
     * @param int $itemId Item ID
     * @param int $quantity New quantity
     * @param array $data Additional item data
     * @return bool
     */
    public static function updateQuantity($type, $itemId, $quantity, $data = [])
    {
        $cart = self::get();
        $itemKey = self::generateItemKey($type, $itemId, $data);

        if (isset($cart[$itemKey])) {
            if ($quantity <= 0) {
                return self::remove($type, $itemId, $data);
            }

            $cart[$itemKey]['quantity'] = $quantity;
            self::save($cart);

            return true;
        }

        return false;
    }

    /**
     * Clear cart
     *
     * @return bool
     */
    public static function clear()
    {
        Session::set(self::$sessionKey, []);

        Logger::info('Cart cleared');

        return true;
    }

    /**
     * Get cart total
     *
     * @return float Total amount
     */
    public static function total()
    {
        $cart = self::get();
        $total = 0;

        foreach ($cart as $item) {
            $total += ($item['price'] * $item['quantity']);
        }

        return $total;
    }

    /**
     * Get cart count (total items)
     *
     * @return int Total number of items
     */
    public static function count()
    {
        $cart = self::get();
        $count = 0;

        foreach ($cart as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    /**
     * Get cart summary by type
     *
     * @return array Summary grouped by type
     */
    public static function summary()
    {
        $cart = self::get();
        $summary = [];

        foreach ($cart as $item) {
            $type = $item['type'];
            
            if (!isset($summary[$type])) {
                $summary[$type] = [
                    'count' => 0,
                    'total' => 0,
                    'items' => [],
                ];
            }

            $summary[$type]['count'] += $item['quantity'];
            $summary[$type]['total'] += ($item['price'] * $item['quantity']);
            $summary[$type]['items'][] = $item;
        }

        return $summary;
    }

    /**
     * Convert cart to transaction items
     *
     * @return array Transaction items
     */
    public static function toTransactionItems()
    {
        $cart = self::get();
        $items = [];

        foreach ($cart as $item) {
            $items[] = [
                'type' => $item['type'],
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ];
        }

        return $items;
    }

    /**
     * Generate unique item key
     *
     * @param string $type Item type
     * @param int $itemId Item ID
     * @param array $data Additional data
     * @return string Item key
     */
    private static function generateItemKey($type, $itemId, $data)
    {
        $key = $type . '_' . $itemId;
        
        // Add additional parameters to key for variants
        if (isset($data['date'])) {
            $key .= '_' . $data['date'];
        }
        if (isset($data['time'])) {
            $key .= '_' . $data['time'];
        }

        return md5($key);
    }

    /**
     * Save cart to session
     *
     * @param array $cart Cart data
     * @return void
     */
    private static function save($cart)
    {
        Session::set(self::$sessionKey, $cart);
    }

    /**
     * Check if cart is empty
     *
     * @return bool
     */
    public static function isEmpty()
    {
        return empty(self::get());
    }

    /**
     * Get item by key
     *
     * @param string $key Item key
     * @return array|null
     */
    public static function getItem($key)
    {
        $cart = self::get();
        return $cart[$key] ?? null;
    }
}
