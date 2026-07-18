/**
 * Peak Traffic Load Test
 * Simulates peak traffic during holidays/events
 * 
 * Parameters:
 * - Virtual users: 500
 * - Ramp-up time: 10 minutes
 * - Test duration: 60 minutes
 * - Think time: 1-3 seconds
 */

import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '10m', target: 500 }, // Ramp up to 500 users
    { duration: '60m', target: 500 }, // Stay at 500 users
    { duration: '10m', target: 0 },   // Ramp down to 0
  ],
  thresholds: {
    http_req_duration: ['p(95)<800'],  // 95% of requests < 800ms
    http_req_failed: ['rate<0.01'],    // Error rate < 1%
  },
};

const BASE_URL = 'http://localhost/mywisata';

export default function () {
  // Browse home page (15%)
  if (Math.random() < 0.15) {
    let homeRes = http.get(`${BASE_URL}/`);
    check(homeRes, {
      'home page status': (r) => r.status === 200,
      'home page response time': (r) => r.timings.duration < 800,
    });
    sleep(Math.random() * 2 + 1);
  }
  
  // View destinations (25%)
  if (Math.random() < 0.25) {
    let destRes = http.get(`${BASE_URL}/destinations`);
    check(destRes, {
      'destinations status': (r) => r.status === 200,
      'destinations response time': (r) => r.timings.duration < 800,
    });
    sleep(Math.random() * 2 + 1);
  }
  
  // Search destinations (20%)
  if (Math.random() < 0.20) {
    let searchRes = http.get(`${BASE_URL}/destinations/search?q=bali`);
    check(searchRes, {
      'search status': (r) => r.status === 200,
      'search response time': (r) => r.timings.duration < 800,
    });
    sleep(Math.random() * 2 + 1);
  }
  
  // View tour guides (20%)
  if (Math.random() < 0.20) {
    let guideRes = http.get(`${BASE_URL}/tourguides`);
    check(guideRes, {
      'tour guides status': (r) => r.status === 200,
      'tour guides response time': (r) => r.timings.duration < 800,
    });
    sleep(Math.random() * 2 + 1);
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
    sleep(Math.random() * 2 + 1);
  }
  
  // Create booking (5%)
  if (Math.random() < 0.05) {
    let bookingRes = http.post(`${BASE_URL}/bookings/create`, {
      tour_guide_id: 1,
      date: '2026-07-20',
      duration: 4,
      notes: 'Load test booking',
    });
    check(bookingRes, {
      'booking status': (r) => r.status === 200 || r.status === 302,
    });
    sleep(Math.random() * 2 + 1);
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
    sleep(Math.random() * 2 + 1);
  }
}
