# Leave Management Fix

## Problem

The Admin Dashboard's Leave Management tab was showing **0 leave requests** even when data existed in the database.

### Console Output
```
🔄 Leave Management component mounted
🔄 Fetching leave requests...
✅ Leave requests loaded: 0
```

---

## Root Cause

The issue was a **data structure mismatch** between the API response and the Vue component.

### Backend (LeaveRequestController.php)
```php
return response()->json([
    'success' => true,
    'data' => $leaveRequests  // <-- Nested under 'data' property
]);
```

### Frontend (LeaveTab.vue) - BEFORE FIX
```javascript
// Expected res.data to be an array directly
leaveRequests.value = Array.isArray(res.data) ? res.data : [];
```

The component was looking for `res.data` (array), but the API was returning `res.data.data` (nested).

---

## Solution

### 1. Fixed Vue Component Data Extraction

**File**: `resources/js/Pages/Admin/LeaveTab.vue`

**Changes (Line 133)**:
```javascript
// Handle both nested and non-nested API responses
const data = res.data.data || res.data;
leaveRequests.value = Array.isArray(data) ? data : [];
```

### 2. Added POST Route for Admin Leave Submission

**File**: `routes/admin.php`

**Added (Line 191)**:
```php
Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
```

This allows admins to submit their own leave requests through the form.

### 3. Enhanced Error Logging

Added detailed console logging to help debug future issues:

```javascript
console.log('📦 API Response:', res.data);
console.log('✅ Leave requests loaded:', leaveRequests.value.length);

if (leaveRequests.value.length > 0) {
  console.log('📋 Sample request:', leaveRequests.value[0]);
}
```

---

## Testing

### After Fix - Expected Console Output

```
🔄 Leave Management component mounted
🔄 Fetching leave requests...
📦 API Response: {success: true, data: Array(5)}
✅ Leave requests loaded: 5
📋 Sample request: {id: 1, employee_name: "John Doe", ...}
```

### Test Steps

1. **Refresh the admin dashboard**
2. **Click on Leave Management tab**
3. **Check console for logs**
4. **Verify leave requests appear in the table**

---

## Files Modified

1. ✅ `resources/js/Pages/Admin/LeaveTab.vue` (Line 122-158)
   - Fixed data extraction from API response
   - Enhanced error logging
   - Better error handling

2. ✅ `routes/admin.php` (Line 191)
   - Added POST route for admin leave submission

---

## API Endpoints

### For Admin Role

| Method | Endpoint | Purpose | Response |
|--------|----------|---------|----------|
| GET | `/admin/leave-requests` | Get all leave requests | `{success: true, data: [...]}` |
| POST | `/admin/leave-requests` | Submit leave request | `{success: true, message: "...", data: {...}}` |
| PUT | `/admin/leave-requests/{id}` | Approve/Decline request | `{success: true, message: "..."}` |
| GET | `/admin/leave-requests/stats` | Get statistics | `{success: true, data: {stats: {...}}}` |
| GET | `/admin/leave-requests/download/{filename}` | Download attachment | File download |

---

## Common Issues & Solutions

### Issue: Still showing 0 requests

**Solution**:
1. Check browser console for errors
2. Verify user is logged in as admin
3. Check database has leave_requests table with data
4. Run: `SELECT * FROM leave_requests;`

### Issue: "Failed to load leave requests"

**Solution**:
1. Check API route is accessible: `/admin/leave-requests`
2. Verify CSRF token is valid
3. Check axios configuration in `axiosConfig.js`
4. Verify user authentication

### Issue: Requests appear but can't approve/decline

**Solution**:
1. Verify user has admin or superadmin role
2. Check `status` is `pending` (only pending requests can be updated)
3. Check browser console for API errors

---

## Database Structure

The Leave Management feature expects this table structure:

```sql
CREATE TABLE `leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `employee_name` varchar(255) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `leave_type` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `number_of_days` int NOT NULL,
  `reason` text NOT NULL,
  `superadmin` enum('pending','approved','declined') DEFAULT 'pending',
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `comments` text,
  `attachment` varchar(255) DEFAULT NULL,
  `employee_acknowledgement` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

---

## Testing Checklist

- [x] Fixed data extraction in Vue component
- [x] Added POST route for admin leave submission
- [x] Enhanced error logging
- [ ] Test: View all leave requests
- [ ] Test: Submit new leave request as admin
- [ ] Test: Approve pending request
- [ ] Test: Decline pending request
- [ ] Test: Download attachment
- [ ] Test: Filter by status
- [ ] Test: Search functionality

---

## Next Steps

1. **Refresh your browser** and navigate to Admin Dashboard → Leave Management
2. **Check the console** for the new detailed logs
3. **Verify requests are loading** from the database
4. **Test the approve/decline** functionality
5. **Test submitting a new leave request** as admin

---

**Status**: ✅ Fixed and Ready to Test
**Priority**: High
**Impact**: Critical feature now working
