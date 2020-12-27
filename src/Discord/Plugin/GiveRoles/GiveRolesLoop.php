<?php declare(strict_types=1);

namespace App\Discord\Plugin\GiveRoles;

use App\AssettoCorsa\Practice;
use App\Discord\Utility\Plugin\PermissionsChecker;
use App\Entity\AssettoCorsaActiveEvent;
use App\Entity\AssettoCorsaGaveRank;
use App\Repository\AssettoCorsaAssociatedNameRepository;
use App\Repository\AssettoCorsaGaveRankRepository;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Role;
use Discord\Parts\User\Member;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use stdClass;

final class GiveRolesLoop
{
    private AssettoCorsaActiveEvent $instance;
    private Practice $practice;
    private AssettoCorsaAssociatedNameRepository $associatedNameRepository;
    private Discord $discord;
    private ?array $results;
    private ?Channel $channel;
    private ?Role $role;
    private PermissionsChecker $permissions;
    /** @var AssettoCorsaGaveRank[] $gaveRankTo */
    private array $gaveRankTo;
    private EntityManagerInterface $em;

    public function __construct(
        AssettoCorsaActiveEvent $instance,
        Practice $practice,
        Discord $discord,
        AssettoCorsaAssociatedNameRepository $associatedNameRepository,
        PermissionsChecker $permissions,
        AssettoCorsaGaveRankRepository $gaveRankRepository,
        EntityManagerInterface $em
    )
    {
        $this->instance = $instance;
        $this->practice = $practice;
        $this->discord = $discord;
        $this->em = $em;
        $this->associatedNameRepository = $associatedNameRepository;
        $this->results = $this->practice->getByActiveEvent($instance) ?: [];
        $this->channel = $this->discord->getChannel($instance->getAlertChannelId());
        $this->role = $this->channel->guild->roles->get("id", $instance->getDiscordGroupId());
        $this->permissions = $permissions;
        $this->gaveRankTo = $gaveRankRepository->findByInstance($this->instance);
        $this->iterateResults();
    }

    private function iterateResults(): void
    {
        foreach ($this->results as $result) {
            $this->result($result);
        }
    }

    private function result(stdClass $result): void
    {
        if ($result->instance !== $this->instance->getId() || $result->laps < (int)$_ENV['ASSETTO_LAPS'])
            return;

        $association = $this->associatedNameRepository->findOneByAssettoCorsaName($result->driver);
        if ($association === null)
            return;

        $findDriver = fn(AssettoCorsaGaveRank $item) => $item->getDriver()->getDiscord() === $association->getDiscord();
        if (count(array_filter($this->gaveRankTo, $findDriver)) > 0)
            return;

        try {
            $addRole = fn(Member $member) => $member->addRole($this->role);
            $this->channel->guild->members->fetch($association->getDiscord())->done($addRole);
        } catch (Exception $e) {
            echo "{$e->getMessage()}\n";
        }

        $this->channel->sendMessage("<@{$association->getDiscord()}> właśnie spełnił warunki do wystartowania w wyścigu!");
        $gave = new AssettoCorsaGaveRank();
        $gave->setInstance($this->instance);
        $gave->setDriver($association);
        $this->em->persist($gave);
        $this->em->flush();
    }
}