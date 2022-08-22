<?php

namespace App\Entity;

use App\Repository\TransportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TransportRepository::class)]
#[UniqueEntity(fields: ["number", "type"], message: "There is already a route with this number and type")]
class Transport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['TRANSPORT_PUBLIC', 'TRANSPORT_RUN_PUBLIC'])]
    private int $id;

    #[Assert\NotBlank]
    #[ORM\Column]
    #[Groups(['TRANSPORT_PUBLIC', 'TRANSPORT_RUN_PUBLIC'])]
    private int $number;


    #[ORM\Column]
    #[Groups(['TRANSPORT_PUBLIC'])]
    private bool $active = true;

    #[ORM\ManyToOne(inversedBy: 'transports')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['TRANSPORT_PUBLIC', 'TRANSPORT_RUN_PUBLIC'])]
    private TransportType $type;

    #[ORM\OneToMany(mappedBy: 'transport', targetEntity: TransportRun::class, orphanRemoval: true)]
    #[Groups(['TRANSPORT_PUBLIC'])]
    private ?Collection $transportRuns = null;

    #[ORM\OneToOne(mappedBy: 'transport', cascade: ['persist', 'remove'])]
    #[Groups(['TRANSPORT_PUBLIC'])]
    private ?TransportStart $transportStart = null;

    public function __construct()
    {
        $this->transportRuns = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getType(): ?TransportType
    {
        return $this->type;
    }

    public function setType(?TransportType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, TransportRun>
     */
    public function getTransportRuns(): Collection
    {
        return $this->transportRuns;
    }

    public function addTransportRun(TransportRun $transportRun): self
    {
        if (!$this->transportRuns->contains($transportRun)) {
            $this->transportRuns->add($transportRun);
            $transportRun->setTransport($this);
        }

        return $this;
    }

    public function removeTransportRun(TransportRun $transportRun): self
    {
        if ($this->transportRuns->removeElement($transportRun)) {
            // set the owning side to null (unless already changed)
            if ($transportRun->getTransport() === $this) {
                $transportRun->setTransport(null);
            }
        }

        return $this;
    }

    public function getTransportStart(): ?TransportStart
    {
        return $this->transportStart;
    }

    public function setTransportStart(TransportStart $transportStart): self
    {
        // set the owning side of the relation if necessary
        if ($transportStart->getTransport() !== $this) {
            $transportStart->setTransport($this);
        }

        $this->transportStart = $transportStart;

        return $this;
    }
}
