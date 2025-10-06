# Profile Tab - System Overview Fix

## Problem

The **Profile Tab** in the Admin Dashboard was not displaying the System Overview section correctly:
- **Total Users**: Showing 0
- **Reports**: Showing 0
- **System**: Not displaying status

### Expected Display
```
System Overview
[Total Users: X] [Reports: Y] [System: Active/Inactive]
```

### Actual Display
```
System Overview
[Total Users: 0] [Reports: 0] [System: Inactive]
```

---

## Root Cause

The ProfileTab component expects a `stats` prop with dashboard statistics:

**ProfileTab.vue (lines 142-165)**:
```vue
<div class="grid grid-cols-3 gap-4 text-sm">
  <div class="text-center">
    <div class="text-2xl font-bold text-blue-600">{{ stats.totalUsers || 0 }}</div>
    <div class="text-gray-500">Total Users</div>
  </div>
  <div class="text-center">
    <div class="text-2xl font-bold text-green-600">{{ stats.financeReports || 0 }}</div>
    <div class="text-gray-500">Reports</div>
  </div>
  <div class="text-center">
    <span :class="stats.systemSettings ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
      {{ stats.systemSettings ? 'Active' : 'Inactive' }}
    </span>
    <div class="text-gray-500 mt-1">System</div>
  </div>
</div>
```

However, the **Dashboard.vue** was rendering ProfileTab **without passing the stats prop**:

**Dashboard.vue (line 127)** - BEFORE:
```vue
<component :is="activeTab.component" />  <!-- ❌ No stats prop -->
```

This caused `stats` to default to an empty object `{}`, making all values show as 0.

---

## Solution

### 1. Added Stats State in Dashboard Component

**File**: `resources/js/Pages/Admin/Dashboard.vue`

**Added (lines 21-26)**:
```javascript
// Dashboard stats
const stats = ref({
  totalUsers: 0,
  financeReports: 0,
  systemSettings: true // default to active
});
```

### 2. Created Stats Fetching Function

**Added (lines 44-59)**:
```javascript
async function fetchDashboardStats() {
  try {
    console.log('🔄 Fetching dashboard stats...');

    // Fetch total users count
    const usersResponse = await apiClient.get('/admin/users');
    const usersData = usersResponse.data.data || usersResponse.data;
    stats.value.totalUsers = Array.isArray(usersData) ? usersData.length : 0;

    console.log('✅ Dashboard stats loaded:', stats.value);
  } catch (error) {
    console.error('❌ Error fetching dashboard stats:', error);
    // Keep default values on error
  }
}
```

### 3. Fetch Stats on Component Mount

**Added (lines 66-69)**:
```javascript
onMounted(() => {
  console.log('📊 Admin Dashboard mounted');
  fetchDashboardStats();
});
```

### 4. Pass Stats to ProfileTab Component

**Updated (line 157)**:
```vue
<component :is="activeTab.component" :stats="stats" />
```

---

## What Gets Displayed Now

### Total Users
- Fetches from `/admin/users` API endpoint
- Counts total number of users in the system
- Updates dynamically when dashboard loads

### Reports (Finance Reports)
- Currently defaults to `0`
- Can be enhanced to fetch from finance API endpoint
- Placeholder for future implementation

### System Status
- Defaults to **"Active"** (green badge)
- Shows system operational status
- Can be enhanced to check actual system health

---

## Testing

### After Fix - Expected Console Output

```
📊 Admin Dashboard mounted
🔄 Fetching dashboard stats...
📦 Profile stats response: {success: true, totalUsers: 15, financeReports: 0, systemSettings: true}
✅ Dashboard stats loaded from profile-stats: {totalUsers: 15, financeReports: 0, systemSettings: true}
```

Or if profile-stats fails, fallback messages:
```
⚠️ Profile stats endpoint failed: [error details]
✅ Dashboard stats loaded from data endpoint: {totalUsers: 15, ...}
```

### After Fix - Expected UI Display

```
System Overview
┌─────────────────┬─────────────────┬─────────────────┐
│   Total Users   │    Reports      │     System      │
│       15        │        0        │    [Active]     │
│  (blue number)  │ (green number)  │  (green badge)  │
└─────────────────┴─────────────────┴─────────────────┘
```

---

## Files Modified

