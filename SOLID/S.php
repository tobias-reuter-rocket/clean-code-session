<?php

// Single Responsibility Principle

// A class/method/module should have only a single responsibility
// A class/method/module should have only one reason to change.

class User
{
    private $name;

    private $email;

    private $passwordHash;

    public function __construct(string $name, string $email, string $password)
    {
        $this->name = $name;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Email invalid');
        }

        $this->email = $email;
        $this->passwordHash = sha1($password);
    }

    public function checkPassword(string $password): bool
    {
        return sha1($password) === $this->passwordHash;
    }

    public function greet()
    {
        echo 'Hello, ' . $this->name;
    }
}

class SUser
{
    private $name;

    private $email;

    private $passwordHash;

    public function __construct(string $name, string $email, string $passwordHash)
    {
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
}

class SUserValidator
{
    public function validate(array $userdata)
    {
        if (!filter_var($userdata, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Email invalid');
        }
    }
}

class SSecurityManager
{
    public function hash(string $password): string
    {
        return sha1($password);
    }

    public function validate(string $given, string $expectedHash)
    {
        return $this->hash($given) === $expectedHash;
    }
}

class SGreeter
{
    public function greet(SUser $user)
    {
        return 'Hello, ' . $user->getName();
    }
}
