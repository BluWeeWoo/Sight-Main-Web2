# SIGHT Web Backend - Quality Assurance & Testing Checklist
**Project:** SIGHT (Sight-focused Integrated Guidance for Healthy Technology use)  
**Branch:** API_Integration  
**Date:** April 7, 2026  
**Scope:** Steps 1-4 Implementation (UDS Refactor, Delta Sync, Batch Metrics, Guardian Dashboard)

---

## Phase 1: Code Quality & Architecture Validation

### 1.1 Service Layer Pattern Compliance (Step 1)
- [ ] **Verify Controllers are Thin:**
  - `MobileApiController`: Contains ONLY validation + service delegation + JSON response
  - `GuardianApiController`: Contains ONLY validation + service delegation + JSON response
  - No business logic, database queries, or calculations in controllers
  - Run: `grep -n "Model::" app/Http/Controllers/Api/*.php` (should return 0 results)
  - Run: `grep -n "->create\|->update\|->delete\|->where" app/Http/Controllers/Api/*.php` (should return 0 results)

- [ ] **Verify Service Classes Contain All Logic:**
  - `MetricsService`: 12 public methods (loginChild, syncMetrics, ingestBatchMetrics, syncPet, syncSessionLimits, syncCalibration, registerFcmToken, devicePing, getConfig)
  - `RuleEngineService`: 6 public methods (registerGuardian, addChild, updateChildLimits, linkDoctor, deleteChild, + private generateUniqueLoginCode)
  - All methods follow `response()` pattern for standardized API envelopes
  - All methods include proper error handling with appropriate HTTP status codes

- [ ] **Response Envelope Standard:**
  - All service methods return array with structure: `['http_code' => int, 'body' => ['status' => string, 'message' => string, 'data' => array, 'errors' => null|array]]`
  - Run: `grep -n "private function response" app/Services/*.php` (verify both services have this)
  - Spot-check 3 service methods to confirm response structure

### 1.2 Dependencies & Imports
- [ ] **MetricsService imports:**
  - SessionLimits ✓
  - ChildProfile ✓
  - EyeHealthMetrics ✓
  - User ✓
  - VirtualPet ✓
  - Carbon ✓

- [ ] **RuleEngineService imports:**
  - DB Facade ✓
  - Hash Facade ✓
  - All required models ✓

- [ ] **Controller imports:**
  - Auth Facade (not auth helper) ✓
  - MetricsService ✓
  - RuleEngineService ✓

- [ ] **No missing use statements:**
  - Run: `php -l app/Services/*.php app/Http/Controllers/Api/*.php` (all pass)
  - Run: `php artisan tinker` → test basic class instantiation

---

## Phase 2: API Endpoint Testing

### 2.1 Mobile API Endpoints (MobileApiController)

#### 2.1.1 POST /api/mobile/child/login
**Test Case 1: Valid Login**
- Request:
  ```json
  {
    "login_code": "123456",
    "device_id": "device_abc123"
  }
  ```
- Expected: HTTP 200, `status: "success"`, child data in response
- Verify: Child's device_id is updated in DB

**Test Case 2: Invalid Login Code**
- Request: `"login_code": "999999"`
- Expected: HTTP 401, `status: "error"`, `message: "Invalid login code"`

**Test Case 3: Missing Login Code**
- Request: No login_code field
- Expected: HTTP 422 (validation error)

#### 2.1.2 POST /api/mobile/child/{child_id}/sync/metrics
**Test Case 1: Single Metric Sync**
- Request:
  ```json
  {
    "metrics": [
      {
        "avg_blink_rate": 18.5,
        "avg_distance": 35.0,
        "strain_events": 2,
        "screen_time_minutes": 15,
        "timestamp": "2026-04-07 14:30:00"
      }
    ]
  }
  ```
- Expected: HTTP 200, `status: "success"`, `inserted_records: 1`
- Verify: Child's last_sync is updated to now()

