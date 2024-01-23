<?php

namespace Enhavo\Bundle\ContactBundle\Model;


use DateTime;
use Sylius\Component\Resource\Model\ResourceInterface;

interface ContactInterface extends ResourceInterface
{
    public function getForm(): ?string;
    public function setForm(?string $form): void;
    public function getCreatedAt(): ?DateTime;
    public function setCreatedAt(?DateTime $createdAt): void;
    public function getToken(): ?string;
    public function setToken(?string $token): void;
    public function getEmail(): ?string;
    public function setEmail(?string $email): void;
    public function getFirstName(): ?string;
    public function setFirstName(?string $firstName): void;
    public function getLastName(): ?string;
    public function setLastName(?string $lastName): void;
    public function getMessage(): ?string;
    public function setMessage(?string $message): void;
}
