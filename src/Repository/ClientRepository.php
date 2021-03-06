<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClientRepository extends EntityRepository
{
    public function findOneByUuid(string $id): ?Client
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (InvalidUuidStringException $exception) {
            throw new NotFoundHttpException(sprintf('Invalid Client ID: %s', $id));
        }
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function findAllPaged(
        $page = null,
        $limit = null,
        $sortField = null,
        $sortDirection = 'ASC',
        ParameterBag $params = null
    ) {
        $qb = $this->createQueryBuilder('c');

        // $this->joinRelatedTables($qb);

        if ($page && $limit) {
            $qb->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        if ($sortField) {
            if (!strstr($sortField, '.')) {
                $sortField = 'c.' . $sortField;
            }
            $qb->orderBy($sortField, $sortDirection);
        }

        $this->addCriteria($qb, $params);

        $results = $qb->getQuery()->execute();
        return $results;
    }

    public function findAllCount(ParameterBag $params)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('count(c)');

        $this->addCriteria($qb, $params);

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function addCriteria(QueryBuilder $qb, ParameterBag $params)
    {
        if ($params->has('keyword') && $params->get('keyword')) {
            $qb->andWhere('c.name.lastname LIKE :keyword OR c.name.firstname LIKE :keyword')
                ->setParameter('keyword', '%' . $params->get('keyword') . '%');
        }
    }
}
