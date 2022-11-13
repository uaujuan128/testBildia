<?php

namespace App\Entity;

use App\Repository\MunicipalityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MunicipalityRepository::class)]
class Municipality
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $latitude = null;

    #[ORM\Column]
    private ?float $longitude = null;

    #[ORM\ManyToOne(inversedBy: 'municipalities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Province $province = null;


    /**
     * @param int|null $id
     * @param string|null $slug
     * @param string|null $name
     * @param float|null $latitude
     * @param float|null $longitude
     * @param Province|null $province
     */



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
    public function setId(?int $id): Municipality
    {
        $this->id = $id;

        return $this;
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
    public function setSlug(?string $slug): Municipality
    {
        $this->slug = $slug;

        return $this;
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
    public function setName(?string $name): Municipality
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @param float|null $latitude
     */
    public function setLatitude(?float $latitude): Municipality
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @param float|null $longitude
     */
    public function setLongitude(?float $longitude): Municipality
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Province|null
     */
    public function getProvince(): ?Province
    {
        return $this->province;
    }

    /**
     * @param Province|null $province
     */
    public function setProvince(?Province $province): Municipality
    {
        $this->province = $province;

        return $this;
    }


}
