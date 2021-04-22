<?php declare(strict_types=1);

namespace App\Discord\Plugin\UpcomingEvents;

use App\Discord\Plugin\AbstractPlugin;
use App\Util\EventUrlGenerator;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class UpcomingEvents extends AbstractPlugin
{
    private CacheInterface $cache;
    private HttpClientInterface $httpClient;
    private EventUrlGenerator $eventUrlGenerator;
    private EmbedGenerator $embedGenerator;

    public function __construct(
        CacheInterface $cache,
        HttpClientInterface $httpClient,
        EventUrlGenerator $eventUrlGenerator,
        EmbedGenerator $embedGenerator
    )
    {
        $this->cache = $cache;
        $this->httpClient = $httpClient;
        $this->eventUrlGenerator = $eventUrlGenerator;
        $this->embedGenerator = $embedGenerator;
    }

    protected function bind(): void
    {
        $this->discord->on("message", fn(Message $message) => $this->messageHandler($message));
    }

    private function messageHandler(Message $message): void
    {
        if ($this->matcher->isOwnMessage($message) || !$this->matcher->messageHasRequiredContent($message))
            return;

        $embed = $this->embedGenerator->generate();
        $message->channel->sendEmbed($embed);
    }

    public function getEmbedToSend():Embed{

    }

    public function getMessageContains(): array
    {
        return [
            "kiedy wyscig",
            "kiedy jezdzcie",
            "kiedy macie wyscig"
        ];
    }

    public function getMessageEquals(): array
    {
        return [
            "!kiedy"
        ];
    }
}