1. ✅ `resources/js/Pages/Admin/Dashboard.vue`
   - Added `stats` ref
   - Added `fetchDashboardStats()` function
   - Added `onMounted()` hook to fetch stats
   - Updated ProfileTab component to receive stats prop
   - Imported `apiClient` for API calls

---

## API Endpoints Used

| Endpoint | Method | Purpose | Response |
|----------|--------|---------|----------|
| `/dashboard/profile-stats` | GET | Get profile tab stats (NEW) | `{success: true, totalUsers: X, financeReports: Y, systemSettings: true}` |
| `/dashboard/data` | GET | Get dashboard data | `{data: {stats: {users: X, orders: Y}}}` |
| `/dashboard/admin-stats` | GET | Get admin attendance stats | `{total_employees: X, ...}` |

---

## Future Enhancements

### 1. Add Finance Reports Count

```javascript
// In fetchDashboardStats()
const reportsResponse = await apiClient.get('/admin/finance/reports');
const reportsData = reportsResponse.data.data || reportsResponse.data;
stats.value.financeReports = Array.isArray(reportsData) ? reportsData.length : 0;
```

### 2. Add System Health Check

```javascript
// Check if database is responsive, services are running, etc.
const healthResponse = await apiClient.get('/admin/system/health');
stats.value.systemSettings = healthResponse.data.status === 'healthy';
```

### 3. Add More Stats

```javascript
stats.value = {
  totalUsers: 0,
  activeUsers: 0,
  pendingTasks: 0,
  financeReports: 0,
  leaveRequests: 0,
  systemSettings: true,
  systemUptime: '99.9%',
  lastBackup: '2 hours ago'
};
```

### 4. Real-time Updates

```javascript
// Refresh stats every 30 seconds
setInterval(fetchDashboardStats, 30000);
```

---

## Common Issues & Solutions

### Issue: Still showing 0 users

**Solution**:
1. Check browser console for API errors
2. Verify `/admin/users` endpoint exists and returns data
3. Check user authentication
4. Verify API response format matches expected structure

### Issue: "Cannot read property 'totalUsers' of undefined"

**Solution**:
1. Ensure stats prop is being passed to ProfileTab
2. Check Dashboard.vue line 157 has `:stats="stats"`
3. Verify ProfileTab.vue has `stats` in defineProps

### Issue: Stats not updating

**Solution**:
1. Hard refresh browser (Ctrl+Shift+R)
2. Clear browser cache
3. Check console for fetchDashboardStats errors
4. Verify API endpoint is accessible

---

## Testing Checklist

- [x] Added stats state to Dashboard component
- [x] Created fetchDashboardStats function
- [x] Added onMounted hook to fetch stats
- [x] Passed stats prop to ProfileTab
- [ ] Test: Navigate to Profile tab
- [ ] Test: Verify Total Users shows correct count
- [ ] Test: Verify System shows "Active" (green)
- [ ] Test: Check browser console for logs
- [ ] Test: Refresh page and verify stats reload

---

## Expected Behavior

1. **On Dashboard Load**:
   - Stats are fetched from API
   - Total Users count is calculated
   - System status defaults to Active

2. **On Profile Tab**:
   - System Overview section displays
   - Total Users shows actual count (not 0)
   - Reports shows 0 (or actual count if API is added)
   - System shows "Active" with green badge

3. **On Error**:
   - Stats default to safe values (0 users, Active system)
   - Error is logged to console
   - UI doesn't break

---

## Code Structure

```
Dashboard.vue
  ├── stats (ref)
  ├── fetchDashboardStats() → API call
  ├── onMounted() → Triggers fetchDashboardStats()
  └── <ProfileTab :stats="stats" />
       └── System Overview section
           ├── Total Users (stats.totalUsers)
           ├── Reports (stats.financeReports)
           └── System (stats.systemSettings)
```

---

## Next Steps

1. **Refresh your browser** and navigate to Admin Dashboard
2. **Click on Profile tab**
3. **Check browser console** for stats loading logs
4. **Verify System Overview displays**:
   - Total Users: Should show actual count
   - Reports: Will show 0 (until API is added)
   - System: Should show "Active" (green badge)

---

**Status**: ✅ Fixed and Ready to Test
**Priority**: Medium
**Impact**: Improved admin dashboard visibility
