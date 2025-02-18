<?php

namespace App\Repository\Game\Glenmore;

use App\Entity\Game\Glenmore\TileGLM;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TileGLM>
 *
 * @method TileGLM|null find($id, $lockMode = null, $lockVersion = null)
 * @method TileGLM|null findOneBy(array $criteria, array $orderBy = null)
 * @method TileGLM[]    findAll()
 * @method TileGLM[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

/**
 * @codeCoverageIgnore
 */
class TileGLMRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TileGLM::class);
    }

}
