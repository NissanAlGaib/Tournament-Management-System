# Role-Based Access Control (RBAC) Integration - Setup Guide

## Overview
This guide will help you set up and test the complete role-based access control system with JWT authentication and server-side sessions for the Tournament Management System.

## Installation Steps

### 1. Database Setup

Run the SQL migration script to create the necessary tables:

```bash
# Navigate to your MySQL/XAMPP installation
mysql -u root -p tournament_db < backend/database/setup_roles.sql
```

Or manually execute the SQL file in phpMyAdmin:
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select the `tournament_db` database
3. Click on "Import" tab
4. Choose file: `backend/database/setup_roles.sql`
5. Click "Go"

**Tables Created:**
- `roles` - Stores role definitions (Admin, Player, Organizer)
- `user_roles` - Junction table linking users to roles
- `role_requests` - Stores organizer role upgrade requests
- `sessions` - Server-side session storage for JWT tokens

### 2. Create First Admin User

After running the migration, you need to manually create your first admin user:

```sql
-- 1. Register a new user normally through the app
-- 2. Then run this SQL to make them an admin:

INSERT INTO user_roles (user_id, role_id) 
VALUES (1, 1); -- Assigns Admin role (role_id=1) to user_id=1

-- Verify admin role:
SELECT u.username, r.role_name 
FROM users u 
JOIN user_roles ur ON u.id = ur.user_id 
JOIN roles r ON ur.role_id = r.id 
WHERE u.id = 1;
```

### 3. Backend Configuration

**Important:** Change the JWT secret key in production!

Edit `backend/classes/JWT.class.php`:
```php
// Line 8-9
// Change this to a secure random string for production
$this->secret_key = getenv('JWT_SECRET') ?: 'your_super_secret_jwt_key_change_in_production_2024';
```

For production, set an environment variable:
```php
// In your server config or .env file
JWT_SECRET=your-very-long-random-secure-key-here
```

### 4. Test the System

#### Step 1: Register New Users
1. Navigate to: `http://localhost/your-project/frontend/app/views/pages/auth/register.php`
2. Register 2-3 test users
3. All new users automatically get the "Player" role

#### Step 2: Login
1. Login with any registered user
2. Check browser DevTools > Application > Local Storage
3. You should see:
   - `auth_token` - JWT token
   - `user` - User data with roles array

#### Step 3: Test Player Features
- Login as a regular player
- Access dashboard and tournaments pages
- Try to access admin page (should be denied)

#### Step 4: Request Organizer Role
```javascript
// In browser console on any logged-in page:
import { requestOrganizerRole } from './core/auth.js';
await requestOrganizerRole("I want to organize tournaments for my community");
```

#### Step 5: Admin Approval (as Admin)
1. Login as your admin user (created in step 2)
2. Navigate to: `http://localhost/your-project/frontend/app/views/pages/admin/role-management.php`
3. View pending role requests
4. Approve or reject requests
5. Manage user roles directly

## API Endpoints

### Authentication API (`backend/api/auth_api.php`)

#### POST `/auth_api.php`
```javascript
// Register
{
  "action": "register",
  "username": "john_doe",
  "email": "john@example.com",
  "password": "password123"
}

// Login
{
  "action": "login",
  "username": "john_doe",
  "password": "password123"
}

// Logout
{
  "action": "logout"
}

// Check Role
{
  "action": "check-role",
  "role": "Admin"
}
// Headers: Authorization: Bearer <token>

// Request Organizer Role
{
  "action": "request-organizer-role",
  "reason": "I want to organize tournaments"
}
// Headers: Authorization: Bearer <token>

// Verify Token
{
  "action": "verify-token"
}
// Headers: Authorization: Bearer <token>

// Get All Roles
{
  "action": "get-roles"
}
```

### Admin API (`backend/api/admin_api.php`)
**All endpoints require Admin role and JWT token in Authorization header**

#### GET `/admin_api.php`
```javascript
// Get Pending Requests
?action=pending-requests

// Get All Users
?action=all-users
```

#### POST `/admin_api.php`
```javascript
// Approve Request
{
  "action": "approve-request",
  "request_id": 1
}

// Reject Request
{
  "action": "reject-request",
  "request_id": 1
}

// Assign Role to User
{
  "action": "assign-role",
  "user_id": 5,
  "role_id": 3  // 1=Admin, 2=Player, 3=Organizer
}

// Remove Role from User
{
  "action": "remove-role",
  "user_id": 5,
  "role_id": 3
}
```

## Frontend Usage

### Import Auth Functions
```javascript
import { 
  login, 
  logout, 
  register,
  getCurrentUser,
  isAuthenticated,
  hasRole,
  isAdmin,
  isOrganizer,
  requestOrganizerRole
} from './core/auth.js';
```

