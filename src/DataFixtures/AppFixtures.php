<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $userDescription = "Le maître de l'urbanité capturée, explore les méandres des cités avec un regard vif et impétueux, figeant l'énergie des rues dans des instants éblouissants. À travers une technique avant-gardiste, il métamorphose le béton et l'acier en toiles abstraites, révélant l'essence même de l'architecture moderne. Ses clichés transcendent les formes familières pour révéler des perspectives inattendues, offrant une vision nouvelle et captivante du monde urbain.";
        $users = [];

        $admin = new User();
        $admin->setEmail('ina@zaoui.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $admin->setName('Ina Zaoui');
        $manager->persist($admin);

        for ($u = 0; $u < 100; $u++) {
            $user = new User();

            $user->setEmail('invite+' . $u . '@example.com');
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setName('Invité ' . $u);

            if ($u % 2 === 0) {
                $user->setRoles(['ROLE_GUEST']);

                if ($u <= 50) {
                    $user->setDescription($userDescription);
                }
            } elseif ($u > 50) {
                $user->setDescription($userDescription);
            }

            $manager->persist($user);
            $users[] = $user;
        }

        $albums = [];

        for ($a = 1; $a <= 5; $a++) {
            $album = new Album();
            $album->setName('Album ' . $a);

            $manager->persist($album);
            $albums[] = $album;
        }

        for ($m = 0; $m < 50; $m++) {
            $media = new Media();
            $media->setUser($admin);

            $album = intdiv($m, 10);
            $media->setAlbum($albums[$album]);

            $path = 'uploads/' . str_pad((string) ($m + 1), 4, '0', STR_PAD_LEFT) . '.jpg';
            $media->setPath($path);
            $media->setTitle('Titre ' . $m);

            $manager->persist($media);
        }

        foreach ($users as $u => $user) {
            for ($m = 0; $m < 50; $m++) {
                $mediaNumber = 50 + $u * 50 + $m + 1;

                $media = new Media();
                $media->setUser($user);

                $path = 'uploads/' . str_pad((string) $mediaNumber, 4, '0', STR_PAD_LEFT) . '.jpg';
                $media->setPath($path);
                $media->setTitle('Titre ' . $m);

                $manager->persist($media);
            }
        }

        $manager->flush();
    }
}
