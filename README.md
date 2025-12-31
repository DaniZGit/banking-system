# 🏦 Banking API Core

This project provides the core backend services for a fictional banking application.

## Installation and Setup

Follow these steps to get the APP running locally.

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & pnpm (or npm/yarn)
- sqLite

### Steps

1.  **Clone the Repository**

    ```bash
    git clone https://github.com/DaniZGit/banking-system
    cd your-project-name
    ```

2.  **Install Dependencies**

    ```bash
    # Install Laravel/PHP dependencies
    composer install

    # Install Vue/Javascript dependencies
    pnpm install
    ```

3.  **Configure Environment**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Database Setup**

    ```bash
    # Create the database tables
    php artisan migrate
    ```

5.  **Start Servers**

    ```bash
    # Start the Laravel development server
    php artisan serve

    # Start the frontend watcher (if using a frontend)
    pnpm run dev
    ```

    The API will be available at `http://127.0.0.1:8000`.
