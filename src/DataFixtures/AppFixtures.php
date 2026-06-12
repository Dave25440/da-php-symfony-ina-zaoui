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
    private ObjectManager $manager;

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $userDescription = "Le maître de l'urbanité capturée, explore les méandres des cités avec un regard vif et impétueux, figeant l'énergie des rues dans des instants éblouissants. À travers une technique avant-gardiste, il métamorphose le béton et l'acier en toiles abstraites, révélant l'essence même de l'architecture moderne. Ses clichés transcendent les formes familières pour révéler des perspectives inattendues, offrant une vision nouvelle et captivante du monde urbain.";

        // Création de l'admin
        $admin = new User();
        $admin->setEmail('ina@zaoui.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($admin, 'password'))
            ->setName('Ina Zaoui');

        $manager->persist($admin);

        // Création des invité·es
        $users = $this->createUsers($userDescription);

        // Création des albums
        $albums = [];

        for ($i = 1; $i <= 5; $i++) {
            $album = new Album();
            $album->setName('Album ' . $i);

            $manager->persist($album);
            $albums[] = $album;
        }

        // Création des médias admin
        for ($i = 0; $i < 50; $i++) {
            $albumIndex = intdiv($i, 10);

            $this->createMedia($admin, $i + 1, $i, $albums[$albumIndex]);
        }

        // Création des médias invité·es
        foreach ($users as $u => $user) {
            for ($i = 0; $i < 50; $i++) {
                $mediaNumber = 50 + $u * 50 + $i + 1;

                $this->createMedia($user, $mediaNumber, $i);
            }
        }

        $manager->flush();
    }

    /**
     * Crée les invité·es.
     *
     * @param string $desc
     * @return array<User>
     */
    private function createUsers(string $desc): array
    {
        $users = [];

        for ($i = 0; $i < 100; $i++) {
            $user = new User();
            $user->setEmail('invite+' . $i . '@example.com')
                ->setPassword($this->hasher->hashPassword($user, 'password'))
                ->setName('Invité ' . $i);

            if ($i % 2 === 0) {
                $user->setRoles(['ROLE_GUEST']);

                if ($i <= 50) {
                    $user->setDescription($desc);
                }
            } elseif ($i > 50) {
                $user->setDescription($desc);
            }

            $this->manager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    /**
     * Crée un média.
     *
     * @param User $user
     * @param int $mediaNumber
     * @param int $titleIndex
     * @param Album|null $album
     */
    private function createMedia(User $user, int $mediaNumber, int $titleIndex, ?Album $album = null): void
    {
        $media = new Media();
        $media->setUser($user)
            ->setAlbum($album)
            ->setPath('uploads/' . str_pad((string) $mediaNumber, 4, '0', STR_PAD_LEFT) . '.webp')
            ->setTitle('Titre ' . $titleIndex);

        $this->manager->persist($media);
    }
}
