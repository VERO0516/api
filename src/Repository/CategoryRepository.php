<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLastThree(): array
    {
       return $this->createQueryBuilder('c')
           ->select('c.id', 'c.title')
           ->orderBy('c.id', 'DESC')
           ->setMaxResults(3)
           ->getQuery()
           ->getResult()
       ;
    }

   public function findOneById($value)
      {
          return $this->createQueryBuilder('c')
            ->select('c.id', 'c.title','a.id as article_id', 'a.title as article_title')
            ->leftJoin('c.articles', 'a')
            ->andWhere('c.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
          ;
      }

      public function getCategoryWithArticles($value)
      {
            $category = $this->createQueryBuilder('c')
            ->select('c.id', 'c.title')
            ->andWhere('c.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;

            $articles = $this->createQueryBuilder('c')
            ->select('a.id as id', 'a.title as title', 'a.content as content','u.lastName as author')
            ->leftJoin('c.articles', 'a')
            ->leftJoin('a.author', 'u')
            ->andWhere('c.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
          ;

        $data=[];

            if($category){

                $data = [
                    'id' => $category['id'],
                    'title' => $category['title'],
                ];

                foreach ($articles as $article) {

                    if($article['id']){
                        $data['article'][] = $article;
                    }
                    
                }

            }
       
        return $data;

      }

//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
