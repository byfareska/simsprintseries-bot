<?php

namespace App\Entity;

use App\Repository\AssettoCorsaGaveRankRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AssettoCorsaGaveRankRepository::class)
 */
class AssettoCorsaGaveRank
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=AssettoCorsaActiveEvent::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $instance;

    /**
     * @ORM\ManyToOne(targetEntity=AssettoCorsaAssociatedName::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $driver;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstance(): ?AssettoCorsaActiveEvent
    {
        return $this->instance;
    }

    public function setInstance(?AssettoCorsaActiveEvent $instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    public function getDriver(): ?AssettoCorsaAssociatedName
    {
        return $this->driver;
    }

    public function setDriver(?AssettoCorsaAssociatedName $driver): self
    {
        $this->driver = $driver;

        return $this;
    }
}
