<?php

namespace Enhavo\Bundle\ContactBundle\Model;


use DateTime;

class Contact implements ContactInterface
{
    private ?int $id = null;
    private ?string $form = null;
    private ?DateTime $createdAt = null;
    private ?string $token = null;
    private ?string $email;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $message;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getForm(): ?string
    {
        return $this->form;
    }

    public function setForm(?string $form): void
    {
        $this->form = $form;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(?bool $confirmed): void
    {
        $this->confirmed = $confirmed;
    }

    public function getFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(?bool $finished): void
    {
        $this->finished = $finished;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }


}
