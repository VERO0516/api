<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function save(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAll(): array
    {
       return $this->createQueryBuilder('c')
           ->select('c.id', 'c.title')
           ->getQuery()
           ->getResult()
       ;
    }

    public function findOneById($value): array
    {
        $article =  $this->createQueryBuilder('a')
            ->select('a.id', 'a.content','a.state','a.release_date','c.title as category','u.firstName as auther')
            ->leftJoin('a.category', 'c')
            ->leftJoin('a.author', 'u')
            ->andWhere('a.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;

                return  $article;

    }

    public function findCommentsByArticleId($value): array
    {
        $article =  $this->createQueryBuilder('a')
            ->select('a.id', 'a.content','a.state','a.release_date','c.title as category','u.firstName as auther')
            ->leftJoin('a.category', 'c')
            ->leftJoin('a.author', 'u')
            ->andWhere('a.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        
        $comments = $this->createQueryBuilder('a')
            ->select('c.id as id','c.Comment as comment','c.public_date as Date','u.firstName as auther')
            ->leftJoin('a.comments', 'c')
            ->leftJoin('c.author', 'u')
            ->andWhere('a.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();

            $data=[];

            if($article){

                $data = [
                    'id' => $article['id'],
                    'content' => $article['content'],
                    'state' => $article['state'],
                    'release_date' => $article['release_date'],
                    'category' => $article['category'],
                    'auther' => $article['auther'],
    
                ];

                foreach ($comments as $comment) {

                    if($comment['id']){
                        $data['comments'][] = $comment;
                    }
                    
                }
            }
        return $data;

    }

//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
