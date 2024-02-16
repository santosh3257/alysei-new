<?php
/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */
$multipleHeaders = ['http://localhost:3000', 'https://alyseiweb.ibyteworkshop.com','http://64.227.181.34','https://alysei-site.ibyteworkshop.com'];
$http_origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;


if ($http_origin == "https://alysei.com" || $http_origin == "https://dev.alysei.com" || $http_origin == "http://134.119.179.231" || $http_origin == "http://localhost:3000" || $http_origin == "https://alyseiweb.ibyteworkshop.com" || $http_origin == "http://64.227.181.34" || $http_origin == "https://alysei-site.ibyteworkshop.com" )
{  
    header("Access-Control-Allow-Origin: $http_origin");
}
//header('Access-Control-Allow-Origin: https://alyseiweb.ibyteworkshop.com');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);