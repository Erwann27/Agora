<?php

namespace App\Repository\Game\Myrmes;

use App\Entity\Game\Myrmes\ResourceMYR;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceMYR>
 *
 * @method ResourceMYR|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceMYR|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceMYR[]    findAll()
 * @method ResourceMYR[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

/**
 * @codeCoverageIgnore
 */
class ResourceMYRRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResourceMYR::class);
    }

}
