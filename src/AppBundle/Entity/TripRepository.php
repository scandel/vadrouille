<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * TripRepository
 */
class TripRepository extends EntityRepository
{
    public function search(TripSearch $tripSearch)
    {
        // DBAL query builder
        $qb= $this->getEntityManager()->getConnection()->createQueryBuilder();

        $qb->select('t.id')
            ->from('Trips', 't');

        $parameters = array();

        // Departure City is given
        if ($tripSearch->getDepCity()) {
            $qb->innerJoin('t', 'Stops', 'd', 't.id = d.trip_id')
                ->andWhere('d.city_id = :depCityId');
            $parameters['depCityId'] = $tripSearch->getDepCity()->getId();
        }

        // Arrival City is given
        if ($tripSearch->getArrCity()) {
            $qb->innerJoin('t', 'Stops', 'a', 't.id = a.trip_id')
                ->andWhere('a.city_id = :arrCityId');
            $parameters['arrCityId'] = $tripSearch->getArrCity()->getId();
        }

        // Order between departure and arrival
        if ($tripSearch->getDepCity() && $tripSearch->getArrCity()) {
            $qb->andWhere('d.delta < a.delta');
        }
        else if ($tripSearch->getArrCity()) {
            $qb->andWhere('a.delta > 1');
        }
        else if ($tripSearch->getDepCity()) {
            // Add a fake arrival to simulate it is not the last stop
            $qb->innerJoin('t', 'Stops', 'a', 't.id = a.trip_id')
                ->andWhere('d.delta < a.delta');
        }

        // Distinct
        $qb->groupBy('t.id');

        $qb->setParameters($parameters);

        $ids =  $qb->execute()->fetchAll();
        if ($ids) {
            $trips = array();
            foreach ($ids as $id) {
                $trips[] = $this->find($id);
            }
            return $trips;
        }
        else {
            return null;
        }
    }
}
