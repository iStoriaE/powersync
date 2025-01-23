# PowerSync Laravel Backend: Todo List Demo

## Overview

This repo contains a demo Laravel application which has HTTP endpoints to authorize a [PowerSync](https://www.powersync.com/) enabled application to sync data between a client device and a PostgreSQL database.

The endpoints are as follows:

1. GET `/api/auth/token`

    - PowerSync uses this endpoint to retrieve a JWT access token which is used for authentication.

2. GET `/api/auth/keys`

    - PowerSync uses this endpoint to validate the JWT returned from the endpoint above.

3. PUT `/api/data`

    - PowerSync uses this endpoint to sync upsert events that occurred on the client application.

4. PATCH `/api/data`

    - PowerSync uses this endpoint to sync update events that occurred on the client application.

5. DELETE `/api/data`

    - PowerSync uses this endpoint to sync delete events that occurred on the client application.

## Packages

[php-jwt](https://github.com/firebase/php-jwt) is used to encode and decode JSON Web Tokens (JWT) which PowerSync uses for authorization.

## Requirements

This app needs a Postgres instance that's hosted. For a free version for testing/demo purposes, visit [Supabase](https://supabase.com/).

## Running the app

1. Clone the repository
2. Generate keys pairs with openssl command. Ensure they are exists in the storage folder.

```shell
openssl genpkey -algorithm RSA -out storage/private.key -pkeyopt rsa_keygen_bits:2048
openssl rsa -pubout -in storage/private.key -out storage/public.key
```

3. Create a new `.env` file in the root project directory and add the variables as defined in the `.env` file:

```shell
cp .env.example .env
```

4. Install dependencies

```shell
composer i
```

### Change env variables as required.

```dotenv
POWERSYNC_PUBLIC_KEY=public.key
POWERSYNC_PRIVATE_KEY=private.key
POWERSYNC_KID=powersync-111
POWERSYNC_URL=https://111.powersync.journeyapps.com
```

## Start App

1. Run the following to start the application

```shell
php artisan serve
```

This will start the app on `http://127.0.0.1:8000`.

2. Test if the app is working by opening `http://127.0.0.1:8000/api/auth/token` in the Postman

3. You should get a JSON object as the response to that request

## Connecting the app with PowerSync

3. Open the [PowerSync Dashboard](https://powersync.journeyapps.com/) and paste the `Forwarding` URL starting with HTTPS into the Credentials tab of your PowerSync instance e.g.

```
JWKS URI
https://<YOUr-URL>/api/auth/keys
```

Pay special attention to the URL, it should include the `/api/auth/keys` path as this is used by the PowerSync server to validate tokens.
