# Translation Managment Service Backend

This is a Laravel-based backend for a translation management system. It provides APIs to handle languages and translations, enabling seamless multilingual support for frontend applications.


## Features

- Language Management (CRUD) – Create, update, and manage supported languages.
- Translation Management (CRUD) – Add, edit, and organize translations for different languages.
- Translation Export (JSON) – Export translations in JSON format for seamless integration with frontend applications.

## Setup Instructions
### Prerequisites

- PHP 8.3 or higher
- Composer
- MySQL or compatible database
- Laravel requirements

### Installation

1. Clone the repository:

   ```bash
   git clone git@github.com:MuhammadSulman/translation-management.git
   cd translation-management
   ```

2. Install dependencies:

   ```bash
   composer install
   ```

3. Copy the environment file and configure your database:

   ```bash
   cp .env.example .env
   ```

4. Edit the `.env` file to set up your database connection and add your API keys:

   ```
	DB_CONNECTION=mysql
	DB_HOST=mysql
	DB_PORT=3306
	DB_DATABASE=translation_management
	DB_USERNAME=sail
	DB_PASSWORD=password
	FORWARD_DB_PORT=3307
   ```

5. Generate the application key:

   ```bash
   php artisan key:generate
   ```

6. Run the migrations:

   ```bash
   php artisan migrate --seed
   ```

7. Start the development server:

   ```bash
   php artisan serve
   ```
## Installation using laravel sail

Laravel sail already added in the project.

Ensure you have Docker installed on your machine before proceeding.

1. Install dependencies:

   ```bash
   composer install
   ```

2. Copy the environment file and configure your database:

   ```bash
   cp .env.example .env
   ```
3. Start Laravel Sail

    ```bash
   ./vendor/bin/sail up -d
   ```

4. SSH into laravel sail using below command

    ```bash
   ./vendor/bin/sail shell
   ```
5. Run the migrations:

   ```bash
   php artisan migrate --seed
   ```

## API Endpoints

| Endpoint | Description | Query Parameters/Form Data |
|----------|-------------|------------------|
| `POST /api/login` | Authentication | email, password
| `POST /api/logout` | Logout ||
| `GET /api/languages` | Get available languages | None |
| `POST /api/languages` | To create a new language record| `name, code` |
| `PUT /api/languages/{languages}` | To update existing record | None |
| `DELETE /api/languages/{languages}` | To delete the language record | None |
| `GET /api/translations` | Get available languages | None |
| `POST /api/translations` | To create a new language record| `{"key": "test 1", "value": "this is test value", "language_id":1, "tags": []}` |
| `PUT /api/translations/{translation}` | To update existing record | `{"key": "test 1", "value": "this is test value", "language_id":1, "tags": []}` |
| `DELETE /api/translations/{translation}` | To delete the translation record | None |

## API Documentation

Interactive API documentation is available via Swagger UI at:

```
http://localhost:8000/api/documentation
```

The Swagger documentation provides:
- Detailed parameter information
- Response schemas
- Interactive testing interface
- Example requests and responses

## Testing

Run the tests with:

```bash
php artisan test
```

See [TESTING.md](TESTING.md) for more information on the test suite.
