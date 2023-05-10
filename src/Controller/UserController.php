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

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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

        $key = $this->getParameter('jwt_secret');
        $payload = [
            'lat' =>time(), 
            'exp' =>time() + 3600, 
            'roles' => $user->getRoles(),
            'userid' => $user->getId(),
        ];

        $jwt = JWT::encode($payload, $key,'HS256');

        return new JsonResponse($jwt,200);
    }
}
