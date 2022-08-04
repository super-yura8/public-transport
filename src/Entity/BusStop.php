<?php

namespace App\Entity;

use App\Repository\BusStopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BusStopRepository::class)]
class BusStop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $address;

    #[ORM\OneToMany(mappedBy: 'busStop', targetEntity: TransportRun::class, orphanRemoval: true)]
    private Collection $transportRuns;

    public function __construct()
    {
        $this->transportRuns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

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
            $transportRun->setBusStop($this);
        }

        return $this;
    }

    public function removeTransportRun(TransportRun $transportRun): self
    {
        if ($this->transportRuns->removeElement($transportRun)) {
            // set the owning side to null (unless already changed)
            if ($transportRun->getBusStop() === $this) {
                $transportRun->setBusStop(null);
            }
        }

        return $this;
    }
}
