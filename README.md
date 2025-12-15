# Tournament Management System

A modern tournament management system with role-based access control, JWT authentication, and a dark neon theme.

## Features

### Tournament Management
- **Tournament Creation**: Organizers can create tournaments with custom configurations
- **Multiple Formats**: Single elimination, double elimination, round robin, Swiss, and custom formats
- **Registration Management**: Configurable registration requirements and deadlines
- **Organizer Dashboard**: Dedicated interface for organizers to manage their tournaments
  - View all created tournaments with participant statistics
  - Manage participant registrations (approve/reject)
  - View and manage teams (for team-based tournaments)
  - Real-time participant status tracking
- **Match Tracking**: Complete match result tracking and verification system
- **Prize Management**: Flexible prize pool setup with multiple prize types (cash, trophies, medals, points)
- **Standings & Leaderboard**: Real-time tournament standings and rankings
- **Tournament Status**: Full lifecycle management (draft, open, ongoing, completed, cancelled)
- **Team Support**: Optional team tournament functionality
- **Notifications**: Tournament announcements and updates system with modern toast notifications
- **Activity Logging**: Complete audit trail for all tournament actions

### Authentication & Security
- **JWT Authentication**: Secure token-based authentication with server-side session validation
- **Role-Based Access Control (RBAC)**: Three-tier role system (Admin, Organizer, Player)
- **Secure Password Storage**: Password hashing with bcrypt
- **Session Management**: Server-side session tracking with IP and user agent validation
- **Input Sanitization**: Protection against SQL injection and XSS attacks

### User Roles
- **Admin**: Full system access, role management, approve organizer requests
- **Organizer**: Create and manage tournaments (requires admin approval)
- **Player**: Default role for new users, participate in tournaments

### UI/UX
- **Dark Neon Theme**: Modern UI with cyan and purple gradients
- **Role-Based UI**: Dynamic content visibility based on user permissions
- **Responsive Design**: Built with Tailwind CSS and Bootstrap
- **Admin Dashboard**: Complete role management interface
- **AJAX-Powered**: Dynamic content loading without page refresh

## Project Structure

```
.
├── backend/
│   ├── api/
│   │   ├── auth_api.php           # Authentication API with JWT
│   │   ├── admin_api.php          # Admin role management API
│   │   └── database.php           # Database connection
│   ├── classes/
│   │   ├── Auth.class.php         # Authentication with role methods
│   │   ├── JWT.class.php          # JWT token generation/validation
│   │   └── Session.class.php      # Server-side session management
│   ├── middleware/
│   │   └── auth_middleware.php    # JWT & role verification
│   ├── database/
│   │   └── setup_roles.sql        # Database migration script
│   └── verify-setup.php           # Setup verification tool
├── frontend/
│   ├── app/views/pages/
│   │   ├── auth/
│   │   │   ├── login.php          # Login page
│   │   │   └── register.php       # Registration page
│   │   ├── home/
│   │   │   ├── dashboard.php      # User dashboard
│   │   │   ├── profile.php        # User profile with role request
│   │   │   └── tournaments.php    # Tournament listing
│   │   └── admin/
│   │       └── role-management.php # Admin role management
│   └── src/
│       ├── js/
│       │   ├── core/
│       │   │   └── auth.js        # Auth functions with JWT & roles
│       │   ├── roleUtils.js       # Role-based UI utilities
│       │   ├── admin-role-management.js # Admin panel logic
│       │   └── main.js            # Main application logic
│       ├── input.css              # Tailwind CSS input
│       └── output.css             # Compiled CSS
├── SETUP_GUIDE.md                 # Complete setup guide
├── ROLE_REFERENCE.md              # Quick reference for developers
└── IMPLEMENTATION_SUMMARY.md      # Implementation overview
```

## Quick Start

### Prerequisites

- PHP 8.0 or higher
- MySQL/MariaDB
- Node.js and npm (for Tailwind CSS)
- XAMPP or similar local server

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Tournament-Management-System
   ```

2. **Set up the database**
   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE tournament_db;"
   
   # Run migration scripts
   mysql -u root -p tournament_db < backend/database/setup_roles.sql
   mysql -u root -p tournament_db < backend/database/tournament_management.sql
   ```
   
   Or use phpMyAdmin:
   - Create database: `tournament_db`
   - Import: `backend/database/setup_roles.sql`
   - Import: `backend/database/tournament_management.sql`

3. **Configure database connection** (if needed)
   - Update `backend/api/database.php` with your credentials
   - Default: `root` user with no password

4. **Install frontend dependencies**
   ```bash
   cd frontend
   npm install
   npm run build  # Compile Tailwind CSS
   ```

5. **Start your server**
   - Start XAMPP (Apache + MySQL)
   - Access: `http://localhost/Tournament-Management-System/frontend/app/views/pages/auth/login.php`

6. **Create first admin user**
   ```bash
   # 1. Register a user through the app
   # 2. Make them admin:
   mysql -u root -p tournament_db -e "INSERT INTO user_roles (user_id, role_id) VALUES (1, 1);"
   ```

7. **Verify setup**
   - Visit: `http://localhost/Tournament-Management-System/backend/verify-setup.php`
   - Check that all tables exist and files are in place
   - Delete `verify-setup.php` after verification

8. **Verify tournament database**
   ```bash
   # Run validation script
   cd backend/database
   chmod +x validate_schema.sh
   ./validate_schema.sh
   ```
   
   Or check manually in MySQL:
   ```sql
   SHOW TABLES LIKE 'tournament%';
   SHOW TABLES LIKE 'match%';
   ```

