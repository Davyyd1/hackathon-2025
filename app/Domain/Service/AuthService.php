<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function register(string $username, string $password): User
    {
        // TODO: check that a user with same username does not exist, create new user and persist
        $dbUser = $this->users->findByUsername($username);
        if($dbUser !== null){
            throw new \RuntimeException('Username already taken');
        };
        if(strlen($username) < 4) {
            throw new \RuntimeException('Username must have minimum 4 characters length');
        }
        if(strlen($password) < 8 || !preg_match('/\d/', $password) || !preg_match('/[a-zA-Z]/', $password)) {
            throw new \RuntimeException('Password must contain minimum 8 characters including 1 number');
        }

        // TODO: make sure password is not stored in plain, and proper PHP functions are used for that
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // TODO: here is a sample code to start with
        $user = new User(null, $username, $hashedPassword, new \DateTimeImmutable());
        $this->users->save($user);

        return $user;
    }

    public function attempt(string $username, string $password): bool
    {
        // TODO: implement this for authenticating the user
        // TODO: make sure the user exists and the password matches
        // TODO: don't forget to store in session user data needed afterwards

        return true;
    }
}
