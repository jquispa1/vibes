<?php


  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  use Illuminate\Http\Request;
  use Illuminate\Contracts\Http\Kernel;

  define('LARAVEL_START', microtime(true));

  if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
      require $maintenance;
  }

  require __DIR__.'/../vendor/autoload.php';

  $app = require_once __DIR__.'/../bootstrap/app.php';

  if (!file_exists(__DIR__ . '/../.env')) {
      $app->loadEnvironmentFrom('env.example');
  }

  $kernel = $app->make(Kernel::class);

  $request = Request::capture();
  $response = $kernel->handle($request);
  $response->send();
  $kernel->terminate($request, $response);
