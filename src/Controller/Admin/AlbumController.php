<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AlbumController extends AbstractController
{
    public function __construct(
        private readonly AlbumRepository $albumRepository,
        private readonly EntityManagerInterface $manager,
    ) {}

    #[Route('/admin/album', name: 'admin_album_index', methods: ['GET'])]
    public function index(): Response
    {
        $albums = $this->albumRepository->findAll();

        return $this->render('admin/album/index.html.twig', ['albums' => $albums]);
    }

    #[Route('/admin/album/add', name: 'admin_album_add', methods: ['GET', 'POST'])]
    public function add(Request $request): RedirectResponse|Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($album);
            $this->manager->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/add.html.twig', ['form' => $form]);
    }

    #[Route('/admin/album/update/{id}', name: 'admin_album_update', methods: ['GET', 'POST'])]
    public function update(Album $album, Request $request): RedirectResponse|Response
    {
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/update.html.twig', ['form' => $form]);
    }

    #[Route('/admin/album/delete/{id}', name: 'admin_album_delete', methods: ['DELETE'])]
    public function delete(Album $album): RedirectResponse
    {
        $this->manager->remove($album);
        $this->manager->flush();

        return $this->redirectToRoute('admin_album_index');
    }
}
