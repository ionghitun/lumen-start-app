# Lumen start kit #

**Requirments:**

- server with docker

**Instalation notes:**

Optional use: https://github.com/ionghitun/nginx-proxy/releases

- docker configuration
    - make a copy of `.env.example` into `.env` and edit it to match your environment
    - add certificates in `ssl` folder and name them `domain.crt` and `domain.key`
      
          a. Example: `local.ro.crt` and `local.ro.key`
          
          b. For local development you can use self signed certificates:
          https://www.selfsignedcertificate.com/
          
    - run `docker-compose up -d` in project directory
    - access php container, default `docker exec -it api_php bash`
    - run `composer install` inside container
    - run `php artisan migrate --seed` inside container
    - optional add DNS configuration: on localhost: `127.0.0.1 environment-link`
    - check everything is ok, open browser and navigate to environment link, default `https://api.local.ro`
    
    To access mysql databasse for example in HeidiSQL use:
    
        - host: localhost
        - user: root
        - pass: the one defined in docker-compose.yml file
        - port: check the one exposed on container running docker ps, you can preset one.

**Features - TO BE UPDATED**

*Done*
1. Postman collection for application routes available in `docs/postman_collection.json`
2. Same json response structure when in production, possible errors described in `docs/errors.yml`
3. User registration with account activation (user will receive an email with code), resend activation code
4. User login with possibility to be remembered, login generates a JWT token
5. Login with facebook
6. Forgot password (user will receive an email with code and will have to use that code to change password)
7. Update profile with change email (a confirmation code with be sen on email)
8. Update user picture
9. Valid for CORS
10. GDPR compliant, sensitive fields are encrypted in database
11. Notifications with emitting event

*Planned*
1. Add a CRUD example (user tasks) to show full potential of the application
2. A way to use translations, on emails also
3. Add more security to remember token
4. Error management, log errors in a more efficient way
5. Add user activity logs
6. Add a way for some results of the database to be cached/retrived from cache
7. Automatically generate crontab for docker, add configuration for server
8. Unit and functional testing
