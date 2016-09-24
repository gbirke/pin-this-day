<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Birke\PinThisDay\Db\BookmarkQuery;
use Birke\PinThisDay\Db\UserQuery;

$app = new Application();

Dotenv::load(__DIR__."/..");

$dsn = getenv("DB_DSN");
if (!$dsn) {
    throw new \RuntimeException("Please configure DB in DB_DSN");
}

$app["debug"] = getenv("SILEX_ENV") != "prod";

// Parameters
$app["pinboard_url"] = "https://pinboard.in/";
$app["cache.default_time"] = 60;
$app["cache.rss_time"] = 1;

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

$app["bookmark_query"] = $app->share(function($app) {
    return new BookmarkQuery($app["db"]);
});

$app["user_query"] = $app->share(function($app) {
    return new UserQuery($app["db"]);
});

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

$app->get("u:{user}/summary.atom", function(Application $app, $user) {
    $contentHeaders = ["Content-Type" => "application/atom+xml"];
    $feedCreator = new \Birke\PinThisDay\FeedCreator($app);
    $response = new Response($feedCreator->getSummaryFeed($user), 200, $contentHeaders);
    $response->setTtl($app["cache.rss_time"]);
    $response->setSharedMaxAge($app["cache.rss_time"]);
    return $response;
})->bind("summary_feed");

$app->get("u:{user}/links.atom", function(Application $app, $user) {
    $contentHeaders = ["Content-Type" => "application/atom+xml"];
    $feedCreator = new \Birke\PinThisDay\FeedCreator($app);
    $response = new Response($feedCreator->getLinkFeed($user, new \DateTime()), 200, $contentHeaders);
    $response->setTtl($app["cache.rss_time"]);
    $response->setSharedMaxAge($app["cache.rss_time"]);
    return $response;
})->bind("link_feed");

$app->get("u:{user}/{date}", function (Application $app, $user, \DateTime $date) {
    $userId = $app["user_query"]->getIdForUsername($user);
    if (empty($userId)) {
        $app['twig']->render('thisday_error.html.twig', ["error" => "User not found"]);
    }

    $now = new \DateTime();
    $dateIsToday = $date->format("Y-m-d") === $now->format("Y-m-d");
    $prevDate = clone $date;
    $nextDate = clone $date;
    $prevDate->modify('-1 day');
    $nextDate->modify('+1 day');
    $bookmarks = $app['bookmark_query']->getBookmarks($date->format("Y-n-j"), $userId);
    $lastUpdate = $app["user_query"]->getLastUpdateForUser($userId);
    $responseContext = [
        "today" => $dateIsToday,
        "bookmarks" => $bookmarks,
        "user" => $user,
        "userid" => $userId,
        "last_update" => $lastUpdate,
        "current_date" => $date,
        "prev_date" => $prevDate
    ];
    if ( !$dateIsToday ) {
        $responseContext["next_date"] = $nextDate;
    }
    $response = new Response($app['twig']->render('thisday.html.twig', $responseContext));
    $response->setTtl($app["cache.default_time"]);
    $response->setSharedMaxAge($app["cache.default_time"]);
    return $response;
})
    ->bind("thisday")
    ->convert("date", function($date) { return new \DateTime($date);})
    ->value("date", date("Y-m-d"))
    ->assert("date", '\d{4}-\d{2}-\d{2}')
;

Request::setTrustedProxies(array('127.0.0.1'));
$app["http_cache"]->run();
