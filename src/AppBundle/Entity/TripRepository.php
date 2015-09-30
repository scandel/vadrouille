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
        // TODO : utiliser delta dans le query builder pour l'ordre. 

       /* $query = $this->getEntityManager()->createQuery(
                'SELECT t FROM AppBundle:Trip t, AppBundle:Stop d, AppBundle:Stop a
                WHERE d.cityName = :depName
                AND   d.trip = t
                AND   a.cityName = :arrName
                AND   d.trip = t
                AND   d.delta < a.delta
                ORDER BY t.id ASC'
            )
        ->setParameters(array(
            'depName' => $tripSearch->getDepCityName(),
            'arrName' => $tripSearch->getArrCityName(),
        )); */

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('t')
            ->from('AppBundle:Trip', 't');

        $parameters = array();

        if ($tripSearch->getDepCityName()) {
            $qb->from('AppBundle:Stop', 'd')
                ->andwhere('d.trip = t')
                ->andWhere('d.cityName = :depName');
            $parameters['depName'] = $tripSearch->getDepCityName();
        }

        if ($tripSearch->getArrCityName()) {
            $qb->from('AppBundle:Stop', 'a')
                ->andwhere('a.trip = t')
                ->andWhere('a.cityName = :arrName');
            $parameters['arrName'] = $tripSearch->getArrCityName();
        }

        $qb->setParameters($parameters);

        return $qb->getQuery()->getResult();
    }
}
