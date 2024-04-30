<?php

namespace App\Repository;

use App\Entity\CustomerNoteAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerNoteAttachment>
 *
 * @method CustomerNoteAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerNoteAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerNoteAttachment[]    findAll()
 * @method CustomerNoteAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerNoteAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerNoteAttachment::class);
    }
}
