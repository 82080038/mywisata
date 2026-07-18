<?php

/**
 * MyWisata Application - Availability Model
 *
 * Centralized availability management for all bookable items.
 * Handles stock/quota checking and reduction for hotels, destinations,
 * events, restaurants, products, and tour guides.
 */
class Availability extends Model
{
    protected $table = 'hotel_rooms';

    /**
     * Check hotel room availability for date range
     */
    public function checkRoom($roomId, $checkIn, $checkOut, $numRooms = 1)
    {
        $db = Database::getInstance();

        // Get room info
        $room = $db->query(
            "SELECT * FROM hotel_rooms WHERE id = :id AND is_active = 1",
            ['id' => $roomId]
        )->fetch();

        if (!$room) {
            return ['available' => false, 'message' => 'Kamar tidak ditemukan'];
        }

        // Quick check: if available_rooms is 0, room is fully sold out
        if ($room['available_rooms'] <= 0) {
            return [
                'available' => false,
                'message' => 'Kamar sudah habis dipesan',
                'available_count' => 0,
                'total' => $room['total_rooms'],
            ];
        }

        // Also check available_rooms column
        if ($room['available_rooms'] < $numRooms) {
            return [
                'available' => false,
                'message' => "Kamar tidak cukup. Tersedia: {$room['available_rooms']} kamar",
                'available_count' => $room['available_rooms'],
                'total' => $room['total_rooms'],
            ];
        }

        // Count overlapping bookings for date range
        $overlap = $db->query(
            "SELECT COALESCE(SUM(num_rooms), 0) as booked
             FROM bookings 
             WHERE room_id = :rid 
             AND status IN ('pending', 'confirmed')
             AND check_in < :cout AND check_out > :cin",
            ['rid' => $roomId, 'cin' => $checkIn, 'cout' => $checkOut]
        )->fetch();

        $booked = $overlap['booked'] ?? 0;
        $available = $room['total_rooms'] - $booked;

        if ($available < $numRooms) {
            return [
                'available' => false,
                'message' => "Kamar tidak tersedia untuk tanggal tersebut. Tersedia: {$available} kamar",
                'available_count' => $available,
                'total' => $room['total_rooms'],
            ];
        }

        return [
            'available' => true,
            'available_count' => $available,
            'total' => $room['total_rooms'],
            'price_per_night' => $room['price_per_night'],
        ];
    }

    /**
     * Book a hotel room (reduce availability)
     */
    public function bookRoom($roomId, $checkIn, $checkOut, $numRooms = 1)
    {
        $db = Database::getInstance();

        // Update available_rooms (legacy column, keep in sync)
        $db->query(
            "UPDATE hotel_rooms SET available_rooms = GREATEST(available_rooms - :n, 0) WHERE id = :id",
            ['n' => $numRooms, 'id' => $roomId]
        );

        return true;
    }

    /**
     * Check destination ticket availability for a date
     */
    public function checkDestinationTicket($destinationId, $visitDate, $quantity = 1)
    {
        $db = Database::getInstance();

        $dest = $db->query(
            "SELECT id, name, daily_quota, daily_quota_used, is_active FROM destinations WHERE id = :id",
            ['id' => $destinationId]
        )->fetch();

        if (!$dest) {
            return ['available' => false, 'message' => 'Destinasi tidak ditemukan'];
        }

        if (!$dest['is_active']) {
            return ['available' => false, 'message' => 'Destinasi ini sedang tutup'];
        }

        if (empty($dest['daily_quota'])) {
            return ['available' => true, 'available_count' => 999999, 'total' => null];
        }

        // Count tickets sold for this date
        $sold = $db->query(
            "SELECT COALESCE(SUM(quantity), 0) as sold 
             FROM ticket_orders 
             WHERE destination_id = :did 
             AND visit_date = :vdate 
             AND status IN ('paid', 'pending')",
            ['did' => $destinationId, 'vdate' => $visitDate]
        )->fetch();

        $soldToday = $sold['sold'] ?? 0;
        $available = $dest['daily_quota'] - $soldToday;

        if ($available < $quantity) {
            return [
                'available' => false,
                'message' => "Tiket habis untuk tanggal " . date('d M Y', strtotime($visitDate)) . ". Tersedia: {$available} tiket",
                'available_count' => $available,
                'total' => $dest['daily_quota'],
            ];
        }

        return [
            'available' => true,
            'available_count' => $available,
            'total' => $dest['daily_quota'],
        ];
    }

    /**
     * Check event ticket availability
     */
    public function checkEventTicket($eventId, $quantity = 1)
    {
        $db = Database::getInstance();

        $event = $db->query(
            "SELECT id, title, max_participants, tickets_sold, is_active, end_date 
             FROM events WHERE id = :id",
            ['id' => $eventId]
        )->fetch();

        if (!$event) {
            return ['available' => false, 'message' => 'Event tidak ditemukan'];
        }

        if (!$event['is_active']) {
            return ['available' => false, 'message' => 'Event ini sudah berakhir'];
        }

        if (strtotime($event['end_date']) < time()) {
            return ['available' => false, 'message' => 'Event sudah selesai'];
        }

        if (empty($event['max_participants'])) {
            return ['available' => true, 'available_count' => 999999, 'total' => null];
        }

        $available = $event['max_participants'] - $event['tickets_sold'];

        if ($available < $quantity) {
            $msg = $available <= 0 ? "Tiket event sudah habis terjual" : "Tiket tersisa {$available}, Anda memesan {$quantity}";
            return [
                'available' => false,
                'message' => $msg,
                'available_count' => $available,
                'total' => $event['max_participants'],
            ];
        }

        return [
            'available' => true,
            'available_count' => $available,
            'total' => $event['max_participants'],
        ];
    }

