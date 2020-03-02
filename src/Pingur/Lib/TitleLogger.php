<?php
namespace Pingur\Lib;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Illuminate\Support\Str;
use Spatie\Crawler\CrawlObserver;
use DOMDocument;

class TitleLogger extends CrawlObserver{

private $pages =[];


public function crawled(
    UriInterface $url,
    ResponseInterface $response,
    ?UriInterface $foundOnUrl = null
)
{

    $path = $url->getPath();
    $doc = new DOMDocument();
    @$doc->loadHTML($response->getBody());
    if ($doc->getElementsByTagName("title")[0]->nodeValue) {
        $title = $doc->getElementsByTagName("title")[0]->nodeValue;
    } else {
        $title = "Missing page title";
    }


    $this->pages[] = [
        'path'=>$path,
        'title'=> $title
    ];
}

public function crawlFailed(
    UriInterface $url,
    RequestException $requestException,
    ?UriInterface $foundOnUrl = null
)
{
    echo 'failed';
}

public function finishedCrawling()
{
    echo 'crawled ' . count($this->pages) . ' urls' . PHP_EOL;
    foreach ($this->pages as $page){
        echo sprintf("Url  path: %s Page title: %s%s", $page['path'], $page['title'], PHP_EOL);
    }
}

}