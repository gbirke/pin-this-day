<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();

// Only for development
$app["debug"] = true;
Dotenv::load(__DIR__."/..");
// ND only for development

$dsn = getenv("DB_DSN");
if (!$dsn) {
    throw new \RuntimeException("Please configure DB in DB_DSN");
}

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

// routes

$app->get("/", function (Application $app) {
     return $app['twig']->render('index.html.twig');
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
    $sql = "SELECT url, title, description, GROUP_CONCAT(DISTINCT tag ORDER BY seq ASC SEPARATOR ' ') as tags,
        YEAR(b.created_at) as `year`
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

    return $app['twig']->render('thisday.html.twig', [
        "bookmarks" => $bookmarks,
        "user" => $user,
        "userid" => $userId
    ]);
})->bind("thisday");

$app->run();
