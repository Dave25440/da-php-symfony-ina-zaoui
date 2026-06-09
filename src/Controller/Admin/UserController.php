<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $manager,
    ) {}

    #[Route('/admin/user', name: 'admin_user_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 25;

        $users = $this->userRepository->findByRole(
            'ROLE_ADMIN',
            false,
            $limit,
            $limit * ($page - 1)
        );

        $total = $this->userRepository->countByRole('ROLE_ADMIN', false);

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * Désactive l'accès de l'invité·e.
     * 
     * @param User $user
     * @return Response
     */
    #[Route('/admin/user/{id}/guest/disable', name: 'admin_user_guest_disable', methods: ['POST'])]
    public function disableGuestAccess(User $user, Request $request): Response
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_GUEST', $roles, true)) {
            $roles = array_diff($roles, ['ROLE_GUEST']);
            $roles = array_values($roles);
            $user->setRoles($roles);

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
     * @return Response
     */
    #[Route('/admin/user/{id}/guest/enable', name: 'admin_user_guest_enable', methods: ['POST'])]
    public function enableGuestAccess(User $user, Request $request): Response
    {
        $roles = $user->getRoles();

        if (!in_array('ROLE_GUEST', $roles, true)) {
            $roles[] = 'ROLE_GUEST';
            $roles = array_values($roles);
            $user->setRoles($roles);

            $this->manager->persist($user);
            $this->manager->flush();
        }

        $page = $request->request->get('page', 1);

        return $this->redirectToRoute('admin_user_index', ['page' => $page]);
    }
}
