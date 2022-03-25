# Foodtruck reservations

## 1. Endpoints

URL | Method | Desciption 
-- | -- | -- 
/api/signup | POST | Registration with unique email and hashed password
/api/login_check | POST | Allow registered user to sign in via JWT authentication
/api/users/ | GET | Returns informations from logged user 
/api/reservations | POST | Add a reservation from logged user
/api/reservations | GET | Get all reservations from logged users
/api/reservations/{id} | GET | Get one specific reservation (after checking the logged user)

## 2. MCD
![image info](./Users.svg)

## 3. How to install
* Clone the repository & run

```bash
composer install
```

* Specify your work environment

```php
DATABASE_URL="mysql://user_name:password@127.0.0.1:3306/database_name?serverVersion=mariadb-10.3.25"
JWT_PASSPHRASE=e65cbbbe71ea5677b6199c1821c26d0b
APP_ENV=dev
```

* Migrate database 
```bash
php bin/console doctrine:database:create
```

```bash
php bin/console doctrine:migrations:migrate
```

* Generate a keypair for lexik (JWT authentication)
```bash
php bin/console lexik:jwt:generate-keypair
```
