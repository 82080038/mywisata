/**
 * Stress Test
 * Push system beyond normal limits to find breaking point
 * 
 * Parameters:
 * - Virtual users: 1000-2000
 * - Ramp-up time: 15 minutes
 * - Test duration: 90 minutes
 * - Think time: 0-2 seconds
 */

import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '5m', target: 500 },   // Ramp up to 500 users
    { duration: '5m', target: 1000 },  // Ramp up to 1000 users
    { duration: '5m', target: 1500 },  // Ramp up to 1500 users
    { duration: '5m', target: 2000 },  // Ramp up to 2000 users
    { duration: '60m', target: 2000 }, // Stay at 2000 users
    { duration: '10m', target: 0 },    // Ramp down to 0
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'], // 95% of requests < 2000ms (relaxed for stress test)
    http_req_failed: ['rate<0.05'],     // Error rate < 5% (relaxed for stress test)
  },
};

const BASE_URL = 'http://localhost/mywisata';

export default function () {
  // Browse home page (10%)
  if (Math.random() < 0.10) {
    let homeRes = http.get(`${BASE_URL}/`);
    check(homeRes, {
      'home page status': (r) => r.status === 200,
    });
    sleep(Math.random() * 2);
  }
  
  // View destinations (20%)
  if (Math.random() < 0.20) {
    let destRes = http.get(`${BASE_URL}/destinations`);
    check(destRes, {
      'destinations status': (r) => r.status === 200,
    });
    sleep(Math.random() * 2);
  }
  
  // Search destinations (25%)
  if (Math.random() < 0.25) {
    let searchRes = http.get(`${BASE_URL}/destinations/search?q=bali`);
    check(searchRes, {
      'search status': (r) => r.status === 200,
    });
    sleep(Math.random() * 2);
  }
  
  // View tour guides (20%)
  if (Math.random() < 0.20) {
    let guideRes = http.get(`${BASE_URL}/tourguides`);
    check(guideRes, {
      'tour guides status': (r) => r.status === 200,
    });
    sleep(Math.random() * 2);
  }
  
  // Login (10%)
  if (Math.random() < 0.10) {
    let loginRes = http.post(`${BASE_URL}/auth/login`, {
      email: 'testuser@example.com',
      password: 'test123',
    });
    check(loginRes, {
      'login status': (r) => r.status === 200 || r.status === 302,
    });
    sleep(Math.random() * 2);
  }
  
  // Create booking (10%)
  if (Math.random() < 0.10) {
    let bookingRes = http.post(`${BASE_URL}/bookings/create`, {
      tour_guide_id: 1,
      date: '2026-07-20',
      duration: 4,
      notes: 'Stress test booking',
    });
    check(bookingRes, {
      'booking status': (r) => r.status === 200 || r.status === 302,
    });
    sleep(Math.random() * 2);
  }
  
  // Make payment (5%)
  if (Math.random() < 0.05) {
    let paymentRes = http.post(`${BASE_URL}/payments/process`, {
      booking_id: 1,
      amount: 500000,
      payment_method: 'midtrans',
    });
    check(paymentRes, {
      'payment status': (r) => r.status === 200 || r.status === 302,
    });
    sleep(Math.random() * 2);
  }
}
