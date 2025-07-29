<?php

namespace App\Repository\Repositories;

use App\Dto\EventInput;
use App\Dto\SearchInput;
use App\Entity\Event;
use App\Repository\Interfaces\EventRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Event entity operations.
 * 
 * This repository provides a unified interface for both reading and writing Event entities.
 * It uses Doctrine ORM with QueryBuilder for type-safe database operations and supports
 * PostgreSQL-specific JSON operations for searching within event payloads.
 * 
 * @package App\Repository
 */
class EventRepository extends ServiceEntityRepository implements EventRepositoryInterface
{
    /**
     * Constructor.
     * 
     * @param ManagerRegistry $registry The Doctrine manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(SearchInput $searchInput): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('SUM(e.count) as total')
            ->where('DATE(e.createAt) = :date')
            ->andWhere('e.payload::text LIKE :keyword')
            ->setParameter('date', $searchInput->date->format('Y-m-d'))
            ->setParameter('keyword', '%' . $searchInput->keyword . '%');

        $result = $qb->getQuery()->getSingleScalarResult();
        
        return (int) ($result ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function countByType(SearchInput $searchInput): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.type, SUM(e.count) as count')
            ->where('DATE(e.createAt) = :date')
            ->andWhere('e.payload::text LIKE :keyword')
            ->groupBy('e.type')
            ->setParameter('date', $searchInput->date->format('Y-m-d'))
            ->setParameter('keyword', '%' . $searchInput->keyword . '%');

        $results = $qb->getQuery()->getResult();
        
        $countByType = [];
        foreach ($results as $result) {
            $countByType[$result['type']] = (int) $result['count'];
        }
        
        return $countByType;
    }

    /**
     * {@inheritdoc}
     */
    public function statsByTypePerHour(SearchInput $searchInput): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('EXTRACT(HOUR FROM e.createAt) as hour, e.type, SUM(e.count) as count')
            ->where('DATE(e.createAt) = :date')
            ->andWhere('e.payload::text LIKE :keyword')
            ->groupBy('e.type, EXTRACT(HOUR FROM e.createAt)')
            ->orderBy('hour', 'ASC')
            ->setParameter('date', $searchInput->date->format('Y-m-d'))
            ->setParameter('keyword', '%' . $searchInput->keyword . '%');

        $stats = $qb->getQuery()->getResult();

        $data = array_fill(0, 24, ['commit' => 0, 'pullRequest' => 0, 'comment' => 0]);

        foreach ($stats as $stat) {
            $data[(int) $stat['hour']][$stat['type']] = (int) $stat['count'];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getLatest(SearchInput $searchInput): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.type, e.repo')
            ->where('DATE(e.createAt) = :date')
            ->andWhere('e.payload::text LIKE :keyword')
            ->setParameter('date', $searchInput->date->format('Y-m-d'))
            ->setParameter('keyword', '%' . $searchInput->keyword . '%');

        $results = $qb->getQuery()->getResult();

        return array_map(static function($item) {
            return [
                'type' => $item['type'],
                'repo' => $item['repo']
            ];
        }, $results);
    }

    public function exist(int $id): bool
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.id = :id')
            ->setParameter('id', $id);

        $count = $qb->getQuery()->getSingleScalarResult();
        
        return (bool) $count;
    }

    /**
     * {@inheritdoc}
     */
    public function update(EventInput $authorInput, int $id): void
    {
        $event = $this->find($id);
        
        if (!$event) {
            throw new \InvalidArgumentException(sprintf('Event with id %d not found', $id));
        }

        $event->setComment($authorInput->comment);
        
        $this->_em->persist($event);
        $this->_em->flush();
    }
} 