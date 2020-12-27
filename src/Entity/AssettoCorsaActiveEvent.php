<?php

namespace App\Entity;

use App\Repository\AssettoCorsaActiveEventRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AssettoCorsaActiveEventRepository::class)
 */
class AssettoCorsaActiveEvent
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $discordGroupId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $alertChannelId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $eventLink;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiscordGroupId(): ?string
    {
        return $this->discordGroupId;
    }

    public function setDiscordGroupId(string $discordGroupId): self
    {
        $this->discordGroupId = $discordGroupId;

        return $this;
    }

    public function getEventLink(): ?string
    {
        return $this->eventLink;
    }

    public function setEventLink(string $eventLink): self
    {
        $this->eventLink = $eventLink;

        return $this;
    }

    public function getAlertChannelId(): ?string
    {
        return $this->alertChannelId;
    }

    public function setAlertChannelId(string $alertChannelId): self
    {
        $this->alertChannelId = $alertChannelId;

        return $this;
    }
}
