<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'app_category',methods:['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Category::class)->findLastThree();
        return new JsonResponse($categories);
    }

    #[Route('/category', name: 'category_add',methods:['POST'])]
    public function add(Request $r, EntityManagerInterface $em, ValidatorInterface $v): Response
    {
        $category = new Category();
        $category->setTitle($r->get('title') );
       
        $errors = $v->validate($category);
        if(count($errors) > 0){
           
            $e_list = [];
            foreach($errors as $e){
                $e_list = $e->getMessage();
            }
            return new JsonResponse($e_list, 400); 
        }
                
        $em->persist($category);
        $em->flush();

        return new JsonResponse('success',201);
    }


    #[Route('/category/{id}', name: 'one_category',methods:['GET'])]
    public function get($id,EntityManagerInterface $em): Response
    {
        //$category = $em->getRepository(Category::class)->findOneById($id);

        $category = $em->getRepository(Category::class)->getCategoryWithArticles($id);
       
        if($category == null){
            return new JsonResponse('Categorie introuveble',204); 
        }
        return new JsonResponse($category,200); 
    }


    #[Route('/category/{id}', name: 'category_update',methods:['PATCH'])]
    public function update(Category $category = null, Request $r, ValidatorInterface $v, EntityManagerInterface $em) : Response
    {
        if($category == null){
            return new JsonResponse('Categorie introuveble',204); 
        }

        $params = 0;
        if($r->get('title') != null){
            $params++;
 
            $category->setTitle($r->get('title'));
        }

        if($params > 0){

            $errors = $v->validate($category);
            if(count($errors) > 0){
                
                $e_list = [];
                foreach($errors as $e){
                    $e_list = $e->getMessage();
                }
                return new JsonResponse($e_list, 400); 
            }

            $em->persist($category);
            $em->flush();

        }else{
            return new JsonResponse('Empty',201); 
        }

        return new JsonResponse('success',201); 
    }

    #[Route('/category/{id}', name: 'category_delete',methods:['DELETE'])]
    public function delete(Category $category = null, EntityManagerInterface $em) : Response
    {
            if($category == null){
                return new JsonResponse('Categorie introvable','404');
            }

            $em->remove($category);
            $em->flush();
            return new JsonResponse('Categorie suprimee',201);
    }
    

}
