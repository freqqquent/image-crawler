<?php

namespace App\Service;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class MainService
{
    public function __construct(
        private HttpClientInterface $client,
    ) {}

    private function getUrls(string $html): array {
        $crawler = new Crawler($html);
        $urls = $crawler
            ->filterXpath('//img')
            ->extract(array('src'));
        foreach ($urls as &$url){
            $url = $this->fixUrl($url);
        }
        // по какой-то неизвестной причине последний url хранится по ссылке, если не вызвать "unset"
        unset($url);
        return $urls;
    }

    private function getHtml(string $address): string {

        $response = $this->client->request('GET', $address);
        return $response->getContent();
    }

    private function objectSize(array $urls): float {
        $size = 0;
        foreach ($urls as $url){
            try {
                $size += strlen($this->client->request('GET',$url)->getContent());
            } catch (Throwable $exception) {
                echo 'Ошибка при запросе картинки: ' . $exception->getMessage() . '<br>';
            }
        }
        return $size;
    }

    private function fixUrl(string $url): string
    {
        if(str_starts_with($url, "http")){
            return $url;
        }
        // некоторые url начинаются с "//", но не с "https"
        if(str_starts_with($url, "//")){
            return 'https:' . $url;
        }
        return 'https://' . $url;
    }

    #[ArrayShape([
        'urls' => "array",
        'totalImages' => "int",
        'size' => "float",
    ])]
    public function getParameters(string $address): array {
        $urls = $this->getUrls(
            $this->getHtml($address)
        );
        $totalImages = count($urls);
        $size = $this->objectSize($urls);
        return [
            'urls' => $urls,
            'totalImages' => $totalImages,
            'size' => $size,
        ];
    }
}
