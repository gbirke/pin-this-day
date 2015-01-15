<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Application();

// Only for development
$app["debug"] = true;
Dotenv::load(__DIR__."/..");
// ND only for development

$dsn = getenv("DB_DSN");
if (!$dsn) {
    throw new \RuntimeException("Please configure DB in DB_DSN");
}

// Parameters
$app["pinboard_url"] = "https://pinboard.in/";
$app["cache.default_time"] = 60;

// Services
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../src/Resources/views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'url' => $dsn
    ),
));
$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => __DIR__.'/../app/cache/http',
    'http_cache.esi'       => null,
));

// routes

$app->get("/", function (Application $app) {
    $response = new Response($app['twig']->render('index.html.twig'));
    $response->setSharedMaxAge($app["cache.default_time"]);
    $response->setTtl($app["cache.default_time"]);
    return $response;
})->bind("index");

$app->post("/set_user", function (Application $app, Request $req) {
    $user = $req->request->get("username", "");
    if ($user) {
        return $app->redirect($app["url_generator"]->generate("thisday", ["user" => $user]));
    } else {
        return $app->redirect($app["url_generator"]->generate("index"));
    }
})->bind("set_user");

$app->get("u:{user}", function (Application $app, $user) {
    $userId = $app["db"]->fetchColumn("SELECT id FROM users WHERE login = ?", array($user));
    if (empty($userId)) {
        $app['twig']->render('thisday_error.html.twig', ["error" => "User not found"]);
    }
    $sql = "SELECT url, title, description, GROUP_CONCAT(DISTINCT tag ORDER BY seq ASC SEPARATOR ' ') AS tags,
        YEAR(b.created_at) AS `year`, UNIX_TIMESTAMP(b.created_at) AS ts
        FROM bookmarks b
        JOIN btags t ON b.id = t.bookmark_id
        WHERE b.user_id = ?
              AND YEAR(b.created_at) < ?
              AND MONTH(b.created_at) = ?
              AND DAY(b.created_at) = ?
        GROUP by url
        ORDER BY b.created_at DESC
        LIMIT 100
     ";
    $month = date("n");
    $day = date("j");
    $year = date("Y");
    $bookmarks = $app["db"]->fetchAll($sql, [$userId, $year, $month, $day]);

    $response = new Response($app['twig']->render('thisday.html.twig', [
        "bookmarks" => $bookmarks,
        "user" => $user,
        "userid" => $userId
    ]));
    $response->setTtl($app["cache.default_time"]);
    $response->setSharedMaxAge($app["cache.default_time"]);
    return $response;
})->bind("thisday");

Request::setTrustedProxies(array('127.0.0.1'));
$app["http_cache"]->run();
