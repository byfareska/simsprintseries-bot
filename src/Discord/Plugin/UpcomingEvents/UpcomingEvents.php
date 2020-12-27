<?php declare(strict_types=1);

namespace App\Discord\Plugin\UpcomingEvents;

use App\Discord\Plugin\AbstractPlugin;
use App\Helper\ArrayHelper;
use App\Util\HumanDateInterval;
use DateInterval;
use DateTime;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class UpcomingEvents extends AbstractPlugin
{
    private CacheInterface $cache;
    private HttpClientInterface $httpClient;

    public function __construct(CacheInterface $cache, HttpClientInterface $httpClient)
    {
        $this->cache = $cache;
        $this->httpClient = $httpClient;
    }

    protected function bind(): void
    {
        $this->discord->on("message", fn(Message $message) => $this->messageHandler($message));
    }

    private function messageHandler(Message $message): void
    {
        if ($this->matcher->isOwnMessage($message) || !$this->matcher->messageHasRequiredContent($message))
            return;

        $events = $this->getEvents();
        $fields = [];
        foreach ($events as $event) {
            $field = new Field($this->discord);
            $field->name = $event->relatedleague->name;
            $relative = new HumanDateInterval($event->starts);
            $monthName = HumanDateInterval::translateMonthName((int)$event->starts->format("n"));
            $field->value = "**[{$event->name}](https://simss.pl/seria/formula1-ps4-zima-2020)** za **{$relative}** - {$event->starts->format("d")} {$monthName} {$event->starts->format("Y")}";
            $fields[] = $field;
        }

        $embed = new Embed($this->discord);
        $embed->color = 3447003;
        $embed->title = "Kalendarz";
        $embed->url = "https://www.simss.pl/kalendarz";
        $embed->description = "[[Dodaj kalendarz na swój telefon]](https://simss.pl/b/17)";
        $embed->fields = $fields;
        $message->channel->sendEmbed($embed);
    }

    private function getEvents(): ?array
    {
        return $this->cache->get("upcoming-events-events", function (ItemInterface $item) {
            $response = $this->httpClient->request("GET", "https://www.simsprintseries.pl/api/event");
            if ($response->getStatusCode() !== 200) {
                $item->expiresAfter(new DateInterval("PT10S"));
                return null;
            }

            $item->expiresAfter(new DateInterval("PT30M"));
            $now = new DateTime();
            $events = json_decode($response->getContent());
            $upcomingEvents = [];

            foreach ($events as &$event) {
                $event->starts = new DateTime($event->starts);
                if (
                    (
                        !isset($upcomingEvents[$event->relatedleague->id])
                        || $upcomingEvents[$event->relatedleague->id]->starts > $event->starts
                    ) && $event->starts > $now
                ) {
                    $upcomingEvents[$event->relatedleague->id] = $event;
                }
            }

            return ArrayHelper::sortByObjectProperty($upcomingEvents, "starts");
        });
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