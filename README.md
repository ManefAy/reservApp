# ReservApp

A web application for booking meeting rooms, built with PHP (MVC) and MySQL.
It has three roles: **administrator**, **employee** and **member**. Each role has its own dashboard and permissions.

## Features

### Authentication & security

- Login with username/password or **Sign in with Google** (OAuth 2.0)
- Public sign-up for members
- **Two-factor authentication (OTP by email)** for administrators
- **Email alert** when someone logs in from a new IP address
- Password reset via an emailed link (token expires)
- Passwords hashed with bcrypt

### Members & employees

- Browse rooms, with search and filter by type
- Book a room for a date and time slot (time slots already taken or in the past are blocked)
- **Confirmation email with a QR code** for each reservation
- View and cancel reservations (cancellation allowed up to 24h before)
- Visual **calendar** of reservations (FullCalendar)
- Profile page: edit info, change password, upload a profile photo (members)
- Leave a review (rating and comment)
- **AI chatbot** (Google Gemini) that answers questions about the app (members)

### Administrator

- Dashboard with statistics and a chart of reservations over the last 7 days (Chart.js)
- Manage rooms (add / edit / delete)
- Manage employees (add / edit / delete)
- View all reservations with filters (room, date, user, status) and cancel them
- Moderate reviews

## Tech stack

- **Backend:** PHP 8 (custom MVC, PDO)
- **Database:** MySQL
- **Libraries:** [PHPMailer](https://github.com/PHPMailer/PHPMailer), [PHP QR Code](https://phpqrcode.sourceforge.net/)
- **Frontend:** HTML, CSS, JavaScript, FullCalendar, Chart.js
- **APIs:** Google OAuth 2.0, Google Gemini

## Project structure

```
├── chatbot/        # Gemini chatbot (endpoint, widget, knowledge base)
├── config/         # Database, mail and API configuration
├── controllers/    # Application logic
├── libs/           # PHPMailer, PHP QR Code
├── models/         # Database access
├── public/         # CSS, JS, images, generated QR codes, uploads
├── views/          # Pages (admin, employe, membre, auth, ...)
├── database.sql    # Database structure
└── index.php       # Router (index.php?page=...)
```

## Installation

### Requirements

- WAMP / XAMPP (or any PHP 8+ and MySQL server)
- PHP extensions: `pdo_mysql`, `gd`, `fileinfo`, `openssl`

### Steps

1. **Clone the project** into your web server folder (e.g. `C:\wamp64\www\`):

   ```bash
   git clone https://github.com/ManefAy/reservApp.git projet
   ```
2. **Create the database.** In phpMyAdmin, create a database named `gestion_reservations` and import `database.sql`.
3. **Configure the database connection** in `config/database.php` (host, port, user, password).
4. **Configure the API keys.** Copy `config/config.example.php` to `config/config.php` and fill in:

   - `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET`: from [Google Cloud Console](https://console.cloud.google.com/apis/credentials) (OAuth client ID, type "Web application")
   - `GOOGLE_REDIRECT_URI`: e.g. `http://localhost/projet/index.php?page=google_callback` (it must also be added in Google Cloud Console)
   - `GEMINI_API_KEY`: from [Google AI Studio](https://aistudio.google.com/apikey)
5. **Configure email.** Copy `config/mail.example.php` to `config/mail.php` and fill in your SMTP settings.
   For Gmail, enable 2-step verification and create an [app password](https://myaccount.google.com/apppasswords).
6. **Open the app** at `http://localhost/projet/`.

### Create the first administrator

1. Sign up a normal account from the registration page.
2. In phpMyAdmin, run:
   ```sql
   UPDATE utilisateurs SET role = 'administrateur' WHERE email = 'your@email.com';
   ```
3. Log in. A verification code will be sent to that email.

Employees are created by the administrator from the **Employees** page.
