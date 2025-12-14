# Tournament Management System

A modern tournament management system with user authentication featuring a dark neon theme.

## Features

- **User Authentication**: Login and registration with secure password hashing
- **Dark Neon Theme**: Modern UI with cyan and purple gradients
- **AJAX-Powered**: Dynamic content loading without page refresh
- **Responsive Design**: Built with Tailwind CSS
- **Secure**: Password hashing with bcrypt, input sanitization

## Project Structure

```
.
├── backend/
│   ├── api/
│   │   ├── auth_api.php      # Authentication API endpoint
│   │   └── database.php       # Database connection
│   └── classes/
│       └── Auth.class.php     # Authentication class
└── frontend/
    ├── app/
    │   └── views/
    │       ├── layout.php     # Main application layout
    │       ├── login.php      # Login form view
    │       └── register.php   # Registration form view
    └── src/
        ├── js/
        │   └── main.js        # AJAX and form handling
        ├── input.css          # Tailwind CSS input
        └── output.css         # Compiled CSS
```

## Setup

### Prerequisites

- PHP 8.0 or higher
- MySQL database
- Node.js and npm (for Tailwind CSS)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Tournament-Management-System
   ```

2. **Set up the database**
   - Create a MySQL database named `tournament_db`
   - Create a `users` table:
   ```sql
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(50) UNIQUE NOT NULL,
       email VARCHAR(100) UNIQUE NOT NULL,
       password VARCHAR(255) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

3. **Configure database connection**
   - Update `backend/api/database.php` with your database credentials if needed

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

## API Endpoints

### Authentication API (`backend/api/auth_api.php`)

#### Register User
```http
POST /backend/api/auth_api.php
Content-Type: application/json

{
  "action": "register",
  "username": "johndoe",
  "email": "john@example.com",
  "password": "securepassword"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully"
}
```

#### Login User
```http
POST /backend/api/auth_api.php
Content-Type: application/json

{
  "action": "login",
  "username": "johndoe",
  "password": "securepassword"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
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