**Test Case 2: Multiple Metrics Batch**
- Request: 5 metrics with different timestamps
- Expected: HTTP 200, `inserted_records: 5`

**Test Case 3: Invalid Timestamp Format**
- Request: `"timestamp": "2026-04-07"` (missing time)
- Expected: HTTP 422 validation error

**Test Case 4: Unauthorized Access**
- Request: Auth as different child's user
- Expected: HTTP 403, ` message: "Unauthorized"`

#### 2.1.3 POST /api/mobile/child/{child_id}/sync/metrics/batch (Step 3)
**Test Case 1: Batch Ingestion with Deduplication**
- Request: 10 metrics with 2 duplicate timestamps (already in DB)
- Expected: HTTP 200, `inserted_records: 8`, `skipped_duplicates: 2`, `total_processed: 10`
- Verify: DB contains only 8 new records

**Test Case 2: Empty Batch**
- Request: `"metrics": []`
- Expected: HTTP 400, `message: "Metrics batch is empty"`

**Test Case 3: Bulk Insert Performance**
- Request: 100 metrics in batch
- Expected: HTTP 200, `inserted_records: 100`, response time < 500ms
- Verify: All 100 records in DB with correct values

**Test Case 4: Deduplication Edge Case**
- Request: Same timestamp twice in single batch
- Expected: First record inserted, second skipped
- Verify: Only 1 record in DB for that timestamp

#### 2.1.4 PUT /api/mobile/child/{child_id}/sync/pet (Step 2 - Delta Sync)
**Test Case 1: Device Data Newer (Accept Update)**
- Setup: Server has pet with `updated_at: "2026-04-07 10:00:00"`
- Request:
  ```json
  {
    "xp_points": 500,
    "currency": 100,
    "pet_state": "Healthy",
    "device_timestamp": "2026-04-07 12:00:00"
  }
  ```
- Expected: HTTP 200, `status: "success"`, `synced: true`, pet data in response
- Verify: Pet updated in DB with `updated_at: now()` (not device_timestamp)

**Test Case 2: Server Data Newer (Reject Update - Conflict)**
- Setup: Server has pet with `updated_at: "2026-04-07 15:00:00"`
- Request: device_timestamp: "2026-04-07 12:00:00" (older)
- Expected: HTTP 200, `status: "success"`, `synced: false`
- Response includes: Current server pet state (all fields)
- Verify: Pet NOT updated in DB

**Test Case 3: Equal Timestamps (Accept - Greater-than-or-equal)**
- Setup: Server updated_at = device_timestamp
- Expected: Accept and update

**Test Case 4: First Sync (No Server Data Yet)**
- Setup: Pet exists but updated_at is null
- Request: Any device_timestamp > 0
- Expected: Accept and update

#### 2.1.5 PUT /api/mobile/child/{child_id}/sync/limits (Step 2 - New)
**Test Case 1: Device Data Newer (Accept Update)**
- Setup: Session limits with `updated_at: "2026-04-07 09:00:00"`
- Request:
  ```json
  {
    "daily_limit_minutes": 90,
    "mode": "Strict",
    "harmful_distance_threshold": 25,
    "critical_distance_threshold": 8,
    "auto_enforce_breaks": false,
    "device_timestamp": "2026-04-07 14:00:00"
  }
  ```
- Expected: HTTP 200, `synced: true`, limits updated in response
- Verify: DB updated with new values

**Test Case 2: Guardian Updated Rules on Web (Server Newer)**
- Setup: Guardian updates limits via web (updated_at = now)
- Device tries to sync with older timestamp
- Expected: HTTP 200, `synced: false`
- Response includes: Current server limits (new guardian-set values)
- Verify: Mobile app receives authoritative ruleset

**Test Case 3: Partial Update**
- Request: Only `daily_limit_minutes` and `device_timestamp` (other fields omitted)
- Expected: Only changed field updated, others preserved

**Test Case 4: Invalid Mode Value**
- Request: `"mode": "Invalid"`
- Expected: HTTP 422 validation error

