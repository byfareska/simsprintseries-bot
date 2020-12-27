<?php declare(strict_types=1);

namespace App\Discord\Plugin\TimeTrial;

use App\AssettoCorsa\Practice;
use App\Discord\Plugin\AbstractPlugin;
use App\Entity\AssettoCorsaActiveEvent;
use App\Repository\AssettoCorsaActiveEventRepository;
use App\Repository\AssettoCorsaAssociatedNameRepository;
use App\Repository\AssettoCorsaGaveRankRepository;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Doctrine\ORM\EntityManagerInterface;

final class TimeTrial extends AbstractPlugin
{
    private AssettoCorsaActiveEventRepository $activeEventRepository;
    private AssettoCorsaAssociatedNameRepository $associatedNameRepository;
    private Practice $practice;
    private AssettoCorsaGaveRankRepository $gaveRankRepository;
    private EntityManagerInterface $em;

    public function __construct(
        AssettoCorsaActiveEventRepository $activeEventRepository,
        AssettoCorsaAssociatedNameRepository $associatedNameRepository,
        Practice $practice,
        AssettoCorsaGaveRankRepository $gaveRankRepository,
        EntityManagerInterface $em
    )
    {
        $this->activeEventRepository = $activeEventRepository;
        $this->associatedNameRepository = $associatedNameRepository;
        $this->gaveRankRepository = $gaveRankRepository;
        $this->practice = $practice;
        $this->em = $em;
    }

    protected function bind(): void
    {
        $this->discord->on("message", fn(Message $message) => $this->messageHandler($message));
    }

    private function messageHandler(Message $message): void
    {
        if ($this->matcher->isOwnMessage($message) || !$this->matcher->messageHasRequiredContent($message))
            return;

        $instanceId = (int)(isset($this->matcher->getMessageMatches()[1]) ? $this->matcher->getMessageMatches()[1] : 1);
        $instance = $this->activeEventRepository->findOneById($instanceId);

        if ($instance === null) {
            $message->reply("instancja {$instanceId} nie istnieje.");
            return;
        }

        $results = $this->practice->getByActiveEvent($instance);

        if ($results === null) {
            $message->reply("coś poszło nie tak. Proszę, spróbuj ponownie za jakiś czas.");
            return;
        }

        $associations = $this->associatedNameRepository->findAllByDiscordId($message->author->id);
        $clientResult = current(array_filter($results, function ($item) use ($associations) {
            $driver = strtolower($item->driver);
            foreach ($associations as $association) {
                if (strtolower($association->getAssetto()) === $driver)
                    return true;
            }
            return false;
        }));

        $embed = new Embed($this->discord);
        $embed->color = 3447003;
        $embed->title = "TOP5 na instancji #{$instance->getId()}";
        $embed->url = $instance->getEventLink();
        $embed->description = $clientResult === false
            ? "{$message->author}, twoje konto nie jest powiązane z żadnym kontem Assetto Corsa."
            : "{$message->author}, zajmujesz **{$clientResult->position}** miejsce, {$clientResult->bestLap} "
            . "**{$clientResult->gap}** (Okrążeń: {$clientResult->laps}, {$clientResult->car})";
        $embed->fields = $this->createFields($instance, $results);
        $message->channel->sendEmbed($embed);
    }

    public function getMessageEquals(): array
    {
        return [
            "!tt"
        ];
    }

    public function getMessageContainsRegex(): array
    {
        return [
            "/^\!tt ([0-9]+)$/"
        ];
    }

    private function createFields(AssettoCorsaActiveEvent $instance, array $results): array
    {
        $fields = [];
        foreach ($results as $result) {
            if ($instance->getId() === $result->instance) {
                $field = new Field($this->discord);
                $field->name = "{$result->position} {$result->driver}";
                $field->value = "{$result->bestLap} **{$result->gap}** (Okrążeń: {$result->laps}, {$result->car})";
                $fields[] = $field;
            }

            if (count($fields) >= 5)
                return $fields;
        }

        return $fields;
    }

}