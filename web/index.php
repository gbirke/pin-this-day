<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();
$app["debug"] = true;

// Services
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../src/Resources/views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// routes

$app->get("/", function (Application $app) {
     return $app['twig']->render('index.html.twig');
});

$app->post("/set_user", function (Application $app, Request $req) {
    throw new Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Not implemented yet.");
    // TODO: Redirect to user name from POST
    //return $app->redirect()
})->bind("set_user");

$app->run();
