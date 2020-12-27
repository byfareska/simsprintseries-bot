<?php

namespace App\Entity;

use App\Repository\AssettoCorsaAssociatedNameRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AssettoCorsaAssociatedNameRepository::class)
 */
class AssettoCorsaAssociatedName
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
    private $discord;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $assetto;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiscord(): ?string
    {
        return $this->discord;
    }

    public function setDiscord(string $discord): self
    {
        $this->discord = $discord;

        return $this;
    }

    public function getAssetto(): ?string
    {
        return $this->assetto;
    }

    public function setAssetto(string $assetto): self
    {
        $this->assetto = $assetto;

        return $this;
    }
}
