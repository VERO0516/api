<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Service\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    // #[Route('/comment', name: 'app_comment')]
    // public function index(): Response
    // {
    //     return $this->render('comment/index.html.twig', [
    //         'controller_name' => 'CommentController',
    //     ]);
    // }

    #[Route('/comment/{id}', name: 'app_comment',methods:['POST'])]
    public function addc($id,Request $r, EntityManagerInterface $em, Validator $v): Response
    {
        $article = $em->getRepository(Article::class)->findOneBy(['id' => $id]);

        if($article == null){
            return new JsonResponse('Article introvable', 400); 
        }
        
        $comment = new Comment();
        $comment->setcomment($r->get('comment') );


        //$this->getUser()


        return new JsonResponse('ok',200); 
    }


    
}
