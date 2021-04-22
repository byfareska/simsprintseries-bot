<?php declare(strict_types=1);

namespace App\Discord\Plugin\UpcomingEvents;

use App\Helper\ArrayHelper;
use App\Util\EventUrlGenerator;
use App\Util\HumanDateInterval;
use DateInterval;
use DateTime;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use stdClass;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class EmbedGenerator
{
    private CacheInterface $cache;
    private HttpClientInterface $httpClient;
    private EventUrlGenerator $eventUrlGenerator;
    private Discord $discord;

    public function __construct(
        CacheInterface $cache,
        HttpClientInterface $httpClient,
        EventUrlGenerator $eventUrlGenerator,
        Discord $discord
    )
    {
        $this->cache = $cache;
        $this->httpClient = $httpClient;
        $this->eventUrlGenerator = $eventUrlGenerator;
        $this->discord = $discord;
    }

    public function generate(): Embed
    {
        $events = $this->getEvents();
        $fields = $this->convertEventsToFields($events);

        $embed = new Embed($this->discord);
        $embed->color = 3447003;
        $embed->title = "Kalendarz";
        $embed->url = "https://www.simss.pl/kalendarz";
        $embed->description = "[[Dodaj kalendarz na swój telefon]](https://simss.pl/b/17)";
        $embed->fields = $fields;
        return $embed;
    }

    /**
     * @param stdClass[] $events
     * @return Field[]
     */
    private function convertEventsToFields(array $events): array
    {
        $fields = [];

        foreach ($events as $event) {
            $field = new Field($this->discord);
            $field->name = $event->relatedleague->name;
            $field->value = sprintf(
                "**[%s](%s)** za **%s** - %s %s %s",
                $event->name,
                $this->eventUrlGenerator->getUrl($event),
                new HumanDateInterval($event->starts),
                $event->starts->format("d"),
                HumanDateInterval::translateMonthName((int)$event->starts->format("n")),
                $event->starts->format("Y")
            );
            $fields[] = $field;
        }

        return $fields;
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
}