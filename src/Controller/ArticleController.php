<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ArticleController extends AbstractController
{
    // #[Route('/article', name: 'app_article')]
    // public function index(): Response
    // {
    //     return $this->render('article/index.html.twig', [
    //         'controller_name' => 'ArticleController',
    //     ]);
    // }

    #[Route('/article', name: 'article_add',methods:['POST'])]
    public function add(Request $r, EntityManagerInterface $em, Validator $v): Response
    {

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

                $article = new Article();
                $article->setTitle($r->get('title') )
                ->setContent($r->get('content') )
                ->setState($r->get('state') );

                if($r->get('state') == true){
                    $time = new DateTime();
                    $article->setReleaseDate($time);
                }

                $userid = $decoded->userid;

                if($userid == null){
                    return new JsonResponse('User introvable', 400); 
                }
                $user = $em->getRepository(User::class)->findOneBy(['id' => $userid]);

                $article->setAuthor($user);

                $categoryId = $r->get('category');
                $category = $em->getRepository(Category::class)->findOneBy(['id' => $categoryId]);

                if($category == null){
                    return new JsonResponse('Categorie introvable', 400); 
                }

                $article->setCategory( $category );

                $isValid = $v->isValid($article);

                if($isValid != true){
                    return new JsonResponse($isValid,400);
                }
                
                $em->persist($article);
                $em->flush();

                return new JsonResponse('ok',200); 


            }
        }
        return new JsonResponse('Access denied',403); 
        
    }

    #[Route('/article/{id}', name: 'article_upload',methods:['PATCH'])]
    public function upload(Article $article = null, Request $r, EntityManagerInterface $em, Validator $v) : Response
    {
        if($article == null){
            return new JsonResponse('Article introuveble',404); 
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

                if($r->get('title') != null){
                    $params++;
                    $article->setTitle($r->get('title'));
                }
                if($r->get('content') != null){
                    $params++;
                    $article->setContent($r->get('content'));
                }

                if($r->get('state') != null){
                    $params++;
                    $article->setState($r->get('state'));
                }

                if($r->get('state') == true && !$article-> getReleaseDate()){
                    $time = new DateTime();
                    $article->setReleaseDate($time);
                }

                if($r->get('author') != null){
                    $params++;
                    $authorId = $r->get('author');
                    $author = $em->getRepository(User::class)->findOnBy(['id' => $authorId]);

                    if($author == null){
                        return new JsonResponse('Autheur introuveble',204);
                    }

                    $article->setCategory($author);
                }

                if($r->get('category') != null){
                    $params++;
                    $categoryId = $r->get('category');
                    $category = $em->getRepository(Category::class)->findOnBy(['id' => $categoryId]);

                    if($category == null){
                        return new JsonResponse('Categorie introuveble',404);
                    }

                    $article->setCategory($category);
                }

                if($params > 0){

                    $isValid = $v->isValid($article);

                    if($isValid != true){
                        return new JsonResponse($isValid,400);
                    }

                    $em->persist($article);
                    $em->flush();

                    return new JsonResponse('Article a été modifié',200); 

                }else{
                    return new JsonResponse('Aucune donnée reçue',201); 
                }


            }
        }

        return new JsonResponse('Access denied',403); 
        
    }

    #[Route('/article/{id}', name: 'article_one',methods:['GET'])]
    public function article($id,EntityManagerInterface $em): Response
    {
        //$article = $em->getRepository(Article::class)->findOneById($id);
        $article = $em->getRepository(Article::class)->findCommentsByArticleId($id);

        if($article == null){
            return new JsonResponse('Article introuveble',404); 
        }

        return new JsonResponse($article,200);
    }


    #[Route('/article/{id}', name: 'article_delete',methods:['DELETE'])]
    public function delete(Article $article = null, EntityManagerInterface $em, Request $r) : Response
    {
            if($article == null){
                return new JsonResponse('Article introvable',404);
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

                    $em->remove($article);
                    $em->flush();
                    return new JsonResponse('Article suprimee',201);

                }
            }
            
            return new JsonResponse('Access denied',403); 
            
    }

}
