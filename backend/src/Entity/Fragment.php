<?php

namespace App\Entity;

use App\Repository\FragmentRepository;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: FragmentRepository::class)]
#[ORM\Table(name: "fragments")]
class Fragment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $canonicalSmiles = null;

    #[ORM\Column(nullable: true)]
    private ?int $molecularMass = null;

    #[ORM\ManyToMany(targetEntity: Compound::class, inversedBy: "fragments")]
    #[ORM\JoinTable(
        name: "fragments_compounds",
        joinColumns: [new ORM\JoinColumn(name: "fragment_id", referencedColumnName: "id", onDelete: "CASCADE")],
        inverseJoinColumns: [new ORM\JoinColumn(name: "compound_id", referencedColumnName: "id", onDelete: "CASCADE")]
    )]
    private Collection $compounds;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $picture = null;

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

    public function getCanonicalSmiles(): ?string
    {
        return $this->canonicalSmiles;
    }

    public function setCanonicalSmiles(?string $canonicalSmiles): static
    {
        $this->canonicalSmiles = $canonicalSmiles;

        return $this;
    }

    public function getMolecularMass(): ?int
    {
        return $this->molecularMass;
    }

    public function setMolecularMass(?int $molecularMass): static
    {
        $this->molecularMass = $molecularMass;

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
            $compound->addFragment($this);
        }
        return $this;
    }

    public function removeCompound(Compound $compound): self
    {
        if ($this->compounds->removeElement($compound)) {
            $compound->removeFragment($this);
        }
        return $this;
    }

    public function clearCompounds(): self
    {
        foreach ($this->compounds as $compound) {
            $compound->removeFragment($this);
        }
        $this->compounds = new ArrayCollection();
        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;
        return $this;
    }
}
