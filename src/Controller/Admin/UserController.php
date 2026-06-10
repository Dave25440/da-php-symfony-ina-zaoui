<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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

    #[Route('/admin/user/add', name: 'admin_user_add', methods: ['GET', 'POST'])]
    public function add(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
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

            $limit = 25;
            $total = $this->userRepository->countByRole('ROLE_ADMIN', false);
            $lastPage = (int) ceil($total / $limit);

            return $this->redirectToRoute('admin_user_index', ['page' => $lastPage]);
        }

        return $this->render('admin/user/add.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Désactive l'accès de l'invité·e.
     * 
     * @param User $user
     * @return Response
     */
    #[Route('/admin/user/guest/disable/{id}', name: 'admin_user_guest_disable', methods: ['POST'])]
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
    #[Route('/admin/user/guest/enable/{id}', name: 'admin_user_guest_enable', methods: ['POST'])]
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

    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete', methods: ['DELETE'])]
    public function delete(User $user, Request $request): Response
    {
        $this->manager->remove($user);
        $this->manager->flush();

        $page = $request->request->get('page', 1);

        return $this->redirectToRoute('admin_user_index', ['page' => $page]);
    }
}
