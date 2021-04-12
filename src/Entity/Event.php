<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

//@TODO: validation !

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Veuillez donner un nom à la sortie !")
     * @Assert\Length(
     *     min=2,
     *     minMessage="2 caractères minimum svp !",
     *     max=255,
     *     maxMessage="255 caractères minimum svp !"
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Assert\GreaterThan("today", message="La date de début doit être dans le futur !")
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @Assert\GreaterThanOrEqual(1, message="La sortie doit durer au moins une heure !")
     * @Assert\LessThanOrEqual(168, message="La sortie doit durer au maximum 168 heures (une semaine) !")
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    /**
     * @Assert\LessThanOrEqual(propertyPath="startDate", message="La date de fin des inscriptions doit être avant le début de la sortie !")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registrationLimitDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxRegistrations;

    /**
     * @ORM\Column(type="text")
     */
    private $infos;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EventState", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="createdEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EventSubscription", mappedBy="event")
     */
    private $subscriptions;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EventCancelation", mappedBy="event", cascade={"persist", "remove"})
     */
    private $cancelation;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRegistrationLimitDate(): ?\DateTimeInterface
    {
        return $this->registrationLimitDate;
    }

    public function setRegistrationLimitDate(?\DateTimeInterface $registrationLimitDate): self
    {
        $this->registrationLimitDate = $registrationLimitDate;

        return $this;
    }

    public function getMaxRegistrations(): ?int
    {
        return $this->maxRegistrations;
    }

    public function setMaxRegistrations(?int $maxRegistrations): self
    {
        $this->maxRegistrations = $maxRegistrations;

        return $this;
    }

    public function getInfos(): ?string
    {
        return $this->infos;
    }

    public function setInfos(string $infos): self
    {
        $this->infos = $infos;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getState(): ?EventState
    {
        return $this->state;
    }

    public function setState(?EventState $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?UserInterface $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|EventSubscription[]
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(EventSubscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setEvent($this);
        }

        return $this;
    }

    public function removeSubscription(EventSubscription $subscription): self
    {
        if ($this->subscriptions->contains($subscription)) {
            $this->subscriptions->removeElement($subscription);
            // set the owning side to null (unless already changed)
            if ($subscription->getEvent() === $this) {
                $subscription->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * Teste si un User est inscrit à cette sortie
     *
     * @param UserInterface $user
     * @return bool
     */
    public function isSubscribed(UserInterface $user): bool
    {
        foreach($this->getSubscriptions() as $sub){
            if ($sub->getUser()->getId() == $user->getId()){
                return true;
            }
        }

        return false;
    }

    /**
     * Teste si cette sortie est complète
     *
     * @return bool
     */
    public function isMaxedOut(): bool
    {
        if ($this->getMaxRegistrations() && $this->getSubscriptions()->count() >= $this->getMaxRegistrations()){
            return true;
        }

        return false;
    }

    /**
     * Calcule la date de fin de l'événement en fonction de sa durée
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function getEndDate(): \DateTimeInterface
    {
        $endDate = clone $this->getStartDate();

        if ($this->getDuration()){
            $durationInterval = new \DateInterval("PT".$this->getDuration()."H");
            $endDate = $endDate->add($durationInterval);
        }
        else {
            $endDate->setTime(23, 59, 59);
        }
        return $endDate;
    }

    public function getCancelation(): ?EventCancelation
    {
        return $this->cancelation;
    }

    public function setCancelation(EventCancelation $cancelation): self
    {
        $this->cancelation = $cancelation;

        // set the owning side of the relation if necessary
        if ($cancelation->getEvent() !== $this) {
            $cancelation->setEvent($this);
        }

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }
}
