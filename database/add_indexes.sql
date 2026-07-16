-- MyWisata Application - Database Indexes Optimization
-- Add indexes for frequently used queries

-- Add composite indexes for bookings table
ALTER TABLE bookings ADD INDEX IF NOT EXISTS idx_guide_date_status (guide_id, booking_date, status);
ALTER TABLE bookings ADD INDEX IF NOT EXISTS idx_user_status (user_id, status);

-- Add composite indexes for reviews table
ALTER TABLE reviews ADD INDEX IF NOT EXISTS idx_reviewable_rating (reviewable_type, reviewable_id, rating);
ALTER TABLE reviews ADD INDEX IF NOT EXISTS idx_user_created (user_id, created_at);

-- Add composite indexes for transactions table
ALTER TABLE transactions ADD INDEX IF NOT EXISTS idx_user_payment_status (user_id, payment_status);
ALTER TABLE transactions ADD INDEX IF NOT EXISTS idx_type_status (type, payment_status);

-- Add composite indexes for tour_guides table
ALTER TABLE tour_guides ADD INDEX IF NOT EXISTS idx_verified_available_rating (is_verified, is_available, rating_avg);

-- Add composite indexes for destinations table
ALTER TABLE destinations ADD INDEX IF NOT EXISTS idx_active_featured_rating (is_active, is_featured, rating_avg);
ALTER TABLE destinations ADD INDEX IF NOT EXISTS idx_city_active (city, is_active);

-- Add composite indexes for notifications table
ALTER TABLE notifications ADD INDEX IF NOT EXISTS idx_user_read_created (user_id, is_read, created_at);

-- Add composite indexes for events table
ALTER TABLE events ADD INDEX IF NOT EXISTS idx_dates_active (start_date, end_date, is_active);

-- Add composite indexes for hotel_bookings table
ALTER TABLE hotel_bookings ADD INDEX IF NOT EXISTS idx_user_dates_status (user_id, check_in, check_out, status);

-- Add composite indexes for ticket_orders table
ALTER TABLE ticket_orders ADD INDEX IF NOT EXISTS idx_user_visit_status (user_id, visit_date, status);
