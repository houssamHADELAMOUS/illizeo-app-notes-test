# Tenant Notes App

A multi-tenant SaaS-style Notes application built with **Laravel 12** (Backend) and **React 19** (Frontend). Each company (tenant) can register, and their users can create and manage notes that are visible only to users from the same tenant.

---

## Features

- **Multi-Tenancy**: Subdomain-based tenant isolation using [stancl/tenancy](https://tenancy.dev/)
- **Authentication**: Token-based authentication with Laravel Sanctum
- **Notes Management**: Full CRUD operations (Create, Read, Update, Delete)
- **Tenant Isolation**: Users can only access notes belonging to their own company
- **Modern UI**: Built with Tailwind CSS and React Query for optimistic updates
- **Form Validation**: Client-side validation using Formik + Yup

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | Laravel 12, PHP 8.2+ |
| **Frontend** | React 19, Vite 7 |
| **Authentication** | Laravel Sanctum |
| **Multi-Tenancy** | stancl/tenancy ^3.9 |
| **Styling** | Tailwind CSS 4 |
| **State Management** | TanStack React Query |
| **Form Handling** | Formik + Yup |
| **HTTP Client** | Axios |

---

## Project Structure

```
Illizeo-test/
├── notes-app-backend/      # Laravel API Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/    # AuthController, NoteController
│   │   │   ├── Middleware/     # Tenant middleware
│   │   │   └── Requests/       # Form request validation
│   │   ├── Models/             # User, Note, Company, Tenant
│   │   ├── Services/           # Business logic layer
│   │   └── Repositories/       # Data access layer
│   ├── config/
│   │   └── tenancy.php         # Multi-tenancy configuration
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/            # Demo data seeders
│   └── routes/
│       └── api.php             # API routes
│
├── notes-app-frontend/     # React Frontend
│   ├── src/
│   │   ├── components/         # Reusable components
│   │   ├── context/            # Auth context provider
│   │   ├── pages/              # Login, Register, Dashboard
│   │   ├── services/           # API service layer
│   │   └── utils/              # Subdomain utilities
│   └── vite.config.js
│
└── README.md
```

---

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 18.x
- **npm** >= 9.x or **yarn**
- **MySQL** >= 8.0 (or MariaDB/PostgreSQL)

---

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd Illizeo-test
```

### 2. Backend Setup (Laravel)

```bash
# Navigate to backend directory
cd notes-app-backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Environment Variables

Edit the `.env` file with your database credentials:

```env
# Application
APP_NAME="Tenant Notes App"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=notes_app
DB_USERNAME=root
DB_PASSWORD=your_password

# Session & Sanctum
SESSION_DRIVER=database
SESSION_DOMAIN=.localhost
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000,*.localhost:3000

# Cache & Queue
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 4. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed demo data (companies, users, notes)
php artisan db:seed
```

### 5. Frontend Setup (React)

```bash
# Navigate to frontend directory
cd ../notes-app-frontend

# Install Node.js dependencies
npm install

# Or using yarn
yarn install
```

---

## Running the Application

### Start Backend Server

```bash
cd notes-app-backend
php artisan serve
# Server runs at http://localhost:8000
```

### Start Frontend Development Server

```bash
cd notes-app-frontend
npm run dev
# Server runs at http://localhost:3000
```

---

## Subdomain Configuration (Local Development)

To test subdomain-based multi-tenancy locally, you need to configure your hosts file.

### Windows

1. Open Notepad **as Administrator**
2. Open file: `C:\Windows\System32\drivers\etc\hosts`
3. Add the following lines:

```
127.0.0.1   localhost
127.0.0.1   acme.localhost
127.0.0.1   techstart.localhost
127.0.0.1   hdscoders.localhost
```

### macOS / Linux

```bash
sudo nano /etc/hosts
```

Add the same lines as above.

### After Configuration

You can access tenants via:
- `http://acme.localhost:3000` - Acme Corp tenant
- `http://techstart.localhost:3000` - TechStart Inc tenant
- `http://hdscoders.localhost:3000` - HDSCoders tenant

---

## Demo Accounts

After running seeders (`php artisan db:seed`), the following test accounts are available:

### Acme Corp (subdomain: `acme`)
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@acme.localhost | password |
| User | user@acme.localhost | password |

### TechStart Inc (subdomain: `techstart`)
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@techstart.localhost | password |
| User | user@techstart.localhost | password |

### HDSCoders (subdomain: `hdscoders`)
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@hdscoders.localhost | password |
| User | user@hdscoders.localhost | password |

---

## API Endpoints

All API routes are prefixed with `/api`

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/register` | Register new company + user | No |
| POST | `/api/login` | Login to tenant | No (requires X-Tenant header) |
| POST | `/api/logout` | Logout user | Yes |

### Notes

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/notes` | List all notes for current user | Yes |
| POST | `/api/notes` | Create a new note | Yes |
| GET | `/api/notes/{id}` | Get a specific note | Yes |
| PUT | `/api/notes/{id}` | Update a note | Yes |
| DELETE | `/api/notes/{id}` | Delete a note | Yes |

### Request Headers

All authenticated requests require:

```
Authorization: Bearer {token}
X-Tenant: {subdomain}
```

---

## Multi-Tenancy Architecture

### How It Works

1. **Tenant Identification**: The `X-Tenant` header identifies which tenant the request belongs to
2. **Middleware**: `CustomInitializeTenancyBySubdomain` middleware initializes the tenant context
3. **Data Isolation**: All queries are automatically scoped to the current tenant via `tenant_id`
4. **Security**: Users cannot access data from other tenants

### Registration Flow

1. User submits registration with company name and subdomain
2. System creates a new Tenant record
3. Domain mapping is created (e.g., `acme.localhost`)
4. User account is created with `tenant_id`
5. Authentication token is returned

### Login Flow

1. User provides email, password, and subdomain
2. System identifies tenant from subdomain
3. Credentials are validated within tenant context
4. Token is issued for authenticated requests

---

## Testing

### Backend Tests (Pest/PHPUnit)

```bash
cd notes-app-backend

# Run all tests
php artisan test

# Run with Pest directly
./vendor/bin/pest

# Run specific test file
php artisan test --filter=AuthTest
```

### Frontend Build

```bash
cd notes-app-frontend

# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint
```

---

## Troubleshooting

### Common Issues

#### 1. "No X-Tenant header" error
**Solution**: Ensure you're accessing the app via a subdomain (e.g., `acme.localhost:3000`) or the login form includes the company subdomain.

#### 2. CORS errors
**Solution**: Check that `SANCTUM_STATEFUL_DOMAINS` in `.env` includes your frontend URL:
```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,*.localhost:3000
```

#### 3. Subdomain not working locally
**Solution**:
- Verify hosts file is configured correctly
- Clear browser cache
- Try incognito/private browsing mode

#### 4. Database connection refused
**Solution**: Ensure MySQL is running and credentials in `.env` are correct:
```bash
# Check MySQL status
mysql -u root -p -e "SELECT 1"
```

#### 5. "Class not found" errors
**Solution**: Regenerate autoload files:
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

---

## Production Deployment

### Backend

```bash
cd notes-app-backend

# Install dependencies (no dev)
composer install --no-dev --optimize-autoloader

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
```

### Frontend

```bash
cd notes-app-frontend

# Build for production
npm run build

# Output will be in dist/ folder
```

### Environment Variables (Production)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

SESSION_DOMAIN=.yourdomain.com
SANCTUM_STATEFUL_DOMAINS=*.yourdomain.com
```

---

## Security Features

- **Tenant Isolation**: Data is strictly separated by `tenant_id`
- **Token Authentication**: Secure Bearer token authentication via Sanctum
- **Input Validation**: Server-side validation on all endpoints
- **CSRF Protection**: Built-in Laravel CSRF protection
- **Password Hashing**: Bcrypt hashing with configurable rounds

---

## License

This project is created for the Illizeo Technical Test.

---

## Author

Built with Laravel and React.js for the Multi-Tenant Developer Assessment.
