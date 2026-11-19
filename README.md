# Snudens Paradis API

A Laravel REST API for managing products, product attributes, users, and orders.
The API uses Laravel Sanctum personal access tokens to authenticate protected
routes.

## Requirements

- PHP 8.3 or later
- Composer
- Node.js and npm (for the Vite frontend assets)

## Getting started

1. Install PHP dependencies:

   ```sh
   composer install
   ```

2. Create your local environment file and application key:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

   The example configuration uses SQLite. Ensure `database/database.sqlite`
   exists, or configure another database in `.env`.

3. Run migrations and, if you want sample data, seed the database:

   ```sh
   php artisan migrate
   # Optional:
   php artisan db:seed
   ```

4. Install and build frontend assets:

   ```sh
   npm install
   npm run build
   ```

5. Start the API:

   ```sh
   php artisan serve
   ```

The API is available at `http://localhost:8000/api`.

## API

All routes are prefixed with `/api`. Send and receive JSON using the
`Content-Type: application/json` and `Accept: application/json` headers.

### Public routes

| Method | Path | Description |
| --- | --- | --- |
| `POST` | `/login` | Authenticate and receive a Sanctum token |
| `POST` | `/register` | User registration route |
| `GET` | `/products` | List products and their attribute values |
| `GET` | `/products/{id}` | Get a product and its attribute values |
| `GET` | `/attributes-values` | List attribute values |
| `GET` | `/attributes-values/{id}` | Get an attribute value |

### Authenticated routes

Send the token returned by `/login` with protected requests:

```http
Authorization: Bearer <token>
```

| Method | Path | Description |
| --- | --- | --- |
| `POST` | `/logout` | Revoke the authenticated user's tokens |
| `POST` | `/products` | Create a product |
| `PUT` | `/products/{id}` | Update a product |
| `DELETE` | `/products/{id}` | Delete a product |
| `GET` | `/orders/{UID}` | List order items for a user |
| `POST` | `/orders/{UID}` | Create an order |
| `POST` | `/orders/{orderId}/items` | Add, update, or remove a product in an order |
| `POST` | `/attributes-values` | Create an attribute value |
| `PUT` | `/attributes-values/{id}` | Update an attribute value |
| `DELETE` | `/attributes-values/{id}` | Delete an attribute value |

The `/register` route currently points to a `register` controller action, while
`UserController` defines `signup`; align these names before relying on
registration.

### Login example

```sh
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"your-password"}'
```

The response contains the user and a `token` value. Include that value as a
Bearer token on authenticated requests.

## Database and sample data

Migrations create users, products, attributes, attribute values, orders, and
order items. Related product, attribute, and order records use cascading
foreign-key deletes.

`php artisan db:seed` creates sample users, products, attributes, and orders,
including `test@example.com`. The seeded account uses the factory's shared
development password; never use seeded accounts in production. Seeding again
without clearing the sample data may fail because the test email already
exists.

The users migration stops rather than converting or deleting an existing users
table. Back up the database before migrating. Rolling back the application
tables is blocked while users exist.

## Tests

Run the test suite with:

```sh
php artisan test
```
