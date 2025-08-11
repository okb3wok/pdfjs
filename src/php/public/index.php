<?php

namespace App;

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/router.php';
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/utilites.php';

use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;
use Slim\Exception\HttpNotFoundException;

$app = AppFactory::create();


$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, [
  'cache' => false,
]);



$user = [];
$account = '';
if (isset($_SESSION["valid_user"])) {
  $controller = new Controller();
  $user = $controller->load_user_by_email($_SESSION["valid_user"]);
  $account = $_SESSION["valid_user"];
}



$commonData = [
  'year' => date('Y'),
  'isLoggedIn' => isset($_SESSION["valid_user"]),
  'user' => $user,
  'account' => $account
];

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(HttpNotFoundException::class, function (
  Request $request,
  Throwable $exception,
  bool $displayErrorDetails
) use ($app, $twig, $commonData) {
  $response = $app->getResponseFactory()->createResponse();
  $html = $twig->render('404.twig', array_merge([
    'pageTitle' => 'Страница не найдена',
  ], $commonData));
  $response->getBody()->write($html);
  return $response->withStatus(404);
});


router($app, $twig, $commonData, $user);

$app->run();