### First Steps

1. **Register** a new user (auto-assigned Player role)
2. **Login** and explore the dashboard
3. **Make yourself admin** (see step 6 above)
4. **Access admin panel**: `/frontend/app/views/pages/admin/role-management.php`
5. **Request organizer role** from your profile page if you're a player
6. **Create tournaments** once you have the Organizer role

## Documentation

- **📘 [Setup Guide](SETUP_GUIDE.md)** - Detailed installation and configuration
- **📗 [Quick Reference](ROLE_REFERENCE.md)** - Code snippets and API examples
- **📙 [Implementation Summary](IMPLEMENTATION_SUMMARY.md)** - Overview of what's been built
- **🏆 [Tournament Setup Guide](backend/database/TOURNAMENT_SETUP_README.md)** - Tournament database integration guide
- **👤 [Organizer Guide](ORGANIZER_GUIDE.md)** - Guide for managing tournaments as an organizer

4. **Install frontend dependencies**
   ```bash
   cd frontend
   npm install
   ```

5. **Build CSS (if you make changes)**
   ```bash
   npm run build
   # or
   npx tailwindcss -i ./src/input.css -o ./src/output.css --minify
   ```

### Running the Application

1. **Start PHP server**
   ```bash
   cd frontend/app/views
   php -S localhost:8000
   ```

2. **Access the application**
   - Open your browser and navigate to `http://localhost:8000/layout.php`

## Usage

### Register a New Account
1. Click the "Register" button in the navigation
2. Fill in username, email, and password
3. Click "Register" to create your account
4. You'll be redirected to login after successful registration

### Login
1. Click the "Login" button in the navigation
2. Enter your username and password
3. Click "Login" to access your account
4. Upon successful login, you'll see a welcome message with your user details

## Role System

### User Roles

| Role | ID | Description | Access Level |
|------|----|-----------|----|
| **Admin** | 1 | System administrator | Full access, role management |
| **Player** | 2 | Regular user | Default role, participate in tournaments |
| **Organizer** | 3 | Tournament organizer | Create/manage tournaments (requires approval) |

### Role Assignment Flow

1. **New User Registration** → Auto-assigned **Player** role
2. **Player requests Organizer role** → Creates pending request
3. **Admin reviews request** → Approves or rejects
4. **If approved** → User gains **Organizer** role
5. **Admin can manually** → Assign/remove any role

### Frontend Usage

```javascript
import { hasRole, isAdmin, isOrganizer } from './core/auth.js';

// Check roles
if (isAdmin()) {
    // Admin-only features
}

if (hasRole('Organizer')) {
    // Organizer features
}
```

### HTML Role Attributes

```html
<!-- Admin only -->
<div data-role="Admin">Admin panel</div>

<!-- Multiple roles -->
<div data-roles="Admin,Organizer">Create Tournament</div>

<!-- Role badges -->
<div id="user-role-badges"></div>
```

### Backend Protection

```php
require_once '../middleware/auth_middleware.php';

$auth = getAuthMiddleware();

// Require admin
$user = $auth->requireRole('Admin');

// Require one of multiple roles
$user = $auth->requireRole(['Admin', 'Organizer']);
```

## API Endpoints

### Authentication (`/backend/api/auth_api.php`)

**Register User**
```json
POST /auth_api.php
{
  "action": "register",
  "username": "john_doe",
  "email": "john@example.com",
  "password": "password123"
}

Response:
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJh...",
  "user": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "roles": [
      {"id": 2, "role_name": "Player", "description": "Regular player"}
    ]
  }
}
```

**Login**
```json
POST /auth_api.php
{
  "action": "login",
  "username": "john_doe",
  "password": "password123"
}

Response: Same as register
```

**Request Organizer Role**
```json
POST /auth_api.php
Headers: Authorization: Bearer <token>
{
  "action": "request-organizer-role",
  "reason": "I want to organize tournaments"
}
```

### Admin API (`/backend/api/admin_api.php`)
*Requires Admin role*

**Get Pending Requests**
```
GET /admin_api.php?action=pending-requests
Headers: Authorization: Bearer <token>
```

**Approve Request**
```json
POST /admin_api.php
Headers: Authorization: Bearer <token>
{
  "action": "approve-request",
  "request_id": 1
}
```

**Assign Role**
```json
POST /admin_api.php
Headers: Authorization: Bearer <token>
{
  "action": "assign-role",
  "user_id": 5,
  "role_id": 3
}
```
    "username": "johndoe",
    "email": "john@example.com"
  }
}
```

## Technology Stack

- **Backend**: PHP 8.0+, MySQL
- **Frontend**: HTML5, JavaScript (ES6+), Tailwind CSS v4
- **Security**: Password hashing with bcrypt, input sanitization
- **Architecture**: RESTful API, AJAX-based SPA

## Security Notes

- Passwords are hashed using PHP's `password_hash()` with bcrypt
- User input is sanitized with `htmlspecialchars()` and `strip_tags()`
- API uses JSON for data exchange
- **Note**: Current implementation stores user data in localStorage for demonstration. In production, use secure httpOnly cookies or server-side sessions.

## Development

### Building Tailwind CSS
After making changes to the styles or HTML:
```bash
cd frontend
npx tailwindcss -i ./src/input.css -o ./src/output.css --minify
```

### File Watching (Development)
```bash
cd frontend
npx tailwindcss -i ./src/input.css -o ./src/output.css --watch
```

## License

This project is open source and available under the MIT License.
