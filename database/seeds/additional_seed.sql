-- MyWisata Application - Additional Seed Data
-- Database: mywisata
-- This file contains additional sample data for testing
-- Created: 2026-07-15

-- ============================================
-- DESTINATION IMAGES
-- ============================================
INSERT INTO destination_images (destination_id, file_path, caption, sort_order) VALUES
(1, 'borobudur_1.jpg', 'Candi Borobudur dari kejauhan', 1),
(1, 'borobudur_2.jpg', 'Stupa Borobudur', 2),
(1, 'borobudur_3.jpg', 'Relief Borobudur', 3),
(2, 'prambanan_1.jpg', 'Candi Prambanan', 1),
(2, 'prambanan_2.jpg', 'Candi Siwa', 2),
(3, 'kuta_beach_1.jpg', 'Pantai Kuta sunset', 1),
(3, 'kuta_beach_2.jpg', 'Surfer di Pantai Kuta', 2),
(4, 'rinjani_1.jpg', 'Gunung Rinjani', 1),
(4, 'rinjani_2.jpg', 'Danau Segara Anak', 2),
(5, 'komodo_1.jpg', 'Komodo dragon', 1),
(5, 'komodo_2.jpg', 'Pulau Komodo', 2),
(6, 'museum_nasional_1.jpg', 'Museum Nasional Indonesia', 1),
(7, 'ubud_market_1.jpg', 'Pasar Seni Ubud', 1),
(8, 'istiqlal_1.jpg', 'Masjid Istiqlal', 1);

-- ============================================
-- REVIEWS
-- ============================================
INSERT INTO reviews (user_id, reviewable_type, reviewable_id, rating, comment, is_published) VALUES
(1, 'destination', 1, 5, 'Candi yang sangat megah dan bersejarah. Wajib dikunjungi!', 1),
(2, 'destination', 1, 4, 'Tempat yang bagus, tapi terlalu ramai saat weekend.', 1),
(3, 'destination', 2, 5, 'Candi Hindu yang indah dengan arsitektur yang memukau.', 1),
(1, 'destination', 3, 4, 'Pantai yang bagus untuk berselancar dan menikmati sunset.', 1),
(2, 'destination', 4, 5, 'Pendakian yang menantang tapi pemandangannya luar biasa.', 1),
(3, 'destination', 5, 5, 'Pengalaman melihat komodo di habitat aslinya sangat berkesan.', 1),
(1, 'destination', 6, 4, 'Koleksi museum yang lengkap dan edukatif.', 1),
(2, 'destination', 7, 4, 'Pasar seni yang menarik dengan berbagai kerajinan lokal.', 1),
(3, 'destination', 8, 5, 'Masjid yang sangat besar dan indah.', 1),
(1, 'hotel', 1, 4, 'Hotel yang nyaman dengan lokasi strategis di Malioboro.', 1),
(2, 'hotel', 2, 5, 'Resort pantai yang mewah dengan pelayanan excellent.', 1),
(3, 'restaurant', 1, 5, 'Gudeg terenak di Yogyakarta!', 1),
(1, 'restaurant', 2, 4, 'Makanan enak dengan view pantai yang bagus.', 1),
(2, 'guide', 1, 5, 'Pemandu wisata yang sangat berpengalaman dan ramah.', 1),
(3, 'guide', 2, 5, 'Siti sangat mengenal budaya Bali dan menjelaskan dengan detail.', 1);

-- ============================================
-- GUIDE SCHEDULES
-- ============================================
INSERT INTO guide_schedules (guide_id, available_date, start_time, end_time, is_booked, notes) VALUES
(1, '2026-07-16', '08:00:00', '17:00:00', 0, 'Available for Borobudur tour'),
(1, '2026-07-17', '08:00:00', '17:00:00', 0, 'Available for Prambanan tour'),
(1, '2026-07-18', '08:00:00', '17:00:00', 1, 'Booked'),
(2, '2026-07-16', '09:00:00', '18:00:00', 0, 'Available for Ubud tour'),
(2, '2026-07-17', '09:00:00', '18:00:00', 0, 'Available for Kuta tour'),
(2, '2026-07-18', '09:00:00', '18:00:00', 0, 'Available for temple tour'),
(3, '2026-07-16', '07:00:00', '16:00:00', 0, 'Available for adventure tour'),
(3, '2026-07-17', '07:00:00', '16:00:00', 1, 'Booked for diving'),
(3, '2026-07-18', '07:00:00', '16:00:00', 0, 'Available for hiking');

