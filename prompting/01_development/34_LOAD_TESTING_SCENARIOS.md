# LOAD TESTING SCENARIOS
# Module 34 - Comprehensive Load Testing for Tour Guide Application

## OVERVIEW

This prompting template guides the AI through implementing comprehensive load testing for the Tour Guide Application to ensure performance under high traffic and identify bottlenecks.

## LOAD TESTING TOOLS

### Recommended Tools
- **Apache JMeter** - Load and performance testing
- **Gatling** - Load testing framework (alternative)
- **Locust** - Python-based load testing (alternative)
- **k6** - Modern load testing tool (alternative)
- **Artillery** - Node.js load testing (alternative)

## PERFORMANCE TARGETS

### Response Time Targets
- **Static pages**: < 200ms
- **Dynamic pages**: < 500ms
- **API endpoints**: < 200ms
- **Database queries**: < 100ms
- **File uploads**: < 2s

### Throughput Targets
- **Concurrent users**: 1000+
- **Requests per second**: 100+
- **Peak traffic handling**: 10x normal load

### Resource Limits
- **CPU usage**: < 80% under load
- **Memory usage**: < 80% under load
- **Database connections**: < 80% of pool
- **Disk I/O**: < 80% capacity

## LOAD TESTING SCENARIOS

### Scenario 1: Normal Traffic Load
**Description**: Simulate normal daily traffic patterns

**Parameters**:
- Virtual users: 100
- Ramp-up time: 5 minutes
- Test duration: 30 minutes
- Think time: 2-5 seconds

**User Actions**:
1. Browse home page (20%)
2. View destinations (30%)
3. Search destinations (15%)
4. View tour guides (15%)
5. Login (10%)
6. View bookings (10%)

**Expected Results**:
- All response times < 500ms
- No errors
- CPU < 60%
- Memory < 60%

### Scenario 2: Peak Traffic Load
**Description**: Simulate peak traffic during holidays/events

**Parameters**:
- Virtual users: 500
- Ramp-up time: 10 minutes
- Test duration: 60 minutes
- Think time: 1-3 seconds

**User Actions**:
1. Browse home page (15%)
2. View destinations (25%)
3. Search destinations (20%)
4. View tour guides (20%)
5. Login (10%)
6. Create booking (5%)
7. Make payment (5%)

**Expected Results**:
- All response times < 800ms
- Error rate < 1%
- CPU < 80%
- Memory < 80%

### Scenario 3: Stress Test
**Description**: Push system beyond normal limits to find breaking point

**Parameters**:
- Virtual users: 1000-2000
- Ramp-up time: 15 minutes
- Test duration: 90 minutes
- Think time: 0-2 seconds

**User Actions**:
1. Browse home page (10%)
2. View destinations (20%)
3. Search destinations (25%)
4. View tour guides (20%)
5. Login (10%)
6. Create booking (10%)
7. Make payment (5%)

**Expected Results**:
- Identify breaking point
- Document failure modes
- Measure degradation curve
- Recovery time after load

### Scenario 4: Booking Spike
**Description**: Simulate sudden spike in booking requests

**Parameters**:
- Virtual users: 200
- Ramp-up time: 1 minute (rapid)
- Test duration: 20 minutes
- Think time: 1-2 seconds

**User Actions**:
1. Login (20%)
2. Search tour guides (30%)
3. View tour guide details (20%)
4. Create booking (20%)
5. Make payment (10%)

**Expected Results**:
- Booking system handles spike
- No double bookings
- Payment processing stable
- Database transactions consistent

### Scenario 5: API Load Test
**Description**: Test API endpoints under load

**Parameters**:
- Virtual users: 300
- Ramp-up time: 5 minutes
- Test duration: 45 minutes
- Request rate: 50 requests/second

**API Endpoints**:
1. GET /api/destinations (30%)
2. GET /api/tourguides (25%)
3. GET /api/destinations/{id} (20%)
4. POST /api/bookings (15%)
5. GET /api/bookings/{id} (10%)

**Expected Results**:
- All API responses < 200ms
- Error rate < 0.5%
- Rate limiting works
- No data corruption

