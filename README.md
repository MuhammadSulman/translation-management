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

### Schedule Setup

To enable automatic news fetching, add the Laravel scheduler to your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Alternatively, you can manually run the fetch commands:

```bash
# Fetch trending news
php artisan news:fetch trending

# Fetch yesterday's news
php artisan news:fetch yesterday
```

## Architecture

The application uses several design patterns to ensure flexibility and scalability:

- **Strategy Pattern**: Each news source implements a common interface.
- **Factory Pattern**: Creates news source instances.
- **Adapter Pattern**: Standardizes different API responses.
- **Repository Pattern**: For data access and filtering.
- **Command Pattern**: For scheduled tasks.

This architecture makes it easy to add new sources without modifying existing code.

## Adding a New News Source

To add a new news source:

    1. Create a new class in `app/Services/NewsAggregator` that extends `AbstractNewsSource`.
    2. Implement the required methods (`fetchTrending`, `fetchYesterday`, `mapToArticleModel`, `getSourceIdentifier`).
    3. Add the new source to the `NewsSourceFactory`.
    4. Add the API key configuration to `config/news_sources.php`.

## API Endpoints

| Endpoint | Description | Query Parameters |
|----------|-------------|------------------|
| `GET /api/news` | Get articles with filters | search, source, category, date_from, date_to, per_page |
| `GET /api/news/categories` | Get available categories | None |
| `GET /api/news/sources` | Get available sources | None |

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
