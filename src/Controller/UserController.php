<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/login', name: 'login',methods:['POST'])]
    public function login(Request $r, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['email' => $r->get('email')]);

        if($user == null){
            return new JsonResponse('Utilisateur introuvable',400);
        }

        if($r->get('mdp') == null || !$userPasswordHasher->isPasswordValid($user, $r->get('mdp'))){
            return new JsonResponse('Identifiants invalides',400);
        }

        return new JsonResponse('success',200);
    }
}