### Scenario 6: Database Load Test
**Description**: Test database performance under load

**Parameters**:
- Virtual users: 400
- Ramp-up time: 10 minutes
- Test duration: 60 minutes
- Focus: Database-intensive operations

**Database Operations**:
1. Read operations (50%)
2. Write operations (30%)
3. Complex queries (15%)
4. Join operations (5%)

**Expected Results**:
- Query times < 100ms
- No deadlocks
- Connection pool stable
- No connection exhaustion

### Scenario 7: File Upload Load Test
**Description**: Test file upload performance under load

**Parameters**:
- Virtual users: 50
- Ramp-up time: 5 minutes
- Test duration: 30 minutes
- File sizes: 1-5MB

**Upload Operations**:
1. Profile picture upload (40%)
2. Destination image upload (30%)
3. Document upload (20%)
4. Audio guide upload (10%)

**Expected Results**:
- Uploads complete successfully
- No file corruption
- Disk space managed
- Upload queue stable

### Scenario 8: Concurrent User Sessions
**Description**: Test with many concurrent logged-in users

**Parameters**:
- Virtual users: 300
- Ramp-up time: 10 minutes
- Test duration: 60 minutes
- All users logged in

**User Actions**:
1. View dashboard (25%)
2. Manage bookings (25%)
3. View profile (20%)
4. Update settings (15%)
5. Logout/login (15%)

**Expected Results**:
- Session management stable
- No session conflicts
- Memory usage stable
- No session fixation

## JMETER TEST PLANS

### Test Plan Structure
```xml
<?xml version="1.0" encoding="UTF-8"?>
<jmeterTestPlan version="1.2">
  <hashTree>
    <TestPlan guiclass="TestPlanGui" testclass="TestPlan">
      <stringProp name="TestPlan.comments">Tour Guide Application Load Test</stringProp>
      <stringProp name="TestPlan.user_define_classpath"></stringProp>
      <boolProp name="TestPlan.functional_mode">false</boolProp>
      <boolProp name="TestPlan.serialize_threadgroups">false</boolProp>
    </TestPlan>
    <hashTree>
      <!-- Thread Groups -->
      <!-- HTTP Requests -->
      <!-- Assertions -->
      <!-- Listeners -->
    </hashTree>
  </hashTree>
</jmeterTestPlan>
```

### Thread Group Example
```xml
<ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup">
  <stringProp name="ThreadGroup.num_threads">100</stringProp>
  <stringProp name="ThreadGroup.ramp_time">300</stringProp>
  <stringProp name="ThreadGroup.duration">1800</stringProp>
  <boolProp name="ThreadGroup.scheduler">true</boolProp>
</ThreadGroup>
```

### HTTP Request Example
```xml
<HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy">
  <stringProp name="HTTPSampler.domain">localhost</stringProp>
  <stringProp name="HTTPSampler.port">80</stringProp>
  <stringProp name="HTTPSampler.path">/mywisata/destinations</stringProp>
  <stringProp name="HTTPSampler.method">GET</stringProp>
</HTTPSamplerProxy>
```

## K6 TEST SCRIPTS

### Basic Load Test Script
```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '5m', target: 100 },  // Ramp up to 100 users
    { duration: '30m', target: 100 },  // Stay at 100 users
    { duration: '5m', target: 0 },    // Ramp down to 0
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'],  // 95% of requests < 500ms
    http_req_failed: ['rate<0.01'],    // Error rate < 1%
  },
};

export default function () {
  // Browse home page
  let homeRes = http.get('http://localhost/mywisata');
  check(homeRes, {
    'home page status': (r) => r.status === 200,
    'home page response time': (r) => r.timings.duration < 500,
  });
  
  sleep(Math.random() * 3 + 2); // Random think time 2-5s
  
  // View destinations
  let destRes = http.get('http://localhost/mywisata/destinations');
  check(destRes, {
    'destinations status': (r) => r.status === 200,
    'destinations response time': (r) => r.timings.duration < 500,
  });
  
  sleep(Math.random() * 3 + 2);
}
```

