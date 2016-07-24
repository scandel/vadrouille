<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use AppBundle\Entity\Trip;

/**
 * TripRepository
 */
class TripRepository extends EntityRepository
{
    /**
     * Search Trips based on TripSearch params (search form)
     * Paginated results
     *
     * @param TripSearch $tripSearch
     * @param int $page
     * @param int $perPage
     * @return array|null
     */
    public function search(TripSearch $tripSearch, $page=1, $perPage=10)
    {
        // ORM Query Builder
        $qb= $this->getEntityManager()->createQueryBuilder();

        $qb->select('t AS trip')
            ->from('AppBundle:Trip', 't');

        // Trip must be 'current', updated by a cron every 5 minutes
        // todo: index on current
        $qb->where('t.current = 1');

        $parameters = array();

        // Departure City is given
        if ($tripSearch->getDepCity()) {
            $qb->innerJoin('t.stops', 'd', 'WITH', 'd.city = :depCity');
            $parameters['depCity'] = $tripSearch->getDepCity();
            $qb->addSelect('d.delta AS dep');
        }

        // Arrival City is given
        if ($tripSearch->getArrCity()) {
            $qb->innerJoin('t.stops', 'a', 'WITH', 'a.city = :arrCity');
            $parameters['arrCity'] = $tripSearch->getArrCity();
            $qb->addSelect('a.delta AS arr');
        }

        // Order between departure and arrival
        if ($tripSearch->getDepCity() && $tripSearch->getArrCity()) {
            $qb->andWhere('d.delta < a.delta');
        }
        else if ($tripSearch->getArrCity()) {
            $qb->andWhere('a.delta > 0');
        }
        else if ($tripSearch->getDepCity()) {
            // Add a fake arrival to simulate it is not the last stop
            $qb->innerJoin('t.stops', 'a', 'WITH', 'd.delta < a.delta');
        }

        // Date is given
        if ($tripSearch->getDate()) {
            // Must match if this is the one date of the trip (single trip)
            // OR if the given date is in the days of a regular trip,
            // and the date is beetween begin_date and end_date
            // todo : speed up this part ? index ON days ?

            $qb->andwhere('(t.regular=0 AND t.depDate = :date)
                        OR (t.regular=1 AND t.days LIKE :day AND :date BETWEEN t.beginDate AND t.endDate)');
            $parameters['date'] = $tripSearch->getDate()->format('Y-m-d');
            $day  = ($tripSearch->getDate()->format('w') == '0') ? '7' : $tripSearch->getDate()->format('w');
            $parameters['day'] = "%$day%";

        }
       // Else: just do nothing ; the trip must be 'current', already asked.

        // Order
        $qb->orderBy('t.nextDateTime');

        $qb->setParameters($parameters);

        // with pagination
        $qb->setFirstResult(($page-1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb);
    }

    /**
     * Search trips of a given $user
     *
     * @param User $user
     * @param string $mode : "current", "old", or "all"
     * @return array|null
     */
    public function tripsOfUser(User $user, $mode="all", $page=1, $perPage=10)
    {
        // Trips are linked to Persons, so fetch Person by User...
        if (!$user || !$user->getPerson()) {
            return null;
        }
        $person = $user->getPerson();

        // ORM Query Builder
        $qb= $this->getEntityManager()->createQueryBuilder();

        $qb->select('t')
            ->from('AppBundle:Trip', 't')
            ->where('t.person = :person');

        $parameters = array(
            'person' => $person
        );

        // Mode : all, current, or old
        switch ($mode) {
            case "current":
                $qb->andWhere('t.current = 1');
                $qb->orderBy('t.nextDateTime');
                break;
            case "old":
                $qb->andWhere('t.current = 0');
                $qb->orderBy('t.nextDateTime', 'desc');
                break;
            default:
                $qb->orderBy('t.nextDateTime', 'desc');
        }

        $qb->setParameters($parameters);

        // with pagination
        $qb->setFirstResult(($page-1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb);
    }

}
