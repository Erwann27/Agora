<?php

namespace App\Repository\Platform;

use App\Entity\Platform\User;
use App\Data\SearchUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Results of users linked with search
     * @return User[]
     */
    public function searchUsers(SearchUser $search): array
    {
        $query = $this->createQueryBuilder('u');
        if(!empty($search->username)){
            $query->andWhere('u.username LIKE :username')
                ->setParameter('username', "%{$search->username}%");
        }
        if(!empty($search->role)) {
            if($search->role === 'ROLE_MODERATOR' || $search->role === 'ROLE_USER' || $search->role === 'ROLE_ADMIN'){
                $query->andWhere('u.roles LIKE :role')
                    ->setParameter('role', "%{$search->role}%");
            }
        }
        if (!empty($search->isbanned)) {
            $query->andWhere('u.isBanned = :isBanned')
                ->setParameter('isBanned', $search->isbanned);
        }
        $query->orderBy('u.username', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * Results of number of user
     * @return int
     */
    public function countUsers(string $name): int
    {
        $queryBuilder = $this->createQueryBuilder('u');

        if (!empty($name)) {
            $queryBuilder->andWhere('u.username LIKE :username')
                ->setParameter('username', "%{$name}%");
        }

        return (int) $queryBuilder->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }


//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
