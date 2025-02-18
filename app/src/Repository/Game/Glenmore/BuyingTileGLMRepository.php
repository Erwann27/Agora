<?php

namespace App\Repository\Game\Glenmore;

use App\Entity\Game\Glenmore\BuyingTileGLM;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BuyingTileGLM>
 *
 * @method BuyingTileGLM|null find($id, $lockMode = null, $lockVersion = null)
 * @method BuyingTileGLM|null findOneBy(array $criteria, array $orderBy = null)
 * @method BuyingTileGLM[]    findAll()
 * @method BuyingTileGLM[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

/**
 * @codeCoverageIgnore
 */
class BuyingTileGLMRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BuyingTileGLM::class);
    }

}
