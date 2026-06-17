<?php

namespace App\Tests\Entity;

use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testUser(): void
    {
        $user = new User();
        $user->setEmail('email@user.com');

        self::assertSame('email@user.com', $user->getEmail());

        $user->setRoles(['ROLE_ADMIN']);
        $roles = $user->getRoles();

        self::assertContains('ROLE_ADMIN', $roles);
        self::assertContains('ROLE_USER', $roles);

        $user->setPassword('password');
        self::assertSame('password', $user->getPassword());

        $user->setName('Utilisateur');
        self::assertSame('Utilisateur', $user->getName());

        $user->setDescription('Description');
        self::assertSame('Description', $user->getDescription());

        $media = new Media();
        $user->addMedia($media);

        self::assertCount(1, $user->getMedias());
        self::assertSame($user, $media->getUser());

        $user->removeMedia($media);

        self::assertCount(0, $user->getMedias());
        self::assertNull($media->getUser());
    }

    public function testUserWithoutDesc(): void
    {
        $user = new User();
        $user->setDescription(null);

        self::assertNull($user->getDescription());
    }
}
