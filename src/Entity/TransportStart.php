<?php

namespace App\Entity;

use App\Repository\TransportStartRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransportStartRepository::class)]
class TransportStart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'transportStart', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Transport $transport;

    #[ORM\Column]
    private array $times = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    public function setTransport(Transport $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function getTimes(): array
    {
        return $this->times;
    }

    public function setTimes(array $times): self
    {
        $this->times = $times;

        return $this;
    }
}