#### 2.1.6 POST /api/mobile/child/{child_id}/sync/calibration
**Test Case 1: Valid Calibration Sync**
- Request:
  ```json
  {
    "calibration_baseline": "{\"mesh_points\": [...], \"reference_distance\": 35}"
  }
  ```
- Expected: HTTP 200, `status: "success"`
- Verify: Calibration stored in DB as JSON

**Test Case 2: Invalid JSON**
- Request: `"calibration_baseline": "not valid json"`
- Expected: HTTP 422

#### 2.1.7 POST /api/mobile/device/register-token
**Test Case 1: FCM Token Registration**
- Request: `{"child_id": 1, "fcm_token": "token_xyz"}`
- Expected: HTTP 200
- Verify: fcm_token stored in child_profile

#### 2.1.8 POST /api/mobile/device/ping
**Test Case 1: Heartbeat Update**
- Request: `{"child_id": 1}`
- Expected: HTTP 200, timestamp in response
- Verify: last_sync updated to now()

#### 2.1.9 GET /api/mobile/config
**Test Case 1: Config Retrieval**
- Request: No auth required
- Expected: HTTP 200, includes minimum_version, latest_version, force_update, server_time

---

### 2.2 Guardian API Endpoints (GuardianApiController)

#### 2.2.1 POST /api/web/guardian/register
**Test Case 1: Valid Registration**
- Request:
  ```json
  {
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123",
    "contact_number": "5551234567"
  }
  ```
- Expected: HTTP 201, user and guardian profile created
- Verify: Hash password (not plaintext), unique email constraint

**Test Case 2: Duplicate Email**
- Request: Email already exists
- Expected: HTTP 422 validation error

**Test Case 3: Weak Password**
- Request: `"password": "123"` (< 8 chars)
- Expected: HTTP 422

**Test Case 4: Password Mismatch**
- Request: password_confirmation != password
- Expected: HTTP 422

#### 2.2.2 POST /api/web/guardian/child/add
**Test Case 1: Create Child Profile**
- Request:
  ```json
  {
    "name": "Johnny",
    "birthdate": "2015-06-15"
  }
  ```
- Expected: HTTP 201, child profile created with login_code
- Verify: 
  - Child user created with role: "Child"
  - SessionLimits default created (daily_limit_minutes: 120, mode: "Relaxed"...)
  - VirtualPet created (pet_state: "Healthy", xp_points: 0...)
  - GuardianChildLink record created
  - Login code is unique 6-digit string

**Test Case 2: Invalid Birthdate (Future Date)**
- Request: `"birthdate": "2030-01-01"`
- Expected: HTTP 422 (before:today validation)

**Test Case 3: Missing Name**
- Request: No name field
- Expected: HTTP 422

**Test Case 4: Transaction Rollback**
- Setup: Mock SessionLimits creation to fail
- Expected: All changes rolled back (child not created)

#### 2.2.3 PUT /api/web/guardian/child/{child_id}/limits
**Test Case 1: Update Limits**
- Request:
  ```json
  {
    "daily_limit_minutes": 180,
    "mode": "Strict",
    "auto_enforce_breaks": true
  }
  ```
- Expected: HTTP 200, limits updated, updated_at refreshed
- Verify: only specified fields updated, others unchanged

**Test Case 2: Invalid daily_limit_minutes**
- Request: `"daily_limit_minutes": 2000` (> 1440)
- Expected: HTTP 422

**Test Case 3: Child Not Found**
- Request: child_id: 99999
- Expected: HTTP 404

#### 2.2.4 POST /api/web/guardian/doctor/link
**Test Case 1: Link to Doctor**
- Request:
  ```json
  {
    "doctor_id": 5,
    "child_id": 1
  }
  ```
- Expected: HTTP 201, ClinicianPatientLink created with linkage_key, is_active: 0
- Verify: link is pending (not auto-accepted)

**Test Case 2: Duplicate Link**
- Request: Same doctor_id, child_id combo again
- Expected: HTTP 409, `message: "Link already exists"`