### Booking Flow Script
```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '2m', target: 50 },
    { duration: '20m', target: 50 },
    { duration: '2m', target: 0 },
  ],
};

export default function () {
  // Login
  let loginRes = http.post('http://localhost/mywisata/auth/login', {
    email: 'testuser@example.com',
    password: 'test123',
  });
  
  check(loginRes, {
    'login successful': (r) => r.status === 200,
  });
  
  sleep(2);
  
  // Create booking
  let bookingRes = http.post('http://localhost/mywisata/bookings/create', {
    tour_guide_id: 1,
    date: '2026-07-20',
    duration: 4,
    notes: 'Load test booking',
  });
  
  check(bookingRes, {
    'booking created': (r) => r.status === 200,
    'booking response time': (r) => r.timings.duration < 1000,
  });
  
  sleep(3);
}
```

## MONITORING DURING TESTS

### Metrics to Monitor
1. **Response Times**
   - Average response time
   - 95th percentile
   - 99th percentile
   - Min/Max response times

2. **Throughput**
   - Requests per second
   - Transactions per second
   - Bytes per second

3. **Error Rates**
   - HTTP error codes
   - Failed transactions
   - Timeout errors

4. **Resource Usage**
   - CPU utilization
   - Memory usage
   - Disk I/O
   - Network I/O

5. **Database Metrics**
   - Query execution time
   - Connection pool usage
   - Lock wait time
   - Deadlocks

6. **Application Metrics**
   - Active sessions
   - Cache hit ratio
   - Queue lengths
   - Thread pool usage

### Monitoring Tools
- **Grafana** - Visualization
- **Prometheus** - Metrics collection
- **New Relic** - APM (optional)
- **Datadog** - Monitoring (optional)

## TEST EXECUTION

### Pre-Test Checklist
- [ ] Database backed up
- [ ] Test environment ready
- [ ] Monitoring tools configured
- [ ] Baseline metrics recorded
- [ ] Test data prepared
- [ ] Alerts configured

### Test Execution Steps
1. Start monitoring tools
2. Record baseline metrics
3. Start load test
4. Monitor during test
5. Collect results
6. Stop monitoring
7. Analyze results
8. Generate report

### Post-Test Actions
- [ ] Restore database if needed
- [ ] Clean up test data
- [ ] Review results
- [ ] Document findings
- [ ] Plan optimizations

## PERFORMANCE BOTTLENECKS

### Common Bottlenecks
1. **Database**
   - Slow queries
   - Missing indexes
   - N+1 query problem
   - Connection pool exhaustion

2. **Application**
   - Inefficient algorithms
   - Memory leaks
   - Synchronous operations
   - Lack of caching

3. **Network**
   - Bandwidth limitations
   - High latency
   - Network congestion
   - DNS resolution

4. **Server**
   - CPU limitations
   - Memory constraints
   - Disk I/O bottlenecks
   - Thread pool limits

### Optimization Strategies
1. **Database Optimization**
   - Add indexes
   - Optimize queries
   - Use caching
   - Implement read replicas

2. **Application Optimization**
   - Implement caching
   - Use async operations
   - Optimize algorithms
   - Reduce memory usage

3. **Infrastructure Optimization**
   - Load balancing
   - CDN implementation
   - Server scaling
   - Network optimization

## DELIVERABLES

1. Load test plans (JMeter/K6 scripts)
2. Test execution reports
3. Performance analysis reports
4. Bottleneck identification
5. Optimization recommendations
6. Baseline metrics documentation
7. Monitoring dashboards
8. Load testing documentation

## ACCEPTANCE CRITERIA

- All load test scenarios executed
- Performance targets met for normal load
- System degrades gracefully under stress
- Bottlenecks identified and documented
- Optimization recommendations provided
- Monitoring configured for production
- Test documentation complete

## NOTES

- Run load tests during off-peak hours
- Always test on staging environment first
- Never run load tests on production
- Monitor system health during tests
- Have rollback plan ready
- Document all findings
- Regular load testing recommended

---

**Module:** 34_LOAD_TESTING_SCENARIOS  
**Priority:** MEDIUM  
**Status:** READY FOR DEVELOPMENT  
**Last Updated:** 2026-07-18
