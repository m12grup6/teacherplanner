<?php

namespace App\Entity;

use App\Repository\RestrictionsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RestrictionsRepository::class)
 */
class Restrictions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $day;

    /**
     * @ORM\Column(type="datetime")
     */
    private $hora_inici;

    /**
     * @ORM\Column(type="datetime")
     */
    private $hora_fi;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDay(): ?string
    {
        return $this->day;
    }

    public function setDay(string $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getHoraInici(): ?\DateTimeInterface
    {
        return $this->hora_inici;
    }

    public function setHoraInici(\DateTimeInterface $hora_inici): self
    {
        $this->hora_inici = $hora_inici;

        return $this;
    }

    public function getHoraFi(): ?\DateTimeInterface
    {
        return $this->hora_fi;
    }

    public function setHoraFi(\DateTimeInterface $hora_fi): self
    {
        $this->hora_fi = $hora_fi;

        return $this;
    }
}