### Check User Roles
```javascript
// Check if user is admin
if (isAdmin()) {
  // Show admin features
}

// Check specific role
if (hasRole('Organizer')) {
  // Show organizer features
}

// Get all user roles
const roles = getUserRoles();
roles.forEach(role => {
  console.log(role.role_name, role.description);
});
```

### Role-Based UI with HTML Attributes
```html
<!-- Show only to Admin -->
<div data-role="Admin">
  <h3>Admin Panel</h3>
  <p>Only admins can see this</p>
</div>

<!-- Show to Admin OR Organizer -->
<div data-roles="Admin,Organizer">
  <button>Create Tournament</button>
</div>

<!-- Display user role badges -->
<div id="user-role-badges"></div>
```

### Initialize Role-Based UI
```javascript
import { initializeRoleBasedUI } from './roleUtils.js';

// Automatically initializes on page load
// Or manually call:
initializeRoleBasedUI();
```

## Security Features

### ✅ Implemented
- **JWT Authentication** - Secure token-based authentication
- **Server-Side Sessions** - Session validation in database
- **Password Hashing** - BCRYPT algorithm
- **SQL Injection Protection** - PDO prepared statements
- **Input Sanitization** - htmlspecialchars and strip_tags
- **Role-Based Authorization** - Middleware checks on protected endpoints
- **Default Role Assignment** - New users automatically get Player role
- **Admin Approval Workflow** - Organizer role requires admin approval

### 🔒 Security Recommendations for Production
1. **HTTPS Only** - Use SSL/TLS in production
2. **HTTP-Only Cookies** - Store JWT in HTTP-only cookies instead of localStorage
3. **CSRF Protection** - Implement CSRF tokens for state-changing operations
4. **Rate Limiting** - Limit API requests to prevent brute force
5. **Token Expiry** - Tokens expire after 24 hours (configurable)
6. **Session Cleanup** - Run periodic cleanup of expired sessions
7. **Environment Variables** - Use .env files for sensitive configuration
8. **CORS Configuration** - Restrict Access-Control-Allow-Origin in production

## Testing Checklist

- [ ] Database tables created successfully
- [ ] First admin user created and can login
- [ ] New user registration assigns Player role automatically
- [ ] JWT token stored in localStorage after login
- [ ] Token included in Authorization header for protected endpoints
- [ ] Admin can access admin panel
- [ ] Non-admin cannot access admin panel (403 Forbidden)
- [ ] Player can request Organizer role
- [ ] Admin receives and can approve/reject role requests
- [ ] Approved request assigns Organizer role to user
- [ ] User roles display correctly in UI
- [ ] Role-based UI elements show/hide correctly
- [ ] Logout destroys session and clears localStorage

## Troubleshooting

### Issue: "Database connection failed"
- Check XAMPP MySQL is running
- Verify database name in `backend/api/database.php`
- Check credentials (default: root with no password)

### Issue: "Authentication required" on API calls
- Ensure JWT token is being sent in Authorization header
- Check token hasn't expired (24 hour default)
- Verify session exists in database

### Issue: "Insufficient permissions"
- Check user has the required role
- Verify role assignment in `user_roles` table
- Check middleware is correctly validating roles

### Issue: Admin panel not loading
- Ensure user has Admin role (role_id = 1)
- Check browser console for JavaScript errors
- Verify admin_api.php file permissions

## File Structure

```
backend/
├── api/
│   ├── auth_api.php          # Authentication endpoints with JWT
│   ├── admin_api.php          # Admin-only role management endpoints
│   └── database.php           # Database connection
├── classes/
│   ├── Auth.class.php         # Extended with role methods
│   ├── JWT.class.php          # JWT token generation & validation
│   └── Session.class.php      # Server-side session management
├── middleware/
│   └── auth_middleware.php    # JWT & role verification middleware
└── database/
    └── setup_roles.sql        # Database migration script

frontend/
├── app/views/pages/
│   └── admin/
│       └── role-management.php # Admin role management UI
└── src/js/
    ├── core/
    │   └── auth.js            # Extended with JWT & role methods
    ├── roleUtils.js           # Role-based UI utilities
    └── admin-role-management.js # Admin panel logic
```

## Next Steps

1. **Create Protected Routes** - Use middleware on tournament creation, editing, etc.
2. **Add Email Notifications** - Notify users when role requests are approved/rejected
3. **Implement Audit Logging** - Track role changes and admin actions
4. **Add Permission System** - Fine-grained permissions within roles
5. **Create Organizer Dashboard** - Separate interface for organizers
6. **Build Tournament Management** - Integrate role checks into tournament CRUD operations

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review browser console and PHP error logs
3. Verify all files are in the correct locations
4. Ensure database migrations ran successfully
