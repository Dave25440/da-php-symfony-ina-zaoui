<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AlbumRepository $albumRepository,
        private readonly MediaRepository $mediaRepository,
    ) {}

    /**
     * Affiche la page d'accueil.
     *
     * @return Response
     */
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    /**
     * Affiche la page "Invités".
     *
     * @return Response
     */
    #[Route('/guests', name: 'guests', methods: ['GET'])]
    public function guests(): Response
    {
        $guests = $this->userRepository->findByRole('ROLE_GUEST', true);

        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    /**
     * Affiche la page de l'invité·e.
     *
     * @param User $guest
     * @return Response
     */
    #[Route('/guests/{id}', name: 'guest', methods: ['GET'])]
    public function guest(User $guest): Response
    {
        if (!in_array('ROLE_GUEST', $guest->getRoles(), true)) {
            throw $this->createNotFoundException('Invité·e introuvable.');
        }

        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    /**
     * Affiche la page "Portfolio".
     *
     * @param Album|null $album
     * @return Response
     */
    #[Route('/portfolio/{id?}', name: 'portfolio', methods: ['GET'])]
    public function portfolio(?Album $album = null): Response
    {
        $albums = $this->albumRepository->findAll();
        $user = $this->userRepository->findByRole('ROLE_ADMIN', true, 1);
        $user = $user[0] ?? null;

        $medias = $album !== null
            ? $this->mediaRepository->findByAlbum($album)
            : $this->mediaRepository->findByUser($user);

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    /**
     * Affiche la page "Qui suis-je ?".
     *
     * @return Response
     */
    #[Route('/about', name: 'about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