    /**
     * Sell event ticket (reduce availability)
     */
    public function sellEventTicket($eventId, $quantity = 1)
    {
        $db = Database::getInstance();
        $db->query(
            "UPDATE events SET tickets_sold = tickets_sold + :qty WHERE id = :id",
            ['qty' => $quantity, 'id' => $eventId]
        );

        // Mark sold out if 0 remaining
        $db->query(
            "UPDATE events SET is_sold_out = 1 WHERE id = :id AND max_participants <= tickets_sold",
            ['id' => $eventId]
        );

        return true;
    }

    /**
     * Check tour guide availability for a date/time
     */
    public function checkGuide($guideId, $bookingDate, $startTime, $durationHours = 4)
    {
        $db = Database::getInstance();

        $guide = $db->query(
            "SELECT id, is_available FROM tour_guides WHERE id = :id",
            ['id' => $guideId]
        )->fetch();

        if (!$guide) {
            return ['available' => false, 'message' => 'Tour guide tidak ditemukan'];
        }

        if (!$guide['is_available']) {
            return ['available' => false, 'message' => 'Tour guide sedang tidak tersedia'];
        }

        // Check for booking conflicts on same date
        $endTime = date('H:i:s', strtotime($startTime) + $durationHours * 3600);
        $conflict = $db->query(
            "SELECT COUNT(*) as cnt FROM bookings 
             WHERE guide_id = :gid 
             AND booking_date = :bdate 
             AND status IN ('pending', 'confirmed')
             AND start_time < :end AND DATE_ADD(start_time, INTERVAL duration_hours HOUR) > :start",
            ['gid' => $guideId, 'bdate' => $bookingDate, 'end' => $endTime, 'start' => $startTime]
        )->fetch();

        if ($conflict['cnt'] > 0) {
            return [
                'available' => false,
                'message' => 'Tour guide sudah memiliki booking pada tanggal dan jam tersebut',
            ];
        }

        return ['available' => true];
    }

    /**
     * Check restaurant table availability
     */
    public function checkTable($restaurantId, $date, $time, $numPeople = 2)
    {
        $db = Database::getInstance();

        $rest = $db->query(
            "SELECT id, name, opening_time, closing_time, max_tables, available_tables, is_active 
             FROM restaurants WHERE id = :id",
            ['id' => $restaurantId]
        )->fetch();

        if (!$rest) {
            return ['available' => false, 'message' => 'Restoran tidak ditemukan'];
        }

        if (!$rest['is_active']) {
            return ['available' => false, 'message' => 'Restoran sedang tutup'];
        }

        // Check operating hours
        if ($rest['opening_time'] && $rest['closing_time']) {
            $bookingTime = strtotime($time);
            $open = strtotime($rest['opening_time']);
            $close = strtotime($rest['closing_time']);
            if ($bookingTime < $open || $bookingTime >= $close) {
                return [
                    'available' => false,
                    'message' => 'Restoran buka ' . date('H:i', $open) . ' - ' . date('H:i', $close) . '. Jam Anda: ' . date('H:i', $bookingTime),
                ];
            }
        }

        // Check table availability for the date
        $booked = $db->query(
            "SELECT COALESCE(SUM(num_tables), 0) as booked 
             FROM restaurant_reservations 
             WHERE restaurant_id = :rid 
             AND reservation_date = :rdate 
             AND reservation_time BETWEEN :open AND :close
             AND status IN ('pending', 'confirmed')",
            ['rid' => $restaurantId, 'rdate' => $date, 'open' => $rest['opening_time'] ?? '00:00:00', 'close' => $rest['closing_time'] ?? '23:59:59']
        )->fetch();

        // If no reservation table, use available_tables directly
        if ($rest['available_tables'] !== null) {
            $available = $rest['available_tables'];
            if ($available <= 0) {
                return [
                    'available' => false,
                    'message' => 'Meja sudah penuh untuk hari ini',
                    'available_count' => 0,
                    'total' => $rest['max_tables'],
                ];
            }
        }

        return [
            'available' => true,
            'available_count' => $rest['available_tables'] ?? 999,
            'total' => $rest['max_tables'] ?? null,
        ];
    }

    /**
     * Check product stock
     */
    public function checkProduct($productId, $quantity = 1)
    {
        $db = Database::getInstance();

        $product = $db->query(
            "SELECT id, name, stock, is_active FROM products WHERE id = :id",
            ['id' => $productId]
        )->fetch();

        if (!$product) {
            return ['available' => false, 'message' => 'Produk tidak ditemukan'];
        }

        if (!$product['is_active']) {
            return ['available' => false, 'message' => 'Produk tidak aktif'];
        }

        if ($product['stock'] < $quantity) {
            return [
                'available' => false,
                'message' => "Stok tidak mencukupi. Tersedia: {$product['stock']}, Anda memesan: {$quantity}",
                'available_count' => $product['stock'],
            ];
        }

        return [
            'available' => true,
            'available_count' => $product['stock'],
        ];
    }

    /**
     * Get availability status badge for display
     */
    public static function statusBadge($available, $total = null)
    {
        if ($available === 0 || $available === false) {
            return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Habis</span>';
        }
        if ($total && $available <= $total * 0.2) {
            return '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Tersisa ' . $available . '</span>';
        }
        if ($total && $available <= $total * 0.5) {
            return '<span class="badge bg-info text-dark"><i class="fas fa-clock me-1"></i>Tersisa ' . $available . '</span>';
        }
        return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Tersedia</span>';
    }
}
