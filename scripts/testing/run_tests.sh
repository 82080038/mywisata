#!/bin/bash

# Test Execution Script for MyWisata Application
# Runs all tests and generates reports

echo "=========================================="
echo "MyWisata Application - Test Execution"
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

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    print_error "Vendor directory not found. Running composer install..."
    composer install
fi

# Run PHPUnit tests
echo "Running PHPUnit tests..."
vendor/bin/phpunit --colors=always
UNIT_TEST_RESULT=$?

if [ $UNIT_TEST_RESULT -eq 0 ]; then
    print_success "PHPUnit tests passed"
else
    print_error "PHPUnit tests failed"
fi

echo ""

# Run PHPStan static analysis
echo "Running PHPStan static analysis..."
vendor/bin/phpstan analyse app --level=8 --no-progress
PHPSTAN_RESULT=$?

if [ $PHPSTAN_RESULT -eq 0 ]; then
    print_success "PHPStan analysis passed"
else
    print_warning "PHPStan found issues (non-blocking)"
fi

echo ""

# Run PHP_CodeSniffer
echo "Running PHP_CodeSniffer..."
vendor/bin/phpcs --standard=PSR12 app --colors
PHPCS_RESULT=$?

if [ $PHPCS_RESULT -eq 0 ]; then
    print_success "Code style check passed"
else
    print_warning "Code style issues found (non-blocking)"
fi

echo ""
echo "=========================================="
echo "Test Execution Summary"
echo "=========================================="
echo "PHPUnit: $([ $UNIT_TEST_RESULT -eq 0 ] && echo 'PASSED' || echo 'FAILED')"
echo "PHPStan: $([ $PHPSTAN_RESULT -eq 0 ] && echo 'PASSED' || echo 'ISSUES FOUND')"
echo "PHP_CodeSniffer: $([ $PHPCS_RESULT -eq 0 ] && echo 'PASSED' || echo 'ISSUES FOUND')"
echo ""

# Exit with error if PHPUnit failed
if [ $UNIT_TEST_RESULT -ne 0 ]; then
    exit 1
fi

exit 0
