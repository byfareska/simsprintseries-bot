<?php declare(strict_types=1);

namespace App\AssettoCorsa;

use App\Entity\AssettoCorsaActiveEvent;
use Cocur\Slugify\Slugify;
use DateInterval;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class Practice
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private Slugify $slugify;

    public function __construct(CacheInterface $cache, Slugify $slugify, HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->slugify = $slugify;
    }

    public function getByActiveEvent(AssettoCorsaActiveEvent $event): ?array
    {
        $response = $this->doRequestOrUseCache($event, "{$event->getEventLink()}/practice");

        if($response === null)
            return null;

        return json_decode($response->getContent());
    }

    private function doRequestOrUseCache(AssettoCorsaActiveEvent $instance, string $resultsUrl): ?ResponseInterface
    {
        return $this->cache->get($this->slugify->slugify($resultsUrl), function (ItemInterface $item) use ($resultsUrl, $instance) {
            $item->expiresAfter(new DateInterval("PT10M"));
            $request = $this->httpClient->request("GET", $resultsUrl);
            if ($request->getStatusCode() !== 200)
                return null;

            return $request;
        });
    }
}