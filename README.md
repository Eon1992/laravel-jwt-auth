Step 1. Install laravel

composer create-project laravel/laravel laravel-jwt-auth –prefer-dist

Step 2. Install JWT Package

composer require tymon/jwt-auth

Step 3. Add jwt package into a service provider

'providers' => [
...
'Tymon\JWTAuth\Providers\LaravelServiceProvider',
],
'aliases' => [
...
'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class,
'JWTFactory' => Tymon\JWTAuth\Facades\JWTFactory::class,
],

Step 4. Publish jwt configuration

Publish jwt configuration Command:

php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"

=> a new file in config/jwt.php

Step 5. Generate JWT Key

php artisan jwt:secret

=> Jwt key will be created in .env like this
=> JWT_SECRET=OSPvaJsWFZ2lXHJl12Hvi6sVUuPo403wjoR6Soaay2OfVCHrscfPmj1Jz8PW87B0

Step 6. Create jwt middleware

=> app\Http\Middleware\JwtMiddleware.php

Register this into Kernel. Open app\Http\Kernel.php

...
protected $routeMiddleware = [
...
'jwt.verify' => \App\Http\Middleware\JwtMiddleware::class,
'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',
];
...

=> In case of user not authenticated middleware throw UnauthorizedHttpException exception.

Step 7. Configure database

...
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restapi
DB_USERNAME=root
DB_PASSWORD=
...

Step 8. Create API Routes

Open routes\api.php

Step 9. Create api controller

create a JWTAuthController controller

php artisan make:controller ApiController

=> app\Http\Controllers\ApiController.php

Step 10. Create boilerplate for Task

php artisan make:model Task -rcm

-r = ressource
-c = controller
-m = migration

Step 11. Update api controller actions

=> app\Http\Controllers\ApiController.php

Step 12. Prepare product controller action

=> app\Http\Controllers\ProductController.php

Step 13. Update User.php model

=> app\Models\User.php

Step 14. Update Test.php model

=> app\Models\Product.php

Step 15. Create migration

Step 16: Migrate database

Run => php artisan migrate

Step 17. Now start the development server

Run => php artisan serve
