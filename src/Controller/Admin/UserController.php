<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    private int $limit = 25;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $manager,
    ) {}

    /**
     * Affiche la liste des invité·es.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/user', name: 'admin_user_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $totalPages = $this->getTotalPages();
        $page = max(1, min($page, $totalPages));

        $users = $this->userRepository->findByRole(
            'ROLE_ADMIN',
            false,
            $this->limit,
            $this->limit * ($page - 1)
        );

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Ajoute un·e invité·e.
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @return RedirectResponse|Response
     */
    #[Route('/admin/user/add', name: 'admin_user_add', methods: ['GET', 'POST'])]
    public function add(Request $request, UserPasswordHasherInterface $userPasswordHasher): RedirectResponse|Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_GUEST']);

            $this->manager->persist($user);
            $this->manager->flush();

            return $this->redirectToRoute('admin_user_index', ['page' => $this->getTotalPages()]);
        }

        return $this->render('admin/user/add.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Désactive l'accès de l'invité·e.
     *
     * @param User $user
     * @param Request $request
     * @return RedirectResponse
     */
    #[Route('/admin/user/guest/disable/{id}', name: 'admin_user_guest_disable', methods: ['POST'])]
    public function disableGuestAccess(User $user, Request $request): RedirectResponse
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_GUEST', $roles, true)) {
            $user->setRoles([]);

            $this->manager->persist($user);
            $this->manager->flush();
        }

        $page = $request->request->get('page', 1);

        return $this->redirectToRoute('admin_user_index', ['page' => $page]);
    }

    /**
     * Active l'accès de l'invité·e.
     *
     * @param User $user
     * @param Request $request
     * @return RedirectResponse
     */
    #[Route('/admin/user/guest/enable/{id}', name: 'admin_user_guest_enable', methods: ['POST'])]
    public function enableGuestAccess(User $user, Request $request): RedirectResponse
    {
        $roles = $user->getRoles();

        if (!in_array('ROLE_GUEST', $roles, true)) {
            $user->setRoles(['ROLE_GUEST']);

            $this->manager->persist($user);
            $this->manager->flush();
        }

        $page = $request->request->get('page', 1);

        return $this->redirectToRoute('admin_user_index', ['page' => $page]);
    }

    /**
     * Supprime l'invité·e.
     *
     * @param User $user
     * @param Request $request
     * @return RedirectResponse
     */
    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete', methods: ['DELETE'])]
    public function delete(User $user, Request $request): RedirectResponse
    {
        $this->manager->remove($user);
        $this->manager->flush();

        $page = $request->request->get('page', 1);

        return $this->redirectToRoute('admin_user_index', ['page' => $page]);
    }

    /**
     * Récupère le nombre total de pages.
     *
     * @return int
     */
    private function getTotalPages(): int
    {
        $total = $this->userRepository->countByRole('ROLE_ADMIN', false);

        return max(1, (int) ceil($total / $this->limit));
    }
}
