<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTime;

class CommentController extends AbstractController
{
    // #[Route('/comment', name: 'app_comment')]
    // public function index(): Response
    // {
    //     return $this->render('comment/index.html.twig', [
    //         'controller_name' => 'CommentController',
    //     ]);
    // }

    #[Route('/comment/{id}', name: 'comment_add',methods:['POST'])]
    public function add($id,Request $r, EntityManagerInterface $em, Validator $v): Response
    {
        $article = $em->getRepository(Article::class)->findOneBy(['id' => $id]);

        if($article == null){
            return new JsonResponse('Article introvable', 404); 
        }

        $headers = $r->headers->all();
        if(isset($headers['token']) && !empty($headers['token'])){

            $jwt =current($headers['token']);
            $key = $this->getParameter('jwt_secret');

            try{
                $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

            }
            catch(\Exception $e){
                return new JsonResponse($e->getMessage(),403);
            }

            if( $decoded->roles != null && in_array('ROLE_USER',$decoded->roles)){
                
                $comment = new Comment();
                $comment->setComment($r->get('comment') );

                $time = new DateTime();
                $comment->setPublicDate($time);

                $comment->setArticle($article);
                $comment->setStatus(true);

                $userid = $decoded->userid;

                if($userid == null){
                    return new JsonResponse('User introvable', 400); 
                }
                $user = $em->getRepository(User::class)->findOneBy(['id' => $userid]);

                $comment->setAuthor($user);


                $isValid = $v->isValid($comment);

                if($isValid != true){
                    return new JsonResponse($isValid,400);
                }
                
                $em->persist($comment);
                $em->flush();

                return new JsonResponse('Le commentaire a bien été enregistré',200); 

            }


        }
        return new JsonResponse('Access denied',403); 

    }
    #[Route('/comment/{id}', name: 'app_comment',methods:['GET'])]
    public function comment($id, EntityManagerInterface $em,Request $r): Response
    {
            $comment = $em->getRepository(Comment::class)->findOneById($id);

            if($comment == null){

                return new JsonResponse('Comment introuveble',404); 
            }

            $headers = $r->headers->all();
            if(isset($headers['token']) && !empty($headers['token'])){

                $jwt =current($headers['token']);
                $key = $this->getParameter('jwt_secret');

                try{
                    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

                }
                catch(\Exception $e){
                    return new JsonResponse($e->getMessage(),403);
                }

                if( $decoded->roles != null && in_array('ROLE_ADMIN',$decoded->roles)){

                    return new JsonResponse($comment,200);
                }
            }
            return new JsonResponse('Access denied',403); 
    }
    
    #[Route('/comment/{id}', name: 'comment_upload',methods:['PATCH'])]
    public function upload(Comment $comment = null, Request $r, EntityManagerInterface $em, Validator $v): Response
    {
        if($comment == null){
            return new JsonResponse('Comment introuveble',404); 
        }

        $headers = $r->headers->all();
        if(isset($headers['token']) && !empty($headers['token'])){

            $jwt =current($headers['token']);
            $key = $this->getParameter('jwt_secret');

            try{
                $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

            }
            catch(\Exception $e){
                return new JsonResponse($e->getMessage(),403);
            }

            if( $decoded->roles != null && in_array('ROLE_ADMIN',$decoded->roles)){

                $params = 0;

                if($r->get('status') != null){
                    $params++;
                    $comment->setStatus($r->get('status'));
                }
        
                if($params > 0){
        
                    $isValid = $v->isValid($comment);
        
                    if($isValid != true){
                        return new JsonResponse($isValid,400);
                    }
        
                    $em->persist($comment);
                    $em->flush();
        
                    return new JsonResponse('ok',200); 
        
                }else{
                    return new JsonResponse('Aucune donnée reçue',201); 
                }
            }

        }
        return new JsonResponse('Access denied',403); 
    }
}
