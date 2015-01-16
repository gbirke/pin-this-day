<?php
/**
 * Created by PhpStorm.
 * User: gbirke
 * Date: 16.01.15
 * Time: 12:11
 */

namespace Birke\PinThisDay;

use Symfony\Component\HttpFoundation\Request;


class FeedCreator {

    protected $container;

    function __construct($serviceContainer)
    {
        $this->container = $serviceContainer;
    }

    public function getSummaryFeed($user)
    {
        $safeUser = strip_tags($user);

        $writer = new \PicoFeed\Syndication\Atom();
        $writer->title = 'On this day on Pinboard for '.$safeUser;
        $writer->site_url = $this->container['url_generator']->generate("index", [], true);
        $writer->feed_url = $this->container['url_generator']->generate("summary_feed", ["user" => $user], true);

        $userId = $this->container["user_query"]->getIdForUsername($user);
        if (empty($userId)) {
            $writer->description = sprintf("Empty feed - the user '%s' is not in our database.", $safeUser);
            return $writer->execute();
        }

        $date = new \DateTime();
        for($i=0; $i < 10; $i++) {
            $dateStr = $date->format("Y-m-d");
            $bookmarks = $this->container["bookmark_query"]->getBookmarks($date->format("Y-n-j"), $userId);
            $summary = sprintf("%d bookmarks", count($bookmarks));
            $content = $this->container["twig"]->render('thisday_list.html.twig', [
                "bookmarks" => $bookmarks,
                "user" => $user,
            ]);
            $writer->items[] = array(
                'title' => 'Pinboard summary for '.$dateStr,
                'updated' => $date->format("U"),
                'url' => $this->container['url_generator']->generate('thisday', ["user" => $user, "date" => $dateStr], true),
                'summary' => $summary,
                'content' => $content
            );
            $date->modify("-1 day");
        }
        return $writer->execute();
    }

} 