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
	## Installation

## Architecture

This application is built with Laravel and follows a service-oriented and RESTful architecture:

- **Service Layer**: Business logic for searching and caching translations is handled by dedicated services (`TranslationSearchService`, `TranslationCacheService`).
- **Contracts/Repository Pattern**: Interfaces define expected behaviors for search and cache, making logic easily swappable.
- **Resource Classes**: API responses are formatted via Laravel Resources (`TranslationResource`, `LanguageResource`, `TagResource`).
- **Authentication**: Laravel Sanctum provides secure API token authentication for all endpoints.
- **RESTful Controllers**: CRUD operations are handled using Laravel's `apiResource` controllers.
- **Factory Usage**: Laravel model factories are used for efficient and unique test data generation.

This architecture ensures scalability, maintainability, and clean separation of concerns for translation management workflows.

## API Endpoints

### Authentication
| Endpoint | Description |
|----------|-------------|
| `POST /api/login` | Login and receive a token |
| `POST /api/logout` | Logout (requires authentication) |

### Languages
| Endpoint | Description |
|----------|-------------|
| `GET /api/languages` | List all languages |
| `POST /api/languages` | Create a new language |
| `GET /api/languages/{id}` | Get a specific language |
| `PUT /api/languages/{id}` | Update a language |
| `DELETE /api/languages/{id}` | Delete a language |

### Translations
| Endpoint | Description | Query Parameters |
|----------|-------------|------------------|
| `GET /api/translations` | List translations | `language_id`, `tags[]`, `search`, `per_page`, `page` |
| `POST /api/translations` | Create a new translation | — |
| `GET /api/translations/{id}` | Get a specific translation | — |
| `PUT /api/translations/{id}` | Update a translation | — |
| `DELETE /api/translations/{id}` | Delete a translation | — |
| `GET /api/translations/export` | Export translations (filtered) | `languages[]`, `tags[]` |

All endpoints (except login) require authentication via Laravel Sanctum.


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
