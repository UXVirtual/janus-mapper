<?php

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CrawlerController
 *
 * @author Michael Andrew (michael@uxvirtual.com
 */
class CrawlerController extends BaseController {

    /**
     * Crawl
     *
     * Uses Goutte crawler package which wraps Symfony's BrowserKit framework.
     * For more information on using the crawler client see http://symfony.com/doc/current/book/testing.html
     */
    public function crawl()
    {
        $this->output('Running crawl...');

        $client = new Client();
        $client->request('GET', Config::get('crawler.start_url').'/');
        $content = $client->getResponse()->getContent();

        //replace all HTML comments in content to work around issue where some sites comment out VR specific code
        $content = preg_replace('/(<!--|-->)/m', '', $content);

        $crawler2 = new Crawler(null, Config::get('crawler.start_url').'/');
        $crawler2->addContent($content);

        echo '<pre>';

        $links = array();

        $crawler2->filter('Link')->each(function ($node) {

            $url = $node->extract(array('url'));

            if($url != ''){
                $url = $url[0];
                $links[] = $url;
                $this->output($url);
            }
        });

        $this->output('Crawl successful.'."\n");

    }

    private function output($message){
        echo "\n";
        echo $message."\n";
    }

}