-- ============================================
-- GUIDE DOCUMENTS
-- ============================================
INSERT INTO guide_documents (guide_id, document_type, file_path) VALUES
(1, 'lisensi', 'guide1_license.pdf'),
(1, 'ktp', 'guide1_ktp.pdf'),
(1, 'sertifikat', 'guide1_certificate.pdf'),
(2, 'lisensi', 'guide2_license.pdf'),
(2, 'ktp', 'guide2_ktp.pdf'),
(2, 'sertifikat', 'guide2_certificate.pdf'),
(3, 'lisensi', 'guide3_license.pdf'),
(3, 'ktp', 'guide3_ktp.pdf'),
(3, 'sertifikat', 'guide3_certificate.pdf');

-- ============================================
-- SAMPLE BOOKINGS
-- ============================================
INSERT INTO bookings (booking_code, user_id, guide_id, booking_date, start_time, duration_hours, num_participants, destination_id, total_amount, status, notes) VALUES
('BKG-20260715-001', 1, 1, '2026-07-20', '08:00:00', 8.0, 2, 1, 1000000, 'pending', 'Borobudur full day tour'),
('BKG-20260715-002', 2, 2, '2026-07-21', '09:00:00', 6.0, 4, 7, 1200000, 'confirmed', 'Ubud cultural tour'),
('BKG-20260715-003', 3, 3, '2026-07-22', '07:00:00', 10.0, 2, 3, 2000000, 'pending', 'Rinjani hiking tour');

-- ============================================
-- SAMPLE TRANSACTIONS
-- ============================================
INSERT INTO transactions (transaction_code, user_id, type, reference_id, gross_amount, discount, net_amount, payment_method, payment_status, paid_at, notes) VALUES
('TRX-20260715-001', 1, 'booking_guide', 1, 1000000, 0, 1000000, 'transfer', 'paid', NOW(), 'Payment for Borobudur tour'),
('TRX-20260715-002', 2, 'booking_guide', 2, 1200000, 100000, 1100000, 'e_wallet', 'paid', NOW(), 'Payment for Ubud tour with discount'),
('TRX-20260715-003', 1, 'ticket', 1, 150000, 0, 150000, 'transfer', 'paid', NOW(), 'Borobudur tickets'),
('TRX-20260715-004', 2, 'hotel', 1, 1500000, 150000, 1350000, 'transfer', 'pending', NULL, 'Hotel booking'),
('TRX-20260715-005', 3, 'restaurant', 1, 100000, 0, 100000, 'cash', 'paid', NOW(), 'Restaurant order');

-- ============================================
-- TRANSACTION ITEMS
-- ============================================
INSERT INTO transaction_items (transaction_id, item_type, item_id, quantity, unit_price, subtotal) VALUES
(1, 'booking', 1, 1, 1000000, 1000000),
(2, 'booking', 2, 1, 1200000, 1200000),
(3, 'ticket', 1, 3, 50000, 150000),
(4, 'hotel_room', 1, 1, 1500000, 1500000),
(5, 'menu_item', 1, 4, 25000, 100000);

-- ============================================
-- NOTIFICATIONS
-- ============================================
INSERT INTO notifications (user_id, type, title, message, link, is_read, is_email_sent) VALUES
(1, 'booking', 'Booking Confirmed', 'Your tour booking has been confirmed.', 'bookings/detail/1', 0, 1),
(2, 'payment', 'Payment Received', 'Your payment has been received successfully.', 'transactions/detail/2', 0, 1),
(3, 'event', 'New Event Available', 'Check out the new Bali Arts Festival!', 'events/detail/1', 0, 0),
(1, 'reminder', 'Upcoming Tour', 'Your Borobudur tour is tomorrow!', 'bookings/detail/1', 0, 0);

-- ============================================
-- AUDIT LOGS
-- ============================================
INSERT INTO audit_logs (user_id, action, module, description, ip_address, user_agent) VALUES
(1, 'login', 'auth', 'User logged in', '127.0.0.1', 'Mozilla/5.0'),
(1, 'create', 'booking', 'Created new booking', '127.0.0.1', 'Mozilla/5.0'),
(2, 'update', 'profile', 'Updated user profile', '127.0.0.1', 'Mozilla/5.0'),
(1, 'delete', 'favorite', 'Removed destination from favorites', '127.0.0.1', 'Mozilla/5.0');

-- ============================================
-- Additional Seed Data Complete
-- ============================================
-- Added:
-- - 14 destination images
-- - 15 reviews
-- - 9 guide schedules
-- - 9 guide documents
-- - 3 bookings
-- - 5 transactions
-- - 5 transaction items
-- - 4 notifications
-- - 4 audit logs
