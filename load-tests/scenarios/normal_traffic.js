/**
 * Normal Traffic Load Test
 * Simulates normal daily traffic patterns
 * 
 * Parameters:
 * - Virtual users: 100
 * - Ramp-up time: 5 minutes
 * - Test duration: 30 minutes
 * - Think time: 2-5 seconds
 */

import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '5m', target: 100 },  // Ramp up to 100 users
    { duration: '30m', target: 100 }, // Stay at 100 users
    { duration: '5m', target: 0 },    // Ramp down to 0
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'],  // 95% of requests < 500ms
    http_req_failed: ['rate<0.01'],    // Error rate < 1%
  },
};

const BASE_URL = 'http://localhost/mywisata';

export default function () {
  // Browse home page (20%)
  if (Math.random() < 0.2) {
    let homeRes = http.get(`${BASE_URL}/`);
    check(homeRes, {
      'home page status': (r) => r.status === 200,
      'home page response time': (r) => r.timings.duration < 500,
    });
    sleep(Math.random() * 3 + 2);
  }
  
  // View destinations (30%)
  if (Math.random() < 0.3) {
    let destRes = http.get(`${BASE_URL}/destinations`);
    check(destRes, {
      'destinations status': (r) => r.status === 200,
      'destinations response time': (r) => r.timings.duration < 500,
    });
    sleep(Math.random() * 3 + 2);
  }
  
  // Search destinations (15%)
  if (Math.random() < 0.15) {
    let searchRes = http.get(`${BASE_URL}/destinations/search?q=bali`);
    check(searchRes, {
      'search status': (r) => r.status === 200,
      'search response time': (r) => r.timings.duration < 500,
    });
    sleep(Math.random() * 3 + 2);
  }
  
  // View tour guides (15%)
  if (Math.random() < 0.15) {
    let guideRes = http.get(`${BASE_URL}/tourguides`);
    check(guideRes, {
      'tour guides status': (r) => r.status === 200,
      'tour guides response time': (r) => r.timings.duration < 500,
    });
    sleep(Math.random() * 3 + 2);
  }
  
  // Login (10%)
  if (Math.random() < 0.1) {
    let loginRes = http.post(`${BASE_URL}/auth/login`, {
      email: 'testuser@example.com',
      password: 'test123',
    });
    check(loginRes, {
      'login status': (r) => r.status === 200 || r.status === 302,
    });
    sleep(Math.random() * 3 + 2);
  }
  
  // View bookings (10%)
  if (Math.random() < 0.1) {
    let bookingRes = http.get(`${BASE_URL}/bookings`);
    check(bookingRes, {
      'bookings status': (r) => r.status === 200 || r.status === 302,
    });
    sleep(Math.random() * 3 + 2);
  }
}
