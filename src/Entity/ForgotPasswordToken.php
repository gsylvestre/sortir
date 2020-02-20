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
    public function __construct()
    {
        $generator = new TokenGenerator();
        $token = $generator->generate(100);
        $hash = password_hash($token, PASSWORD_DEFAULT);

        $this->token = $hash;
        $this->selector = $generator->generate(40);
        $this->dateCreated = new \DateTime();
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

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSelector(): ?string
    {
        return $this->selector;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }
}
