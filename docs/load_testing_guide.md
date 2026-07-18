# LOAD TESTING GUIDE
# Tour Guide Application

## OVERVIEW

This guide provides comprehensive instructions for executing load tests for the Tour Guide Application using k6.

## PREREQUISITES

### Install k6
```bash
# On Linux
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6

# Verify installation
k6 version
```

### Application Setup
- Ensure the application is running on http://localhost/mywisata
- Database should be accessible
- Test data should be available

## LOAD TEST SCENARIOS

### 1. Normal Traffic Load
**Purpose:** Simulate normal daily traffic patterns

**Parameters:**
- Virtual users: 100
- Ramp-up time: 5 minutes
- Test duration: 30 minutes
- Think time: 2-5 seconds

**Target Metrics:**
- Response time: < 500ms (p95)
- Error rate: < 1%

**Execution:**
```bash
k6 run load-tests/scenarios/normal_traffic.js
```

### 2. Peak Traffic Load
**Purpose:** Simulate peak traffic during holidays/events

**Parameters:**
- Virtual users: 500
- Ramp-up time: 10 minutes
- Test duration: 60 minutes
- Think time: 1-3 seconds

**Target Metrics:**
- Response time: < 800ms (p95)
- Error rate: < 1%

**Execution:**
```bash
k6 run load-tests/scenarios/peak_traffic.js
```

### 3. API Load Test
**Purpose:** Test API endpoints under load

**Parameters:**
- Virtual users: 300
- Ramp-up time: 5 minutes
- Test duration: 45 minutes
- Request rate: 50 requests/second

**Target Metrics:**
- Response time: < 200ms (p95)
- Error rate: < 0.5%

**Execution:**
```bash
k6 run load-tests/scenarios/api_load.js
```

### 4. Stress Test
**Purpose:** Push system beyond normal limits to find breaking point

**Parameters:**
- Virtual users: 1000-2000
- Ramp-up time: 15 minutes
- Test duration: 90 minutes
- Think time: 0-2 seconds

**Target Metrics:**
- Identify breaking point
- Document failure modes
- Error rate: < 5%

**Execution:**
```bash
k6 run load-tests/scenarios/stress_test.js
```

## USING THE EXECUTION SCRIPT

A convenience script is provided to simplify load test execution:

```bash
./scripts/run_load_test.sh
```

The script will:
1. Check if k6 is installed
2. Check if the application is running
3. Prompt for test scenario selection
4. Start system monitoring
5. Execute the load test
6. Save results and monitoring logs

## MONITORING DURING TESTS

### System Metrics to Monitor
- **CPU Usage:** Should remain < 80%
- **Memory Usage:** Should remain < 80%
- **Disk I/O:** Should remain < 80% capacity
- **Network I/O:** Monitor bandwidth usage
- **Database Connections:** Should remain < 80% of pool

### Monitoring Tools
- **top/htop:** CPU and memory monitoring
- **iostat:** Disk I/O monitoring
- **netstat:** Network connections
- **MySQL:** `SHOW PROCESSLIST` for database queries

### Manual Monitoring
```bash
# Terminal 1: System monitoring
watch -n 1 'top -bn1 | head -20'

# Terminal 2: Database monitoring
watch -n 1 'mysql -u root -proot -e "SHOW PROCESSLIST"'

# Terminal 3: Run load test
k6 run load-tests/scenarios/normal_traffic.js
```

## INTERPRETING RESULTS

### k6 Output
k6 provides real-time output including:
- **http_reqs:** Total HTTP requests
- **http_req_duration:** Response time statistics
- **http_req_failed:** Failed requests
- **vus:** Active virtual users

### Key Metrics
- **p(95):** 95th percentile response time
- **p(99):** 99th percentile response time
- **avg:** Average response time
- **min/max:** Minimum and maximum response times
- **rps:** Requests per second

### Success Criteria
- All thresholds met
- Error rate within acceptable limits
- Response times within targets
- System resources within limits

## TROUBLESHOOTING

### Connection Refused
**Problem:** Cannot connect to application
**Solution:**
- Ensure application is running
- Check BASE_URL in test scripts
- Verify firewall settings

### High Error Rates
**Problem:** Many requests failing
**Solution:**
- Check application logs for errors
- Verify database connections
- Check rate limiting settings
- Review server resources

### Slow Response Times
**Problem:** Response times exceed targets
**Solution:**
- Check database query performance
- Verify caching is working
- Check server resources
- Review code for bottlenecks

### Script Errors
**Problem:** k6 script fails to run
**Solution:**
- Validate JavaScript syntax
- Check file paths
- Verify k6 version compatibility

## BEST PRACTICES

### Before Testing
1. **Backup database** - Always backup before load tests
2. **Use staging environment** - Never test on production
3. **Monitor baseline** - Record baseline metrics first
4. **Prepare test data** - Ensure sufficient test data exists
5. **Configure alerts** - Set up monitoring alerts

### During Testing
1. **Monitor resources** - Watch CPU, memory, disk, network
2. **Check logs** - Monitor application logs for errors
3. **Document findings** - Record observations during test
4. **Have rollback plan** - Be prepared to stop if issues arise

### After Testing
1. **Analyze results** - Review all metrics and logs
2. **Clean up data** - Remove test bookings and users
3. **Document findings** - Create comprehensive report
4. **Plan optimizations** - Identify areas for improvement
5. **Re-test** - Verify optimizations work

## PERFORMANCE OPTIMIZATION

### Database Optimization
- Add indexes to frequently queried columns
- Optimize slow queries
- Use query caching
- Implement read replicas for scaling

### Application Optimization
- Implement caching (Redis)
- Use CDN for static assets
- Optimize algorithms
- Reduce memory usage

### Infrastructure Optimization
- Implement load balancing
- Scale server resources
- Use CDN for content delivery
- Optimize network configuration

## REPORTING

### Report Template
1. **Test Summary**
   - Test scenario
   - Date and time
   - Duration
   - Virtual users

2. **Results**
   - Response times (avg, p95, p99)
   - Throughput (rps)
   - Error rate
   - Resource usage

3. **Analysis**
   - Bottlenecks identified
   - Comparison with targets
   - Trends observed

4. **Recommendations**
   - Optimization suggestions
   - Priority items
   - Estimated effort

## RESOURCES

- [k6 Documentation](https://k6.io/docs/)
- [k6 Examples](https://k6.io/docs/examples/)
- [Load Testing Best Practices](https://k6.io/docs/test-types/load-testing/)
- [Performance Testing Guide](https://k6.io/docs/test-types/performance-testing/)

---

**Version:** 1.0  
**Last Updated:** 2026-07-18  
**Status:** Active
