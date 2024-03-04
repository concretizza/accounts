# Accounts

## Responsibilities

- Users
- Subscriptions

## Docs

The API definition can be found in the [docs](./docs) folder.

## Environment variables

:warning: Create `.env` file from the `.env.example`

Generate app key

```sh
php artisan key:generate
```

## Storage

Set permissions to `storage` and `public` folders

```sh
chmod -R 777 storage public
```

Create symbolic link from `storage` to `public` folders

```sh
php artisan storage:link
```

## Database

To create the tables

```sh
php artisan migrate
```

It will recreate and populate the tables

```sh
php artisan migrate:fresh --seed
```

### Create migration

```sh
php artisan make:migration <name>
```

### Alter migration specifying table

```sh
php artisan make:migration <name> --table=<table>
```

## Tests

Running API tests

```sh
php artisan test
```

Running API tests specifying environment

```sh
php artisan test --env=testing
```

For integration tests use the `--env=integrations`

Update the `.env` with `APP_ENV=integrations`

Run tests using filter to specify tests to run

```sh
php artisan test --filter AddressTest
```

To create a test

```sh
php artisan make:test AddressTest
```

### E2E tests

Setup is only necessary for a fresh application.

```sh
php artisan dusk:install
```

If already installed, we need to install the **chrome driver**.

```sh
php artisan dusk:chrome-driver
```

Run the E2E test using integrations environment

```sh
php artisan dusk --env=integrations
```

## Queue

```sh
php artisan queue:table
```

```sh
php artisan migrate
```

```sh
php artisan queue:work --tries=3 --queue=default
```

```sh
php artisan queue:work --queue=default,high
```

```sh
php artisan queue:work --queue=default,high --timeout=900
```

## Stripe

```sh
stripe listen --forward-to http://localhost:8000/api/payments/stripe/webhooks
```

## Code style

```sh
./vendor/bin/pint
```

## Email server

https://github.com/axllent/mailpit

## Tags

Create a tag
```sh
git tag v0.0.1
```

Tag with comment
```sh
git tag -a v0.0.1 -m "Account, users and subscriptions"
```

Push the tag
```sh
git push origin v0.0.1
```

Push all at once
```sh
git push origin --tags
```

Show all or specific tag
```sh
git tag
git show v0.0.1
```

Delete tag
```sh
git tag -d v0.0.1
```

## Private and public keys

Private key

```sh
openssl genpkey -algorithm RSA -out private_key.pem -pkeyopt rsa_keygen_bits:2048
```

Public key

```sh
openssl rsa -pubout -in private_key.pem -out public_key.pem
```