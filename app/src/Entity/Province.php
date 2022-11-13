<?php

namespace App\Entity;

use App\Repository\ProvinceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProvinceRepository::class)]
class Province
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'province', targetEntity: Municipality::class, orphanRemoval: true)]
    private Collection $municipalities;

    #[ORM\ManyToOne(inversedBy: 'provinces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Community $community = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Municipality $capital = null;

    #[ORM\Column]
    private ?int $population = null;

    public function __construct()
    {
        $this->municipalities = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getMunicipalities(): ArrayCollection|Collection
    {
        return $this->municipalities;
    }

    /**
     * @param ArrayCollection|Collection $municipalities
     */
    public function setMunicipalities(ArrayCollection|Collection $municipalities): void
    {
        $this->municipalities = $municipalities;
    }

    /**
     * @return Community|null
     */
    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    /**
     * @param Community|null $community
     */
    public function setCommunity(?Community $community): void
    {
        $this->community = $community;
    }

    /**
     * @return Municipality|null
     */
    public function getCapital(): ?Municipality
    {
        return $this->capital;
    }

    /**
     * @param Municipality|null $capital
     */
    public function setCapital(?Municipality $capital): void
    {
        $this->capital = $capital;
    }

    /**
     * @return int|null
     */
    public function getPopulation(): ?int
    {
        return $this->population;
    }

    /**
     * @param int|null $population
     */
    public function setPopulation(?int $population): void
    {
        $this->population = $population;
    }
}
