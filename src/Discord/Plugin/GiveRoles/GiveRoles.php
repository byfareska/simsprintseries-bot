<?php declare(strict_types=1);

namespace App\Discord\Plugin\GiveRoles;

use App\AssettoCorsa\Practice;
use App\Discord\Plugin\AbstractPlugin;
use App\Repository\AssettoCorsaActiveEventRepository;
use App\Repository\AssettoCorsaAssociatedNameRepository;
use App\Repository\AssettoCorsaGaveRankRepository;
use Discord\Parts\Channel\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GiveRoles extends AbstractPlugin
{
    private AssettoCorsaActiveEventRepository $activeEventRepository;
    private AssettoCorsaAssociatedNameRepository $associatedNameRepository;
    private AssettoCorsaGaveRankRepository $gaveRankRepository;
    private Practice $practice;
    private EntityManagerInterface $em;
    private HttpClientInterface $httpClient;

    public function __construct(
        AssettoCorsaActiveEventRepository $activeEventRepository,
        AssettoCorsaAssociatedNameRepository $associatedNameRepository,
        Practice $practice,
        AssettoCorsaGaveRankRepository $gaveRankRepository,
        EntityManagerInterface $em,
        HttpClientInterface $httpClient
    )
    {
        $this->activeEventRepository = $activeEventRepository;
        $this->associatedNameRepository = $associatedNameRepository;
        $this->gaveRankRepository = $gaveRankRepository;
        $this->practice = $practice;
        $this->em = $em;
        $this->httpClient = $httpClient;
    }

    protected function bind(): void
    {
        $this->discord->on("message", fn(Message $message) => $this->messageHandler($message));
        $this->discord->getLoop()->addPeriodicTimer(60 * 10, fn() => $this->giveRolesLoop());
    }

    public function getMessageEquals(): array
    {
        return [
            "!setevent"
        ];
    }

    public function getMessageContainsRegex(): array
    {
        return [
            "/^\!setevent ([0-9]+) (.+)$/"
        ];
    }

    private function giveRolesLoop(): void
    {
        $instances = $this->activeEventRepository->findAll();
        foreach ($instances as $instance) {
            new GiveRolesLoop(
                $instance,
                $this->practice,
                $this->discord,
                $this->associatedNameRepository,
                $this->permissions,
                $this->gaveRankRepository,
                $this->em
            );
        }
    }

    private function messageHandler(Message $message): void
    {
        if ($this->matcher->isOwnMessage($message) || !$this->matcher->messageHasRequiredContent($message))
            return;

        $this->permissions->executeIfAdmin(
            $message->author,
            fn() => $this->messageHandlerAfterPermissionCheck($message),
            fn() => $message->reply("nie masz uprawnień by korzystać z tej komendy.")
        );
    }

    private function messageHandlerAfterPermissionCheck(Message $message): void
    {
        $id = @$this->matcher->getMessageMatches()[1];
        $link = @$this->matcher->getMessageMatches()[2];
        if ($link === null || $id === null) {
            $message->reply("Błędna składnia. Użycie komendy: ```!setevent instancja link np. !setevent 1 https://www.simsprintseries.pl/seria/formula3-assetto-corsa-zima-2020/224-gp-japonii-fuji```");
            return;
        }

        $instance = $this->activeEventRepository->findOneById((int)$id);
        if ($instance === null) {
            $message->reply("Instancja \"{$id}\" nie istnieje.");
            return;
        }

        $instance->setEventLink($link);
        $this->em->persist($instance);
        $this->em->flush();
        $this->gaveRankRepository->deleteByInstance($instance);
        $message->react("✅");
    }
}