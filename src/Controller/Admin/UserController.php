<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
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
}
