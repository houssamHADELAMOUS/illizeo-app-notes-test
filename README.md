# Notes App - Monorepo Setup

This repository contains both the backend (Laravel PHP) and frontend (React + Vite) for the Notes App.

---

## Prerequisites

- **PHP** >= 8.1
- **Composer**
- **Node.js** >= 16.x
- **npm** or **yarn**
- **MySQL** or compatible database

---

## Backend Setup (Laravel)

1. **Navigate to the backend folder:**
   ```sh
   cd notes-app-backend
   ```

2. **Install PHP dependencies:**
   ```sh
   composer install
   ```

3. **Copy the example environment file and configure:**
   ```sh
   cp .env.example .env
   # Edit .env to set your database and other environment variables
   ```

4. **Generate application key:**
   ```sh
   php artisan key:generate
   ```

5. **Run database migrations and seeders:**
   ```sh
   php artisan migrate --seed
   ```

6. **(Optional) Start the backend server:**
   ```sh
   php artisan serve
   # Default: http://localhost:8000
   ```

---

## Frontend Setup (React + Vite)

1. **Navigate to the frontend folder:**
   ```sh
   cd notes-app-frontend
   ```

2. **Install Node.js dependencies:**
   ```sh
   npm install
   # or
   yarn install
   ```

3. **Start the frontend development server:**
   ```sh
   npm run dev
   # or
   yarn dev
   # Default: http://localhost:5173
   ```

---

## Running Tests

### Backend (PHPUnit / Pest)

```sh
cd notes-app-backend
php artisan test
# or
vendor/bin/pest
```

### Frontend (if tests are available)

```sh
cd notes-app-frontend
npm test
# or
npm run test
```

---

## Project Structure

- `notes-app-backend/` — Laravel backend API
- `notes-app-frontend/` — React frontend app

---

## Additional Notes

- Make sure your backend `.env` is properly configured for your database and other services.
- The frontend expects the backend API to be running (update API URLs in frontend config if needed).
- For production, build the frontend with `npm run build` and deploy both apps as needed.

---




