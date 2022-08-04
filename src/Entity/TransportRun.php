<?php

namespace App\Entity;

use App\Repository\TransportRunsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransportRunsRepository::class)]
class TransportRun
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transportRuns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BusStop $busStop = null;

    #[ORM\ManyToOne(inversedBy: 'transportRuns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Transport $transport = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $arrivalTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBusStop(): ?BusStop
    {
        return $this->busStop;
    }

    public function setBusStop(?BusStop $busStop): self
    {
        $this->busStop = $busStop;

        return $this;
    }

    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    public function setTransport(?Transport $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function getArrivalTime(): ?\DateTimeInterface
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(\DateTimeInterface $arrivalTime): self
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }
}