**Test Case 3: Guardian Doesn't Own Child**
- Setup: Try to link doctor to child owned by different guardian
- Request: Valid but unauthorized
- Expected: HTTP 403

#### 2.2.5 DELETE /api/web/guardian/child/{child_id}
**Test Case 1: Delete Child (GDPR)**
- Request: DELETE /api/web/guardian/child/1
- Expected: HTTP 200
- Verify: 
  - child_profile record deleted
  - child user record deleted
  - All cascade deletes (metrics, limits, pet, inventory, etc.)
  
**Test Case 2: Child Not Found**
- Request: DELETE with invalid child_id
- Expected: HTTP 404

**Test Case 3: Unauthorized Delete**
- Setup: Guardian tries to delete other guardian's child
- Expected: HTTP 403

---

## Phase 3: Authorization & Security Testing

### 3.1 Role-Based Access Control
- [ ] **Guardian accessing guardian routes:**
  - Can access own children: ✓
  - Cannot access doctor/admin routes: ✓
  - Cannot access other guardian's children: ✓

- [ ] **Child accessing mobile routes:**
  - Can sync own data: ✓
  - Cannot sync other child's data: ✓

- [ ] **Unauthenticated Access:**
  - Public endpoints (login, config): No auth required ✓
  - Protected endpoints: Return 401 if no token ✓

### 3.2 Data Ownership Validation
- [ ] **Account Ownership Checks:**
  - Child user_id matches in ChildProfile: ✓
  - Guardian user_id matches in GuardianProfile: ✓
  - Guardian-Child link verified before operations: ✓
  
- [ ] **Cross-Tenant Security:**
  - Guardian A cannot see Guardian B's children: ✓
  - Child A cannot sync data as Child B: ✓

### 3.3 Timestamp Manipulation (Delta Sync Security)
- [ ] **Verify device_timestamp validation:**
  - Invalid format rejected: ✓
  - Future timestamps accepted but compared fairly: ✓
  - No timezone confusion (use Y-m-d H:i:s format): ✓

- [ ] **Test timestamp edge cases:**
  - device_timestamp = "9999-12-31 23:59:59": Should work (far future)
  - device_timestamp = very old date: Should work (old data)
  - Compare uses >= logic (equal timestamps accepted): ✓

---

## Phase 4: Database Integrity Testing

### 4.1 Data Consistency
- [ ] **Child Profile Creation:**
  - user_id correctly references users table: ✓
  - login_code is unique: Test with 1000 sequential calls
  - No orphaned records: ✓

- [ ] **Session Limits Defaults:**
  - All 7 fields populated on creation: ✓
  - Correct data types (int, boolean, datetime): ✓
  - updated_at cast to datetime: ✓

- [ ] **Metrics Deduplication:**
  - Same timestamp + child_id = skip: ✓
  - Different timestamps = both inserted: ✓
  - Different children, same timestamp = both inserted: ✓
  - Performance: 100 metrics batch < 500ms: ✓

### 4.2 Foreign Key Constraints
- [ ] **Orphaned Records:**
  - Deleting Guardian: Check cascade deletes all linked children: ✓
  - Deleting Child: Check all metrics/limits/pet deleted: ✓

- [ ] **Race Conditions:**
  - Two simultaneous metric syncs same child: Both should insert if different timestamps
  - Test with concurrent requests

### 4.3 Transactions
- [ ] **addChild() transaction:**
  - All 5 operations (user, child, link, limits, pet) succeed: ✓
  - Any failure rolls back all: ✓
  - Tested with 50 rapid sequential calls: ✓

- [ ] **deleteChild() transaction:**
  - Both delete operations atomic: ✓

---

## Phase 5: Validation & Input Sanitization

