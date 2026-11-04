<?php

namespace App\Entity;

use App\Repository\TypeRepository;

use App\Entity\Compound;
use App\Entity\TypeGroup;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
#[ORM\Table(name: "types")]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: "types")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?TypeGroup $group = null;

    #[ORM\ManyToMany(targetEntity: Compound::class, mappedBy: "types")]
    private Collection $compounds;

    public function __construct()
    {
        $this->compounds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getGroup(): ?TypeGroup
    {
        return $this->group;
    }

    public function setGroup(?TypeGroup $group): static
    {
        $this->group = $group;
        return $this;
    }

    /** @return Collection<int, Compound> */
    public function getCompounds(): Collection
    {
        return $this->compounds;
    }

    public function addCompound(Compound $compound): self
    {
        if (!$this->compounds->contains($compound)) {
            $this->compounds[] = $compound;
            $compound->addType($this);
        }
        return $this;
    }

    public function removeCompound(Compound $compound): self
    {
        if ($this->compounds->removeElement($compound)) {
            $compound->removeType($this);
        }
        return $this;
    }
}
