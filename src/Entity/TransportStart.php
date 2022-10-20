<?php

namespace App\Entity;

use App\Repository\TransportStartRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransportStartRepository::class)]
class TransportStart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['TRANSPORT_RUN_PUBLIC', 'TRANSPORT_PUBLIC'])]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'transportStart')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['TRANSPORT_RUN_PUBLIC'])]
    private Transport $transport;

    #[ORM\Column]
    #[Groups(['TRANSPORT_RUN_PUBLIC', 'TRANSPORT_PUBLIC'])]
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
        sort($times);
        $this->times = $times;

        return $this;
    }
}
