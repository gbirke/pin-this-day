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

    public function getLinkFeed($user, \DateTime $date)
    {
        $safeUser = strip_tags($user);

        $writer = new \PicoFeed\Syndication\Atom();
        $writer->title = 'On this day on Pinboard for '.$safeUser;
        $writer->site_url = $this->container['url_generator']->generate("index", [], true);
        $writer->feed_url = $this->container['url_generator']->generate("link_feed", ["user" => $user], true);

        $userId = $this->container["user_query"]->getIdForUsername($user);
        if (empty($userId)) {
            $writer->description = sprintf("Empty feed - the user '%s' is not in our database.", $safeUser);
            return $writer->execute();
        }

        $today = strtotime($date->format("Y-m-d")); // cut off the time part
        $bookmarks = $this->container["bookmark_query"]->getBookmarks($date->format("Y-n-j"), $userId);
        while ($bookmarks) {
            $bookmark = array_pop($bookmarks); // Reverse array so "newest" are at the top of the feed.
            $content = sprintf("<p>%s</p><p>Tags: %s</p>",
                nl2br(strip_tags($bookmark["description"])),
                $this->container["twig"]->render("bookmark_tags.html.twig", [
                    "tags" => explode(" ", $bookmark["tags"]),
                    "user" => $user
                ])
            );
            $writer->items[] = array(
                'title' => $bookmark["title"],
                'updated' => $today,
                'url' => $bookmark["url"],
                'summary' => $bookmark["tags"],
                'content' => $content
            );
            // TODO: Extend Atom class to add categories linking to the pinboard tags for user
        }
        return $writer->execute();
    }

} 