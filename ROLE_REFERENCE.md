# Role-Based Access Control - Quick Reference

## Role IDs
- **1** = Admin (full access)
- **2** = Player (default for new users)
- **3** = Organizer (requires admin approval)

## Frontend - Check Roles

```javascript
import { hasRole, isAdmin, isOrganizer, isPlayer } from './core/auth.js';

// Check specific role
if (hasRole('Admin')) { /* ... */ }

// Quick checks
if (isAdmin()) { /* admin only */ }
if (isOrganizer()) { /* organizer only */ }
if (isPlayer()) { /* player only */ }

// Get all user roles
import { getUserRoles } from './core/auth.js';
const roles = getUserRoles(); // Returns array of role objects
```

## Frontend - HTML Attributes

```html
<!-- Show to specific role -->
<div data-role="Admin">Admin only content</div>

<!-- Show to multiple roles -->
<div data-roles="Admin,Organizer">Admin or Organizer</div>

<!-- Display role badges -->
<div id="user-role-badges"></div>
```

## Frontend - API Calls

```javascript
import { 
  requestOrganizerRole,
  getPendingRoleRequests,  // Admin only
  approveRoleRequest,       // Admin only
  rejectRoleRequest,        // Admin only
  getAllUsers,              // Admin only
  assignRole,               // Admin only
  removeRole                // Admin only
} from './core/auth.js';

// Request organizer role
await requestOrganizerRole("I want to organize tournaments");

// Admin: Get pending requests
const requests = await getPendingRoleRequests();

// Admin: Approve request
await approveRoleRequest(requestId);

// Admin: Assign role manually
await assignRole(userId, roleId);
```

## Backend - Protect Endpoints

```php
<?php
require_once '../middleware/auth_middleware.php';

$authMiddleware = getAuthMiddleware();

// Require authentication (any logged-in user)
$user = $authMiddleware->requireAuth();

// Require specific role
$user = $authMiddleware->requireRole('Admin');

// Require one of multiple roles
$user = $authMiddleware->requireRole(['Admin', 'Organizer']);

// Check role without throwing error
$user = $authMiddleware->getCurrentUser();
if ($user && $authMiddleware->hasRole($user, 'Admin')) {
    // User is admin
}
```

## Backend - Role Methods

```php
<?php
require_once '../classes/Auth.class.php';

$auth = new Authentication($db);

// Get user roles
$roles = $auth->getUserRoles($userId);

// Check if user has role
$hasRole = $auth->hasRole($userId, 'Admin');

// Assign role to user
$auth->assignRole($userId, $roleId);

// Remove role from user
$auth->removeRole($userId, $roleId);

// Get pending role requests
$requests = $auth->getPendingRoleRequests();

// Process role request
$auth->processRoleRequest($requestId, 'approved', $adminUserId);
$auth->processRoleRequest($requestId, 'rejected', $adminUserId);

// Request role upgrade
$auth->requestRoleUpgrade($userId, $roleId, $reason);
```

## Database Queries

```sql
-- Check user roles
SELECT u.username, r.role_name 
FROM users u
JOIN user_roles ur ON u.id = ur.user_id
JOIN roles r ON ur.role_id = r.id
WHERE u.id = 1;

-- Manually assign admin role
INSERT INTO user_roles (user_id, role_id) VALUES (1, 1);

-- Remove role
DELETE FROM user_roles WHERE user_id = 5 AND role_id = 3;

-- View pending requests
SELECT * FROM role_requests WHERE status = 'pending';

-- View all sessions
SELECT u.username, s.ip_address, s.created_at, s.expires_at
FROM sessions s
JOIN users u ON s.user_id = u.id
WHERE s.expires_at > NOW();
```

## Common Patterns

### Protect Page (Frontend)
```javascript
import { isAuthenticated, hasRole } from './core/auth.js';

// Require login
if (!isAuthenticated()) {
    window.location.href = '../auth/login.php';
}

// Require specific role
if (!hasRole('Admin')) {
    alert('Access denied');
    window.location.href = '../home/dashboard.php';
}
```

### Protect Endpoint (Backend)
```php
<?php
require_once '../middleware/auth_middleware.php';

$authMiddleware = getAuthMiddleware();

// Admin only endpoint
$user = $authMiddleware->requireRole('Admin');

// Continue with admin logic...
```

### Show/Hide UI Elements
```javascript
import { showAdminControls } from './roleUtils.js';

const adminPanel = document.getElementById('admin-panel');
showAdminControls(adminPanel); // Shows if admin, hides if not
```

### Create Role-Based Navigation
```javascript
import { getRoleBasedNavigation } from './roleUtils.js';

const navItems = getRoleBasedNavigation();
// Returns only items user has permission to see
```

## Testing Users

```sql
-- Create test users with different roles
-- 1. Register users through the app first
-- 2. Then assign roles:

-- Make user 1 an Admin
INSERT INTO user_roles (user_id, role_id) VALUES (1, 1);

-- User 2 already has Player (assigned automatically)

-- Make user 3 an Organizer
INSERT INTO user_roles (user_id, role_id) VALUES (3, 3);

-- User with multiple roles (Admin + Organizer)
INSERT INTO user_roles (user_id, role_id) VALUES (4, 1);
INSERT INTO user_roles (user_id, role_id) VALUES (4, 3);
```

## JWT Token Structure

```javascript
// Token payload contains:
{
  "user_id": 1,
  "username": "john_doe",
  "email": "john@example.com",
  "roles": [
    {
      "id": 1,
      "role_name": "Admin",
      "description": "Administrator with full access"
    }
  ],
  "iat": 1639123456,  // Issued at
  "exp": 1639209856,  // Expires at
  "nbf": 1639123456   // Not before
}
```

## Error Codes

- **401 Unauthorized** - No token or invalid token
- **403 Forbidden** - Valid token but insufficient role
- **400 Bad Request** - Missing required parameters
- **500 Internal Server Error** - Server-side error

## Security Notes

✅ **DO:**
- Always validate tokens server-side
- Use HTTPS in production
- Implement rate limiting
- Log role changes
- Expire old sessions

❌ **DON'T:**
- Trust client-side role checks alone
- Store sensitive data in JWT payload
- Use predictable secret keys
- Skip input validation
- Allow role self-assignment
