/**
 * API Load Test
 * Tests API endpoints under load
 * 
 * Parameters:
 * - Virtual users: 300
 * - Ramp-up time: 5 minutes
 * - Test duration: 45 minutes
 * - Request rate: 50 requests/second
 */

import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '5m', target: 300 },  // Ramp up to 300 users
    { duration: '45m', target: 300 }, // Stay at 300 users
    { duration: '5m', target: 0 },    // Ramp down to 0
  ],
  thresholds: {
    http_req_duration: ['p(95)<200'],  // 95% of requests < 200ms
    http_req_failed: ['rate<0.005'],    // Error rate < 0.5%
  },
};

const BASE_URL = 'http://localhost/mywisata/api';

export default function () {
  // GET /api/destinations (30%)
  if (Math.random() < 0.30) {
    let destRes = http.get(`${BASE_URL}/destinations`);
    check(destRes, {
      'destinations API status': (r) => r.status === 200,
      'destinations API response time': (r) => r.timings.duration < 200,
      'destinations API has data': (r) => JSON.parse(r.body).destinations !== undefined,
    });
    sleep(Math.random() * 2 + 1);
  }
  
  // GET /api/tourguides (25%)
  if (Math.random() < 0.25) {
    let guideRes = http.get(`${BASE_URL}/tourguides`);
    check(guideRes, {
      'tour guides API status': (r) => r.status === 200,
      'tour guides API response time': (r) => r.timings.duration < 200,
      'tour guides API has data': (r) => JSON.parse(r.body).tourguides !== undefined,
    });
    sleep(Math.random() * 2 + 1);
  }
  
  // GET /api/destinations/{id} (20%)
  if (Math.random() < 0.20) {
    let destDetailRes = http.get(`${BASE_URL}/destinations/1`);
    check(destDetailRes, {
      'destination detail API status': (r) => r.status === 200,
      'destination detail API response time': (r) => r.timings.duration < 200,
    });
    sleep(Math.random() * 2 + 1);
  }
  
  // POST /api/bookings (15%)
  if (Math.random() < 0.15) {
    let bookingRes = http.post(`${BASE_URL}/bookings`, JSON.stringify({
      tour_guide_id: 1,
      date: '2026-07-20',
      duration: 4,
      notes: 'Load test booking',
    }), {
      headers: { 'Content-Type': 'application/json' },
    });
    check(bookingRes, {
      'booking API status': (r) => r.status === 200 || r.status === 201,
      'booking API response time': (r) => r.timings.duration < 300,
    });
    sleep(Math.random() * 2 + 1);
  }
  
  // GET /api/bookings/{id} (10%)
  if (Math.random() < 0.10) {
    let bookingDetailRes = http.get(`${BASE_URL}/bookings/1`);
    check(bookingDetailRes, {
      'booking detail API status': (r) => r.status === 200,
      'booking detail API response time': (r) => r.timings.duration < 200,
    });
    sleep(Math.random() * 2 + 1);
  }
}
