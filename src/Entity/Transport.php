<?php

namespace App\Entity;

use App\Repository\TransportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TransportRepository::class)]
#[UniqueEntity(fields: ["number"], message: "There is already a route with this number")]
class Transport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[Assert\NotBlank]
    #[ORM\Column(length: 10, unique: true)]
    private string $number;


    #[ORM\Column]
    private bool $active = true;

    #[ORM\ManyToOne(inversedBy: 'transports')]
    #[ORM\JoinColumn(nullable: false)]
    private TransportType $type;

    #[ORM\OneToMany(mappedBy: 'transport', targetEntity: TransportRun::class, orphanRemoval: true)]
    private Collection $transportRuns;

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
}
