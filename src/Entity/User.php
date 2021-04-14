<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Utilisateur du site, admin ou user
 *
 * Callback de cycle de vie Doctrine
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * Appelée automatiquement avant de faire un INSERT
     *
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        //affecte un rôle en fonction du booléen... un peu inutile ce booléen
        $role = ($this->getIsAdmin()) ? "ROLE_ADMIN" : "ROLE_USER";
        $this->setRoles([$role]);

        $this->setCreatedDate(new \DateTime());
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
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
     * @ORM\Column(type="string", length=100)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAdmin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="author")
     */
    private $createdEvents;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolSite", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $school;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EventSubscription", mappedBy="user")
     */
    private $eventSubscriptions;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;


    //Cette propriété ne sert qu'à recevoir l'objet créé par Symfony lors de l'upload du fichier
    //Et à faire la validation
    /**
     *
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 2000,
     *     minHeight = 200,
     *     maxHeight = 2000,
     *     maxSize = "20M"
     * )
     */
    private $pictureUpload;

    /**
     * @ORM\Column(type="boolean", options={"defaults": 0})
     */
    private $isDeleted = false;


    public function __construct()
    {
        $this->createdEvents = new ArrayCollection();
        $this->eventSubscriptions = new ArrayCollection();
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
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getCreatedEvents(): Collection
    {
        return $this->createdEvents;
    }

    public function addCreatedEvent(Event $createdEvent): self
    {
        if (!$this->createdEvents->contains($createdEvent)) {
            $this->createdEvents[] = $createdEvent;
            $createdEvent->setAuthor($this);
        }

        return $this;
    }

    public function removeCreatedEvent(Event $createdEvent): self
    {
        if ($this->createdEvents->contains($createdEvent)) {
            $this->createdEvents->removeElement($createdEvent);
            // set the owning side to null (unless already changed)
            if ($createdEvent->getAuthor() === $this) {
                $createdEvent->setAuthor(null);
            }
        }

        return $this;
    }

    public function getSchool(): ?SchoolSite
    {
        return $this->school;
    }

    public function setSchool(?SchoolSite $school): self
    {
        $this->school = $school;

        return $this;
    }

    /**
     * @return Collection|EventSubscription[]
     */
    public function getEventSubscriptions(): Collection
    {
        return $this->eventSubscriptions;
    }

    public function addEventSubscription(EventSubscription $eventSubscription): self
    {
        if (!$this->eventSubscriptions->contains($eventSubscription)) {
            $this->eventSubscriptions[] = $eventSubscription;
            $eventSubscription->setUser($this);
        }

        return $this;
    }

    public function removeEventSubscription(EventSubscription $eventSubscription): self
    {
        if ($this->eventSubscriptions->contains($eventSubscription)) {
            $this->eventSubscriptions->removeElement($eventSubscription);
            // set the owning side to null (unless already changed)
            if ($eventSubscription->getUser() === $this) {
                $eventSubscription->setUser(null);
            }
        }

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

    /**
     * @return UploadedFile
     */
    public function getPictureUpload(): ?UploadedFile
    {
        return $this->pictureUpload;
    }

    /**
     * @param UploadedFile $pictureUpload
     */
    public function setPictureUpload(?UploadedFile $pictureUpload): void
    {
        $this->pictureUpload = $pictureUpload;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

}
