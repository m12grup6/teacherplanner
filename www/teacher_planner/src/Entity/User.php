<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="L'usuari ja existeix"
 * )
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\Regex(
     *     pattern="/\d/",
     *     match=false,
     *     message="El nom no pot contenir numeros"
     * )
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_active;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updated_at;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $teaching_hours;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $teacher_constraints = [];

    /**
     * @ORM\OneToMany(targetEntity=Schedule::class, mappedBy="teacher")
     */
    private $schedules;

    /**
     * @ORM\ManyToMany(targetEntity=Subject::class, inversedBy="users")
     */
    private $subjects;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
        $this->subjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getTeachingHours(): ?int
    {
        return $this->teaching_hours;
    }

    public function setTeachingHours(?int $teaching_hours): self
    {
        $this->teaching_hours = $teaching_hours;

        return $this;
    }

    public function getTeacherConstraints(): ?array
    {
        return $this->teacher_constraints;
    }

    public function setTeacherConstraints(?array $teacher_constraints): self
    {
        $this->teacher_constraints = $teacher_constraints;

        return $this;
    }

    public function getSchedules(): ?array 
    {
        $schedules = $this->schedules;
        $orderedSchedules = [];
        $primaria = [];
        $secundaria = [];
        foreach ($schedules as $key => $schedule) {        
            switch ($schedule->getDay()) {
                case 'monday':
                    $schedules[$key]->setDay('Dilluns');
                    break;
                case 'tuesday':
                    $schedules[$key]->setDay('Dimarts');
                    break;
                case 'wednesday':
                    $schedules[$key]->setDay('Dimecres');
                    break;
                case 'thursday':
                    $schedules[$key]->setDay('Dijous');
                    break;
                case 'friday':
                    $schedules[$key]->setDay('Divendres');
                    break;
            }

            $hours = array();
            $franja = explode('-', $schedule->getHour());
            foreach ($franja as $hora) {
                $timeParts = explode(':', $hora);
                $hours[] = $timeParts[0];
            }
            $schedules[$key]->setHour(join('-', $hours));            
            $curs = $schedule->getSubject()->getCourse()->getName();
            $cicle = $schedule->getSubject()->getCourse()->getCicle();   
            if ($cicle == 'Primaria') {
                $primaria['Prim??ria'][$curs][] = $schedule;
            } else if ($cicle == 'Secundaria') {
                $secundaria['Secund??ria'][$curs][] = $schedule;
            }
        }     
        $orderedSchedules = array_merge($primaria, $secundaria);
        return $orderedSchedules;
    }

    public function addSchedule(Schedule $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules[] = $schedule;
            $schedule->setTeacher($this);
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getTeacher() === $this) {
                $schedule->setTeacher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Subject[]
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject): self
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects[] = $subject;
        }

        return $this;
    }

    public function removeSubject(Subject $subject): self
    {
        $this->subjects->removeElement($subject);

        return $this;
    }
}
