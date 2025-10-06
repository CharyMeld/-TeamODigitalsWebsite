# TeamO Digital Solutions - Staff Management System

A comprehensive Laravel-based staff database management system designed to streamline employee management, attendance tracking, leave requests, analytics, and more.

## Overview

This system provides role-based access control with tailored dashboards for different user roles including Superadmin, Admin, Developer, and Employee. Built with Laravel 11, Vue.js 3, and Inertia.js for a modern, reactive user experience.

## Features

### Core Functionality
- **User Authentication & Authorization** - Laravel Jetstream with Fortify
- **Role-Based Access Control** - Four distinct user roles (Superadmin, Admin, Developer, Employee)
- **Dynamic Menu System** - Role-based menu permissions and customizable navigation
- **Profile Management** - Comprehensive user profiles with extended fields

### Staff Management
- **Attendance Tracking** - Sign-in/Sign-out with time tracking
- **Leave Request Management** - Submit, approve, and track leave requests
- **Task Assignment** - Assign and monitor tasks across teams
- **Work Reports** - Daily work report submission and tracking

### Analytics & Reporting
- **Dashboard Analytics** - Real-time insights and statistics
- **Report Generation** - Automated analytics reports with email delivery
- **SEO Analytics** - Blog and content performance tracking
- **Export Functionality** - CSV, Excel, and PDF exports

### Content Management
- **Blog Management** - Full-featured blog with SEO optimization
- **Job Vacancies** - Post and manage job openings
- **Job Applications** - Track and manage applicants
- **Gallery Management** - Image and media management

### Additional Features
- **Social Login** - Google, Facebook authentication support
- **Email Notifications** - Automated notifications and alerts
- **Two-Factor Authentication** - Enhanced security
- **API Token Management** - Sanctum-based API authentication
- **Docker Support** - Container-ready deployment

## Technologies Used

- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Vue.js 3, Inertia.js, Tailwind CSS
- **Database:** MySQL
- **Authentication:** Laravel Jetstream, Fortify, Sanctum
- **Build Tools:** Vite
- **PDF Generation:** DomPDF
- **Permissions:** Spatie Laravel Permission

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL database
- XAMPP/LAMPP (for local development)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/CharyMeld/-TeamODigitalsWebsite.git
   cd -TeamODigitalsWebsite
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your database** in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Set up the menu system**
   ```bash
   php artisan menu:setup
   ```

8. **Build frontend assets**
   ```bash
   npm run build
   # For development:
   npm run dev
   ```

9. **Create storage link**
   ```bash
   php artisan storage:link
   ```

10. **Upload images** (not included in repository)
    - Place your images in `public/images/`
    - Gallery images in `storage/gallery/`
    - Testimonial images in `storage/testimonials/`

11. **Start the development server**
    ```bash
    php artisan serve
    ```

Visit `http://localhost:8000` in your browser.

## Default Credentials

After running the seeder, you can log in with:
- Check your database seeders for default user credentials

## User Roles

- **Superadmin** - Full system access, user management, job postings, system settings
- **Admin** - Employee management, attendance, leave approvals, reports, analytics
- **Developer** - Database management, system logs, technical administration
- **Employee** - Personal attendance, leave requests, work reports, profile management

## Project Structure

```
├── app/
│   ├── Actions/          # Fortify actions
│   ├── Console/          # Artisan commands
│   ├── Http/
│   │   ├── Controllers/  # Application controllers
│   │   ├── Middleware/   # Custom middleware
│   │   └── Requests/     # Form requests
│   ├── Models/           # Eloquent models
│   ├── Policies/         # Authorization policies
│   └── Services/         # Business logic services
├── database/
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── resources/
│   ├── js/
│   │   ├── Components/   # Vue components
│   │   ├── Layouts/      # Page layouts
│   │   └── Pages/        # Inertia pages
│   └── views/            # Blade templates
└── routes/               # Application routes
```

## Available Commands

```bash
# Setup menu system
php artisan menu:setup

# Check user access
php artisan user:check-access {email}

# Run tests
php artisan test
```

## Configuration Files

- **Leave Management:** `config/leave.php`
- **Departments:** `config/departments.php`
- **Menu System:** Managed via database and `MenuService`

## Documentation

Additional documentation is available in the project:
- `DYNAMIC_MENU_SYSTEM.md` - Menu system documentation
- `EMAIL_SETUP_GUIDE.md` - Email configuration guide
- `SEO_ANALYTICS_GUIDE.md` - SEO and analytics setup
- `QUICK_START_LOGIN.md` - Login system guide

## Docker Deployment

Docker configurations are available for PHP 8.0, 8.1, 8.2, 8.3, and 8.4:

```bash
docker-compose up -d
```

## Contributing

Contributions are welcome! Please submit pull requests or open issues for any improvements.

## Security

If you discover any security vulnerabilities, please email the development team immediately.

## License

This project is proprietary software developed for TeamO Digital Solutions.

## Support

For questions or support, contact TeamO Digital Solutions.

---

**Developed with Laravel** - Built using Laravel 11, Vue.js 3, and modern web technologies.
