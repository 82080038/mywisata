#!/bin/bash

# Load Test Execution Script for MyWisata Application
# Runs k6 load tests with monitoring

echo "=========================================="
echo "MyWisata Application - Load Test Execution"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# Check if k6 is installed
if ! command -v k6 &> /dev/null; then
    print_error "k6 is not installed"
    echo "Please install k6 first:"
    echo "  sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69"
    echo "  echo \"deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main\" | sudo tee /etc/apt/sources.list.d/k6.list"
    echo "  sudo apt-get update"
    echo "  sudo apt-get install k6"
    exit 1
fi

print_success "k6 is installed"

# Check if application is running
echo "Checking if application is running..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost/mywisata | grep -q "200\|302"; then
    print_success "Application is running"
else
    print_warning "Application may not be running. Please start the application first."
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Select test scenario
echo ""
echo "Available load test scenarios:"
echo "1) Normal Traffic Load (100 users, 40 min)"
echo "2) Peak Traffic Load (500 users, 80 min)"
echo "3) API Load Test (300 users, 55 min)"
echo "4) Stress Test (2000 users, 90 min)"
echo ""
read -p "Select scenario (1-4): " scenario

case $scenario in
    1)
        TEST_FILE="load-tests/scenarios/normal_traffic.js"
        TEST_NAME="Normal Traffic Load"
        ;;
    2)
        TEST_FILE="load-tests/scenarios/peak_traffic.js"
        TEST_NAME="Peak Traffic Load"
        ;;
    3)
        TEST_FILE="load-tests/scenarios/api_load.js"
        TEST_NAME="API Load Test"
        ;;
    4)
        TEST_FILE="load-tests/scenarios/stress_test.js"
        TEST_NAME="Stress Test"
        ;;
    *)
        print_error "Invalid selection"
        exit 1
        ;;
esac

# Check if test file exists
if [ ! -f "$TEST_FILE" ]; then
    print_error "Test file not found: $TEST_FILE"
    exit 1
fi

# Ask for output format
echo ""
echo "Output format:"
echo "1) Console only"
echo "2) JSON output"
echo "3) Both"
echo ""
read -p "Select output format (1-3): " output_format

OUTPUT_ARGS=""
case $output_format in
    2)
        OUTPUT_ARGS="--out json=load-test-results.json"
        ;;
    3)
        OUTPUT_ARGS="--out json=load-test-results.json"
        ;;
esac

# Start monitoring in background
echo ""
print_warning "Starting system monitoring..."
mkdir -p load-tests/results
monitor_output="load-tests/results/monitoring_$(date +%Y%m%d_%H%M%S).log"

(
    while true; do
        echo "=== $(date) ===" >> "$monitor_output"
        echo "CPU: $(top -bn1 | grep 'Cpu(s)' | awk '{print $2}' | cut -d'%' -f1)% idle" >> "$monitor_output"
        echo "Memory: $(free -m | awk 'NR==2{printf "%.2f%%", $3*100/$2 }')" >> "$monitor_output"
        echo "Disk: $(df -h / | awk 'NR==2{print $5}')" >> "$monitor_output"
        echo "" >> "$monitor_output"
        sleep 10
    done
) &
MONITOR_PID=$!

# Run load test
echo ""
echo "Running: $TEST_NAME"
echo "Test file: $TEST_FILE"
echo "Monitoring PID: $MONITOR_PID"
echo ""
echo "Press Ctrl+C to stop the test"
echo ""

k6 run $OUTPUT_ARGS "$TEST_FILE"
TEST_RESULT=$?

# Stop monitoring
kill $MONITOR_PID 2>/dev/null

echo ""
echo "=========================================="
echo "Load Test Summary"
echo "=========================================="
echo "Test: $TEST_NAME"
echo "Result: $([ $TEST_RESULT -eq 0 ] && echo 'PASSED' || echo 'FAILED')"
echo "Monitoring log: $monitor_output"
if [ -f "load-test-results.json" ]; then
    echo "Results JSON: load-test-results.json"
fi
echo ""

exit $TEST_RESULT
