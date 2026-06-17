<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find(mixed $id, int|null $lockMode = null, int|null $lockVersion = null)
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
     * @param string $role
     * @param bool $hasRole
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return User[]
     */
    public function findByRole(string $role, bool $hasRole, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->getRole($role, $hasRole);

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $role
     * @param bool $hasRole
     *
     * @return int
     */
    public function countByRole(string $role, bool $hasRole): int
    {
        $qb = $this->getRole($role, $hasRole)
            ->select('COUNT(u.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $role
     * @param bool $hasRole
     *
     * @return QueryBuilder
     */
    private function getRole(string $role, bool $hasRole): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u');

        $roleLike = '%"' . $role . '"%';

        if ($hasRole) {
            $qb->andWhere('u.roles LIKE :role');
        } else {
            $qb->andWhere('u.roles NOT LIKE :role');
        }

        $qb->setParameter('role', $roleLike);

        return $qb;
    }

//    /**
//    * @param string $role
//    * @param bool $hasRole
//    *
//    * @return QueryBuilder
//    */
//    private function getRoleUsingJsonContains(string $role, bool $hasRole): QueryBuilder
//    {
//        $qb = $this->createQueryBuilder('u');
//
//        $operator = $hasRole ? '= 1' : '= 0';
//        $qb->andWhere("JSON_CONTAINS(u.roles, :role) $operator")
//        ->setParameter('role', json_encode($role));
//
//        return $qb;
//    }

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
