<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use PHPTokenGenerator\TokenGenerator;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ForgotPasswordTokenRepository")
 */
class ForgotPasswordToken
{
    /**
     * On génère absolument tout ce qu'il faut depuis ici même !
     * En enlevant tous les setters, cet objet est donc immuable
     *
     */
    public function __construct(User $user)
    {
        $generator = new TokenGenerator();
        $token = $generator->generate(25);
        $this->clearToken = $token;
        $this->token = password_hash($this->clearToken, PASSWORD_DEFAULT);
        $this->selector = $generator->generate(25);
        $this->dateCreated = new \DateTime();

        $this->user = $user;
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $selector;

    //pas sauvegardé en bdd
    private $clearToken;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSelector(): ?string
    {
        return $this->selector;
    }

    public function getClearToken(): ?string
    {
        return $this->clearToken;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
