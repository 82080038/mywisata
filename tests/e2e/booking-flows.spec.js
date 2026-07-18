const { test, expect } = require('@playwright/test');

test.describe('Booking Flow Simulations', () => {
  test.beforeEach(async ({ page }) => {
    // Login as registered user
    await page.goto('/auth/login');
    await page.fill('input[name="email"]', 'user@test.com');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForURL('/');
  });

  test('Complete halal tourism booking flow', async ({ page }) => {
    await page.goto('/halal-tourism');
    await expect(page.locator('h1')).toContainText('Wisata Halal');
    
    // Select a package
    await page.click('.card-body a').first();
    await expect(page.locator('h1')).toBeVisible();
    
    // Fill booking form
    await page.fill('input[name="travel_date"]', '2026-08-15');
    await page.fill('input[name="number_of_travelers"]', '2');
    await page.selectOption('select[name="gender_preference"]', 'mixed');
    await page.fill('input[name="contact_person_name"]', 'Test User');
    await page.fill('input[name="contact_person_phone"]', '08123456789');
    await page.fill('input[name="contact_person_email"]', 'user@test.com');
    
    // Submit booking
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Complete culinary tourism booking flow', async ({ page }) => {
    await page.goto('/culinary-tourism/food-tours');
    await expect(page.locator('h1')).toContainText('Food Tours');
    
    // Select a tour
    await page.click('.card-body a').first();
    await expect(page.locator('h1')).toBeVisible();
    
    // Fill booking form
    await page.fill('input[name="tour_date"]', '2026-08-20');
    await page.fill('input[name="number_of_people"]', '4');
    await page.selectOption('select[name="dietary_preference"]', 'halal');
    await page.fill('input[name="contact_name"]', 'Test User');
    await page.fill('input[name="contact_phone"]', '08123456789');
    
    // Submit booking
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Complete religious tourism booking flow', async ({ page }) => {
    await page.goto('/religious-tourism');
    await expect(page.locator('h1')).toContainText('Wisata Religi');
    
    // Select a package
    await page.click('.card-body a').first();
    await expect(page.locator('h1')).toBeVisible();
    
    // Fill booking form
    await page.fill('input[name="departure_date"]', '2026-09-01');
    await page.fill('input[name="return_date"]', '2026-09-10');
    await page.fill('input[name="number_of_pilgrims"]', '3');
    await page.selectOption('select[name="room_preference"]', 'triple');
    await page.fill('input[name="group_leader_name"]', 'Test User');
    await page.fill('input[name="group_leader_phone"]', '08123456789');
    await page.fill('input[name="group_leader_email"]', 'user@test.com');
    
    // Submit booking
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Complete adventure tourism booking flow', async ({ page }) => {
    await page.goto('/adventure-tourism');
    await expect(page.locator('h1')).toContainText('Adventure Tourism');
    
    // Select an activity
    await page.click('.card-body a').first();
    await expect(page.locator('h1')).toBeVisible();
    
    // Fill booking form
    await page.fill('input[name="activity_date"]', '2026-08-25');
    await page.fill('input[name="activity_time"]', '09:00');
    await page.fill('input[name="number_of_participants"]', '2');
    await page.check('input[name="equipment_rental"]');
    await page.fill('input[name="contact_person_name"]', 'Test User');
    await page.fill('input[name="contact_person_phone"]', '08123456789');
    await page.fill('input[name="contact_person_email"]', 'user@test.com');
    
    // Submit booking
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Complete agritourism booking flow', async ({ page }) => {
    await page.goto('/agritourism');
    await expect(page.locator('h1')).toContainText('Agritourism');
    
    // Select a farm
    await page.click('.card-body a').first();
    await expect(page.locator('h1')).toBeVisible();
    
    // Book an activity
    await page.click('button').filter({ hasText: 'Booking' }).first();
    await page.fill('input[name="activity_date"]', '2026-08-10');
    await page.fill('input[name="activity_time"]', '10:00');
    await page.fill('input[name="number_of_participants"]', '5');
    await page.selectOption('select[name="group_type"]', 'family');
    
    // Submit booking
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Complete walk-in booking flow', async ({ page }) => {
    await page.goto('/walk-in-booking');
    await expect(page.locator('h1')).toContainText('Express Book');
    
    // Fill booking form
    await page.selectOption('select[name="booking_type"]', 'tour_guide');
    await page.fill('input[name="customer_name"]', 'Walk-in Customer');
    await page.fill('input[name="customer_phone"]', '08123456789');
    await page.fill('input[name="customer_email"]', 'walkin@test.com');
    await page.fill('input[name="booking_date"]', '2026-08-01');
    await page.fill('input[name="booking_time"]', '14:00');
    await page.fill('input[name="duration_hours"]', '3');
    await page.fill('input[name="number_of_people"]', '2');
    await page.selectOption('select[name="payment_method"]', 'cash');
    
    // Submit booking
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Complete split payment group creation flow', async ({ page }) => {
    await page.goto('/split-payment/create-group');
    await expect(page.locator('h1')).toContainText('Create Split Payment');
    
    // Fill group creation form
    await page.fill('input[name="total_amount"]', '1000000');
    await page.fill('input[name="number_of_participants"]', '4');
    await page.fill('input[name="description"]', 'Test split payment');
    await page.fill('input[name="due_date"]', '2026-08-15');
    
    // Submit
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
    
    // Get group code
    const groupCode = await page.locator('.alert-success').textContent();
    console.log('Group Code:', groupCode);
  });

  test('Complete green credits reward claiming flow', async ({ page }) => {
    await page.goto('/green-credits');
    await expect(page.locator('h1')).toContainText('Green Credits');
    
    // Check if user has enough credits
    const creditBalance = await page.locator('.display-4').textContent();
    console.log('Credit Balance:', creditBalance);
    
    // Try to claim a reward
    const claimButton = page.locator('button').filter({ hasText: 'Claim' }).first();
    if (await claimButton.isVisible()) {
      await claimButton.click();
      await expect(page.locator('.alert-success')).toBeVisible();
    }
  });

  test('Complete itinerary creation flow', async ({ page }) => {
    await page.goto('/itinerary');
    await expect(page.locator('h1')).toContainText('Itinerary');
    
    // Create new itinerary
    await page.click('text=Create Itinerary');
    await page.fill('input[name="name"]', 'Bali Trip 2026');
    await page.fill('input[name="start_date"]', '2026-09-01');
    await page.fill('input[name="end_date"]', '2026-09-05');
    await page.selectOption('select[name="destination_id"]', '1');
    
    // Submit
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
    
    // Add timeline event
    await page.click('text=Add Event');
    await page.fill('input[name="event_name"]', 'Visit Temple');
    await page.fill('input[name="event_date"]', '2026-09-01');
    await page.fill('input[name="event_time"]', '09:00');
    await page.fill('input[name="location"]', 'Uluwatu Temple');
    
    // Submit event
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Complete prayer room search flow', async ({ page }) => {
    await page.goto('/halal-tourism/prayer-rooms');
    await expect(page.locator('h1')).toContainText('Prayer Room');
    
    // Enable geolocation (simulate)
    await page.click('button').filter({ hasText: 'Aktifkan Lokasi' });
    
    // Wait for results
    await page.waitForTimeout(2000);
    
    // Check if prayer rooms are displayed
    const prayerRooms = page.locator('.list-group-item');
    if (await prayerRooms.count() > 0) {
      await expect(prayerRooms.first()).toBeVisible();
    }
  });

  test('Complete equipment rental booking flow', async ({ page }) => {
    await page.goto('/adventure-tourism/equipment-rentals');
    await expect(page.locator('h1')).toContainText('Equipment Rental');
    
    // Select equipment
    await page.click('.card-body a').first();
    await expect(page.locator('h1')).toBeVisible();
    
    // Fill rental form
    await page.fill('input[name="rental_date"]', '2026-08-05');
    await page.fill('input[name="return_date"]', '2026-08-07');
    await page.fill('input[name="quantity"]', '2');
    await page.selectOption('select[name="size"]', 'm');
    await page.fill('input[name="contact_name"]', 'Test User');
    await page.fill('input[name="contact_phone"]', '08123456789');
    
    // Submit booking
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });
});
