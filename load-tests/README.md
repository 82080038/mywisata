# LOAD TESTING
# Tour Guide Application

## OVERVIEW

This directory contains load testing scripts for the Tour Guide Application using k6.

## PREREQUISITES

### Install k6
```bash
# On Linux
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6

# Or download binary
wget https://github.com/grafana/k6/releases/download/v0.47.0/k6-v0.47.0-linux-amd64.tar.gz
tar -xzf k6-v0.47.0-linux-amd64.tar.gz
sudo mv k6-v0.47.0-linux-amd64/k6 /usr/local/bin/
```

## LOAD TEST SCENARIOS

### 1. Normal Traffic Load
**File:** `scenarios/normal_traffic.js`
- **Users:** 100
- **Duration:** 40 minutes (5m ramp-up, 30m steady, 5m ramp-down)
- **Target:** < 500ms response time, < 1% error rate

**Run:**
```bash
k6 run scenarios/normal_traffic.js
```

### 2. Peak Traffic Load
**File:** `scenarios/peak_traffic.js`
- **Users:** 500
- **Duration:** 80 minutes (10m ramp-up, 60m steady, 10m ramp-down)
- **Target:** < 800ms response time, < 1% error rate

**Run:**
```bash
k6 run scenarios/peak_traffic.js
```

### 3. API Load Test
**File:** `scenarios/api_load.js`
- **Users:** 300
- **Duration:** 55 minutes (5m ramp-up, 45m steady, 5m ramp-down)
- **Target:** < 200ms response time, < 0.5% error rate

**Run:**
```bash
k6 run scenarios/api_load.js
```

### 4. Stress Test
**File:** `scenarios/stress_test.js`
- **Users:** 2000 (ramp up from 500 to 2000)
- **Duration:** 90 minutes
- **Target:** Identify breaking point, < 5% error rate

**Run:**
```bash
k6 run scenarios/stress_test.js
```

## CONFIGURATION

### Modify Base URL
Edit the `BASE_URL` constant in each scenario file to match your environment:
```javascript
const BASE_URL = 'http://localhost/mywisata';
```

### Adjust Thresholds
Modify the `thresholds` object in each scenario to change performance targets:
```javascript
thresholds: {
  http_req_duration: ['p(95)<500'],
  http_req_failed: ['rate<0.01'],
}
```

## OUTPUT

### Console Output
k6 provides real-time output during test execution:
- Requests per second
- Response times (avg, min, max, p95, p99)
- Error rates
- Virtual users

### HTML Report
Generate an HTML report:
```bash
k6 run --out json=results.json scenarios/normal_traffic.js
```

### JSON Output
Export results to JSON:
```bash
k6 run --out json=results.json scenarios/normal_traffic.js
```

## BEST PRACTICES

1. **Run during off-peak hours** - Load tests consume significant resources
2. **Test on staging first** - Never run load tests on production
3. **Monitor system resources** - Watch CPU, memory, and disk I/O
4. **Start with normal load** - Begin with normal traffic scenarios
5. **Document results** - Save reports for comparison
6. **Clean up test data** - Remove test bookings and users after tests

## TROUBLESHOOTING

### Connection Refused
- Ensure the application is running
- Check the BASE_URL is correct
- Verify firewall settings

### High Error Rates
- Check application logs for errors
- Verify database connections
- Check rate limiting settings

### Slow Response Times
- Check database query performance
- Verify caching is working
- Check server resources

## MONITORING

While running load tests, monitor:
- CPU usage (should be < 80%)
- Memory usage (should be < 80%)
- Database connections (should be < 80% of pool)
- Disk I/O (should be < 80% capacity)
- Network I/O

## NEXT STEPS

1. Run normal traffic load test
2. Analyze results
3. Run peak traffic load test
4. Analyze results
5. Run stress test
6. Identify bottlenecks
7. Implement optimizations
8. Re-test to verify improvements

---

**Version:** 1.0  
**Last Updated:** 2026-07-18
