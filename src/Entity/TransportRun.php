<?php

namespace App\Entity;

use App\Repository\TransportRunsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransportRunsRepository::class)]
class TransportRun
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['TRANSPORT_RUN_PUBLIC', 'TRANSPORT_PUBLIC'])]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'transportRuns')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['TRANSPORT_RUN_PUBLIC', 'TRANSPORT_PUBLIC'])]
    private TransportStop $transportStop;

    #[ORM\ManyToOne(inversedBy: 'transportRuns')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['TRANSPORT_RUN_PUBLIC'])]
    private Transport $transport;

    #[ORM\Column]
    #[Groups(['TRANSPORT_RUN_PUBLIC', 'TRANSPORT_PUBLIC'])]
    private int $arrivalTime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransportStop(): ?TransportStop
    {
        return $this->transportStop;
    }

    public function setTransportStop(?TransportStop $transportStop): self
    {
        $this->transportStop = $transportStop;

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

    public function getArrivalTime(): int
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(int $arrivalTime): self
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }
}
