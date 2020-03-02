<?php
namespace Pingur\Lib;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Illuminate\Support\Str;
use Spatie\Crawler\CrawlObserver;

class Crawler extends CrawlObserver
    {
        private $results =[];
        /**
         * Called when the crawler will crawl the url.
         *
         * @param \Psr\Http\Message\UriInterface $url
         */
        public function willCrawl(UriInterface $url)
        {
    
        }
    
        /**
         * Called when the crawler has crawled the given url successfully.
         *
         * @param \Psr\Http\Message\UriInterface $url
         * @param \Psr\Http\Message\ResponseInterface $response
         * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
         */
        public function crawled(
            UriInterface $url,
            ResponseInterface $response,
            ?UriInterface $foundOnUrl = null
        )
        {
            $path = $url->getPath();
            $this->results[] = [
                'path'=>$path
            ];

        }
    
        /**
         * Called when the crawler had a problem crawling the given url.
         *
         * @param \Psr\Http\Message\UriInterface $url
         * @param \GuzzleHttp\Exception\RequestException $requestException
         * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
         */
        public function crawlFailed(
            UriInterface $url,
            RequestException $requestException,
            ?UriInterface $foundOnUrl = null
        )
        {

        }
    
        /**
         * Called when the crawl has ended.
         */
        public function finishedCrawling() {
            echo 'crawled ' . count($this->results) . ' urls' . PHP_EOL;
            foreach ($this->results as $result){
                echo sprintf("Url  path: %s\n", $result['path'], PHP_EOL);
            }
    
        }
    }

