[![Latest Stable Version](https://poser.pugx.org/ionghitun/lumen-start-app/v/stable)](https://packagist.org/packages/ionghitun/lumen-start-app)
[![Build Status](https://travis-ci.com/ionghitun/lumen-start-app.svg?branch=master)](https://travis-ci.com/ionghitun/lumen-start-app)
[![Total Downloads](https://poser.pugx.org/ionghitun/lumen-start-app/downloads)](https://packagist.org/packages/ionghitun/lumen-start-app)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ionghitun/lumen-start-app/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ionghitun/lumen-start-app/?branch=master)
[![License](https://poser.pugx.org/ionghitun/lumen-start-app/license)](https://packagist.org/packages/ionghitun/lumen-start-app)

# Lumen start kit

Boilerplate for laravel/lumen framework https://github.com/laravel/lumen.

## Instalation notes

`$ composer create-project --prefer-dist ionghitun/lumen-start-app blog`

## Dependencies

- php >= 7.2

## Documentation

Official documentation for the framework can be found on the https://lumen.laravel.com/docs.

The project is docker ready via docker-compose, `.env.example` contains default configuration for docker.

**Features**

1. Same json response structure when in production.
2. Valid for CORS.
3. GDPR compliant, sensitive fields are encrypted in database, anonymize data using https://github.com/ionghitun/laravel-lumen-mysql-encryption.
4. Translations ready, application who consume this api can add their own texts.
5. Error management, any error is catch.
6. User registration with account activation (user will receive an email with code), resend activation code.
7. User login with possibility to be remembered, login generates a JWT token using https://github.com/ionghitun/jwt-token.
8. Login with social: facebook, twitter, google.
9. Forgot password (user will receive an email with code and will have to use that code to change password).
10. Update user profile with change email (a confirmation code with be sen on email).
11. Change user picture.
12. Notifications with emitting event (broadcast).
13. Started CRUD example (users tasks).