### 5.1 Request Validation
**Test each endpoint with:**
- [ ] Missing required fields: ✓ (HTTP 422)
- [ ] Wrong data types (string instead of int): ✓ (HTTP 422)
- [ ] Out-of-range values: ✓ (HTTP 422)
- [ ] Extra fields (shouldn't cause errors): ✓

**Specific Validations:**
- [ ] Numeric ranges:
  - daily_limit_minutes: 1-1440 ✓
  - distance thresholds: 1-100 ✓
  - xp_points, currency: >= 0 ✓
  - screen_time_minutes: >= 0 ✓

- [ ] Enum validation:
  - mode: "Strict" or "Relaxed" only ✓
  - pet_state: "Healthy", "Good", "Critical", "Dead" ✓

- [ ] Email validation:
  - Valid format: ✓
  - Unique constraint: ✓

- [ ] Boolean validation:
  - auto_enforce_breaks: true/false: ✓

### 5.2 SQL Injection Prevention
- [ ] All queries use Eloquent (no raw SQL except in custom methods): ✓
- [ ] Run penetration test:
  ```bash
  # Test various injection payloads
  curl -X POST http://localhost/api/mobile/child/login \
    -H "Content-Type: application/json" \
    -d '{"login_code": "123\" OR \"1\"=\"1", "device_id": null}'
  ```
  Expected: Validation error or safe handling ✓

---

## Phase 6: Frontend Testing (Guardian Dashboard)

### 6.1 UI/UX Verification
- [ ] **Tab Navigation:**
  - Click Overview: Displays correct content ✓
  - Click Analytics: Displays correct content ✓
  - Click Controls: Displays correct content ✓
  - Active tab styling applied: ✓
  
- [ ] **Responsive Design:**
  - Desktop (1920px): Full layout ✓
  - Tablet (768px): Grid adjusts properly ✓
  - Mobile (375px): Single column, readable ✓

- [ ] **Chart Rendering:**
  - Eye Health doughnut chart loads: ✓
  - Weekly trends line chart loads: ✓
  - Charts are interactive (hover shows data): ✓

### 6.2 Form Interaction (Controls Tab)
- [ ] **Screen Time Slider:**
  - Drag from 1 to 1440: Value updates in real-time ✓
  - Display updates: "X minutes" ✓
  - Validate min/max constraints: ✓

- [ ] **Mode Selection:**
  - Click "Relaxed": Selected and description shown ✓
  - Click "Strict": Selected and description shown ✓
  - Radio button styling correct: ✓

- [ ] **Distance Threshold Sliders:**
  - Harmful threshold slider works: ✓
  - Critical threshold slider works: ✓
  - Values update independently: ✓

- [ ] **Auto-Enforce Breaks Toggle:**
  - Toggle on: checked ✓
  - Toggle off: unchecked ✓
  - State persists in form: ✓

### 6.3 Save Functionality
- [ ] **Successful Save:**
  - Click "Save Changes": Request sent to API ✓
  - Success message appears: "✓ Changes saved successfully!" ✓
  - Message auto-hides after 3s: ✓
  - Form values persist: ✓

- [ ] **API Error Handling:**
  - API returns 400 error: Error message displayed ✓
  - API returns 403 (unauthorized): "Unauthorized" message ✓
  - API timeout (> 5s): Timeout error shown ✓

- [ ] **Cancel Button:**
  - Click Cancel: Form reverts to original values ✓
  - No API request sent: ✓

### 6.4 Data Display (Overview & Analytics)
- [ ] **Overview Tab:**
  - Eye Health Score displays: ✓
  - Stat cards show correct values (screen time, blink rate, distance, strain): ✓
  - 20-20-20 compliance percentage shows: ✓
  - Last break time displays: ✓
  - Next break time displays: ✓

- [ ] **Analytics Tab:**
  - Weekly trends chart shows 7 days: ✓
  - Metric cards (Screen Time, Blink, Distance) display: ✓
  - Trend percentages (↑↓) display correctly: ✓
  - Colors indicate Good (green) vs Bad (red): ✓

### 6.5 Accessibility
- [ ] **Mobile Keyboard:**
  - Slider works with keyboard arrows: ✓
  - Tab navigation through form: ✓

- [ ] **Color Contrast:**
  - All text readable (WCAG AA standard): ✓

- [ ] **Screen Reader (NVDA/JAWS):**
  - Form labels read correctly: ✓
  - Chart titles read: ✓
  - Success/error messages announced: ✓

---

## Phase 7: Edge Cases & Error Scenarios

### 7.1 Concurrency & Race Conditions
- [ ] **Two Guardian Updates Simultaneously:**
  - Guardian A updates daily_limit to 100
  - Guardian B updates daily_limit to 200 (same child, different session)
  - Result: Last write wins, timestamp updated: ✓

- [ ] **Mobile & Web Sync Simultaneously:**
  - Mobile syncs pet with timestamp T1
  - Guardian updates rules with timestamp T2 (before mobile receives response)
  - Mobile should get server's T2 rules: ✓

- [ ] **Batch Insert Race:**
  - Two batch requests with overlapping timestamps
  - Deduplication handles correctly: ✓

### 7.2 Null/Missing Data
- [ ] **Child with No Metrics:**
  - Dashboard loads without errors: ✓
  - Eye Health Score shows default (75): ✓
  - Charts show empty states gracefully: ✓

- [ ] **Metrics with Null Fields:**
  - avg_blink_rate = null: Calculations skip ✓
  - avg_distance = null: Calculations skip ✓
  - Results don't break calculations: ✓

### 7.3 Boundary Values
- [ ] **Extreme Data:**
  - daily_limit_minutes = 1: Accepted ✓
  - daily_limit_minutes = 1440: Accepted ✓
  - daily_limit_minutes = 0: Rejected ✓
  - xp_points = 999999999: Accepted ✓
  - strain_events = 1000+: Accepted ✓

- [ ] **Date/Time:**
  - Very old timestamp (1970): Accepted ✓
  - Far future timestamp (2100): Accepted ✓
  - Leap year date (Feb 29): Handled correctly ✓

### 7.4 Cascade Deletions
- [ ] **Delete Child Cascades:**
  - Delete child: User record deleted ✓
  - Delete child: All metrics deleted ✓
  - Delete child: SessionLimits deleted ✓
  - Delete child: VirtualPet deleted ✓
  - Delete child: GuardianChildLink deleted ✓
  - Delete child: ChildInventory deleted ✓

---

## Phase 8: Performance Testing

### 8.1 Load Testing
- [ ] **Batch Metrics Ingestion:**
  - 100 metrics: < 500ms ✓
  - 1000 metrics: < 2s ✓
  - 10000 metrics: < 10s ✓
  - Monitor memory usage: ✓

- [ ] **Concurrent Requests:**
  - 10 simultaneous mobile syncs: All succeed ✓
  - 50 simultaneous logins: No race conditions ✓

### 8.2 Database Query Performance
- [ ] **N+1 Query Detection:**
  - Run dashboard with debugbar: Only required queries ✓
  - Eager load relationships: ✓

- [ ] **Index Coverage:**
  - `child_profile(login_code)`: Indexed ✓
  - `eye_health_metrics(child_id, timestamp)`: Indexed ✓
  - `session_limits(child_id)`: Indexed ✓

### 8.3 API Response Times
- [ ] **Endpoint Benchmarks:**
  - POST /api/mobile/child/login: < 100ms ✓
  - POST /api/mobile/child/{child_id}/sync/metrics/batch (100 items): < 500ms ✓
  - PUT /api/mobile/child/{child_id}/sync/limits: < 200ms ✓
  - GET /guardian/dashboard: < 1s ✓

---

## Phase 9: Integration Testing

### 9.1 Full Workflow Tests

**Workflow 1: Guardian Registration → Add Child → Update Rules**
```
1. Register guardian (POST /api/web/guardian/register)
   ✓ User and GuardianProfile created
2. Add child (POST /api/web/guardian/child/add)
   ✓ Child created with defaults
3. Update limits (PUT /api/web/guardian/child/{id}/limits)
   ✓ Limits saved
4. View dashboard (GET /guardian/dashboard?child_id=X)
   ✓ Child data displayed
5. Save from UI
   ✓ API call succeeds, dashboard updates
```

**Workflow 2: Child Mobile Sync → Guardian Views Data**
```
1. Child logs in (POST /api/mobile/child/login)
   ✓ Token issued
2. Child syncs metrics (POST /api/mobile/child/{id}/sync/metrics/batch)
   ✓ 120 metrics inserted in 30-min batches
3. Child syncs pet (PUT /api/mobile/child/{id}/sync/pet)
   ✓ Pet updated with device timestamp
4. Guardian views dashboard
   ✓ Metrics reflected, Eye Health Score calculated
   ✓ Charts show trends
```

**Workflow 3: Delta Sync Conflict Resolution**
```
1. Child syncs pet (device time: 10:00)
   ✓ Synced: true
2. Guardian updates limits on web (server time: now)
3. Child syncs limits (device time: earlier timestamp)
   ✓ Synced: false, returns server state
4. Child app updates local cache
   ✓ Mobile app reflects guardian's rules
```

### 9.2 Cross-Component Integration
- [ ] **Service → Controller → API Response:**
  - Service returns array → Controller calls response()->json() → Valid JSON ✓
  - Error cases flow correctly: ✓

- [ ] **Controller → View:**
  - Dashboard controller passes data → View renders: ✓
  - Data binding works (child name, metrics): ✓

- [ ] **API → Frontend (AJAX):**
  - Dashboard form calls API: ✓
  - Response parsed correctly: ✓
  - Success/error messages display: ✓

---

## Phase 10: Deployment & Sanity Checks

### 10.1 Pre-Deployment Checklist
- [ ] **Code Review:**
  - No hardcoded credentials: ✓
  - No console.log/dd/dump statements: ✓
  - No debug routes exposed: ✓
  - No sensitive data in comments: ✓

- [ ] **Configuration:**
  - Correct database (not production): ✓
  - Correct API endpoints: ✓
  - CSRF tokens enabled: ✓
  - CORS policies set: ✓

- [ ] **Dependencies:**
  - `composer install --no-dev`: No errors ✓
  - All imports valid: ✓
  - No version conflicts: ✓

### 10.2 Post-Deployment Smoke Tests
- [ ] **Health Checks:**
  - API server responds: ✓
  - Database connectivity: ✓
  - Cache working: ✓

- [ ] **Critical Endpoints:**
  - GET /api/mobile/config: 200 ✓
  - POST /api/mobile/child/login: 200/401 (valid/invalid) ✓
  - GET /guardian/dashboard?child_id=1: 200/403 ✓

- [ ] **Web Access:**
  - Dashboard loads: ✓
  - Charts render: ✓
  - Forms functional: ✓

### 10.3 Database Reset Test
- [ ] **Fresh Migration:**
  ```bash
  php artisan migrate:fresh
  php artisan db:seed
  ```
  - No errors: ✓
  - All tables created: ✓
  - Test data seeded: ✓

- [ ] **Run Tests:**
  ```bash
  php artisan test
  ```
  - All tests pass: ✓
  - Coverage > 80%: ✓

---

## Phase 11: Manual Testing Scenarios

### Scenario A: Parent Setup Flow
1. Parent navigates to /auth/signup
2. Enters name, email, password
3. Clicks "Create Account"
4. Redirected to guardian dashboard
5. Clicks "Add Child"
6. Enters child name, birthdate
7. Receives 6-digit login code
8. Shares code with child mobile app
9. Views dashboard → child metrics sync over time

**Expected: All operations succeed, no errors**

### Scenario B: Child Mobile Usage
1. Child opens app
2. Enters login code (6 digits)
3. App stores auth token
4. Every 30 min, app batches metrics (100 readings) and syncs
5. Sync includes: blink_rate, distance, strain_events, screen_time
6. App stores pet XP locally and syncs when online
7. If parent updates rules, next sync returns server version

**Expected: Seamless sync, no data loss**

### Scenario C: Parent Adjusts Rules
1. Parent logs in → Guardian Dashboard
2. Views child's Overview tab (eye health score, metrics)
3. Clicks Controls tab
4. Adjusts daily_limit_minutes slider to 90 min
5. Toggles "Auto-Enforce Breaks" off
6. Clicks "Save Changes"
7. Success message appears
8. Child app doesn't receive update until next sync
9. Next sync returns new rulesset with server timestamp

**Expected: Rules saved, mobile respects new limits**

### Scenario D: Conflict Handling
1. Parent updates limits on web (time: 14:00)
2. Child syncs pet/limits from offline queue (device time: 13:50, all older)
3. System responds: `synced: false` + current server state
4. Child app updates local cache
5. Parent confirms new values through dashboard

**Expected: No data loss, mobile respects parent authority**

---

## Phase 12: Monitoring & Logging

### 12.1 Log Validation
- [ ] **Monitor logs for errors:**
  ```bash
  tail -f storage/logs/laravel.log | grep ERROR
  ```
  - No 5xx errors in production ✓
  - No database errors ✓

- [ ] **API Request Logging:**
  - All POST/PUT/DELETE logged: ✓
  - Failed validations logged: ✓
  - Authorization failures logged: ✓

### 12.2 Metrics & Observability
- [ ] **API Monitoring:**
  - Response times tracked: ✓
  - Error rates monitored: ✓
  - Slow query detection: ✓

- [ ] **Data Quality:**
  - Metrics ingestion rate: Monitor for anomalies ✓
  - Batch success rate: Track duplicates skipped ✓
  - Delta sync conflict rate: Monitor mismatches ✓

---

## Execution Checklist

### Run These Commands Before Submitting:

```bash
# 1. Syntax validation
php -l app/Services/*.php
php -l app/Http/Controllers/*.php
php -l routes/*.php

# 2. Code standards
php artisan code:analyse  # or /vendor/bin/phpstan analyze

# 3. Unit/Feature tests
php artisan test

# 4. Database reset
php artisan migrate:fresh

# 5. Seed test data
php artisan db:seed

# 6. Check no debug statements
grep -r "dd\|dump\|console\.log" app/

# 7. Verify no hardcoded secrets
grep -r "password\|token\|secret" app/ | grep -v "password_"

# 8. Code review
# - Manually inspect: Services, Controllers, Views
# - Check error messages are user-friendly
# - Verify no N+1 queries

# 9. Manual smoke test
# - Login as guardian
# - View dashboard
# - Update limits and save
# - Verify API response in Network tab
# - Check console for JS errors

# 10. Mobile API test (curl)
curl -X POST http://localhost/api/mobile/child/login \
  -H "Content-Type: application/json" \
  -d '{"login_code":"123456","device_id":"test"}'

# Expected: 
# {"status":"success","message":"...","data":{...},"errors":null}

# 11. One final dashboard test
# - Open /guardian/dashboard?child_id=1
# - All tabs load
# - Charts render
# - Form saves
```

---

## Risk Assessment & Mitigation

| Risk | Severity | Mitigation |
|------|----------|-----------|
| Delta sync timestamp comparison fails | HIGH | Unit test all comparison scenarios (>, <, =) |
| Batch deduplication causes data loss | HIGH | Log skipped duplicates, verify in test |
| Race conditions in transactions | HIGH | Test with concurrent requests tool (JMeter) |
| Child data leaks between guardians | HIGH | Verify ownership check in every endpoint |
| Performance degrades with 100k metrics | MEDIUM | Load test, add DB indexes, pagination if needed |
| Guardian dashboard crash on null data | MEDIUM | Handle nulls in Controller, provide defaults in View |
| Mobile app ignores server ruleset | MEDIUM | Test sync response parsing in mock app |

---

## Sign-Off

**QA Coordinator:** _____________________  
**Date:** _____________________  
**Status:** [ ] PASS [ ] CONDITIONAL [ ] FAIL  
**Comments:**

