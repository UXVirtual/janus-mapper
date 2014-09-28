<?php

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Exception\RequestException;

/**
 * Class CrawlerController
 *
 * @author Michael Andrew (michael@uxvirtual.com
 */
class CrawlerController extends BaseController {

    var $client;
    var $pages = array();
    var $linksToCrawl = array();

    /**
     * Crawl
     *
     * Uses Goutte crawler package which wraps Symfony's BrowserKit framework.
     * For more information on using the crawler client see http://symfony.com/doc/current/book/testing.html
     */
    public function crawl()
    {
        $this->output('<pre>');

        $this->output('Running crawl...');

        $pages[Config::get('crawler.start_url').'/']['parent'] = null;

        $this->crawlPage(Config::get('crawler.start_url').'/');

        $this->output('Crawl successful.'."\n");

        $this->output('Outputting results...'."\n");

        $this->output(print_r($this->pages,TRUE));

        $this->output('Finished.'."\n");

    }

    private function output($message){
        echo "\n";
        echo $message."\n";
    }

    private function crawlPage($url){
        $this->output('Scraping links from URL: '.$url);
        $result = $this->scrapeLinks($url);

        if(!isset($result['error'])){
            $pageUrls = $result;
        }else{
            $this->pages[$url]['scrape_error'] = 'Something went wrong while trying to scrape this page.';
            return;
        }

        $this->output('Got links: '.print_r($pageUrls,TRUE));

        if($this->getNumberParents($url) < Config::get('crawler.depth')+1){
            $this->linksToCrawl = array_merge($this->linksToCrawl,$pageUrls);

            while(count($this->linksToCrawl) > 0){
                $pageURL = array_pop($this->linksToCrawl);
                if(!isset($this->pages[$pageURL])){
                    $this->crawlPage($pageURL);
                }
            }
        }
    }

    private function scrapeLinks($url){
        $this->client = new Client();

        try{
            $this->client->request('GET', $url);
        }catch(RequestException $e){
            return array('error' => $e->getMessage());
        }


        $content = $this->client->getResponse()->getContent();

        //replace all HTML comments in content to work around issue where some sites comment out VR specific code
        $content = preg_replace('/(<!--|-->)/m', '', $content);

        $crawler = new Crawler(null, $url);
        $crawler->addContent($content);



        $pageLinks = array();

        $pageLinks = $crawler->filter('Link')->each(function ($node) {

            $linkURL = $node->extract(array('url'));

            if(!empty($linkURL)){
                $linkURL = $linkURL[0];
                if(!empty($linkURL)){
                    return $linkURL;
                    //$this->output($url);
                }

            }
        });

        for($i = 0; $i < count($pageLinks); $i++){
            $pageLinks[$i] = URLTools::url_to_absolute($url, $pageLinks[$i]);
        }

        if(!isset($pages[$url])){
            $this->pages[$url] = array();
        }

        $this->pages[$url]['md5_hash'] = md5($url);
        $this->pages[$url]['links'] = $pageLinks;
        $this->pages[$url]['parent'] = $url;


        //TODO: split keywords into primary and secondary, storing common keywords in secondary so primary results aren't diluted
        $this->pages[$url]['keywords'] = explode(', ',seo::keywords($content,25));

        $this->output('Keywords: '.print_r($this->pages[$url]['keywords'],TRUE));

        return $pageLinks;
    }

    private function getNumberParents($url){
        $parentCount = 0;

        while($parentCount < Config::get('crawler.depth')+1 ){

            $parentURL = $this->pages[$url]['parent'];

            if($parentURL == null){
                return $parentCount;
            }else{
                $parentCount++;
            }

        }
    }


}
