<?php

namespace App\Entity;

use App\Repository\TransportStopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TransportStopRepository::class)]
#[UniqueEntity(fields: ["address"], message: "There is already a stop with this address")]
class TransportStop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["TRANSPORT_PUBLIC"])]
    private int $id;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(["TRANSPORT_PUBLIC"])]
    private string $address;

    #[ORM\OneToMany(mappedBy: 'transportStop', targetEntity: TransportRun::class, orphanRemoval: true)]
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
