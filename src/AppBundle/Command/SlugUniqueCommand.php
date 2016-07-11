<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\CityName;
use AppBundle\Entity\City;
use Cocur\Slugify\Slugify;

/**
 * Uniquify slugs (calculated before with slug command) for CitiesNames
 *
 * Class SlugUniqueCommand
 * @package AppBundle\Command
 */
class SlugUniqueCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('dbprepare:slug:unique')
            ->setDescription('Uniquify cities names slugs - fills slug column if not null')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        // Repository of City Names and manager
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $this->getContainer()->get('doctrine')->getRepository('AppBundle:CityName');

        // Batch number for updates
        $batch = 1000;


        $i = 0;

        $slugify = new Slugify();

        // Get all city names where slug is null
        while($cityName = $repo->findOneBySlug(null)) {
            $slugNotUnique = $cityName->getSlugNotUnique();

            // Search all cities with that slug
            $otherNames = $repo->findBySlugNotUnique($slugNotUnique);

            // Unique
            if (count($otherNames) == 1) {
                $cityName->setSlug($slugNotUnique);
            }
            // Not Unique
            else {
                // We need the city
                $city = $cityName->getCity();
                // We need to know the best note for this name
                $query = $em->createQuery(
                        'SELECT MAX(c.note) AS maxnote
                        FROM AppBundle:CityName cn
                        JOIN cn.city c
                        WHERE cn.slugNotUnique = :slugnotunique'
                    )->setParameter('slugnotunique', $slugNotUnique);
                $maxNote = $query->getOneOrNullResult()['maxnote'];

                if ($city->getNote() == $maxNote && $city->getNote() >= 50000) {
                    $newSlug = $slugNotUnique;
                }
                else {
                    // On rajoute le code pays et le code-postal
                    $slug = $slugNotUnique . ' ' . $city->getCountry()->getCode() . ' ' . $city->getPostCode();
                    $newSlug = $slugify->slugify($slug);
                }

                // Check if this slug already exists, alone or with a counter appended
                $query = $em->createQuery(
                    'SELECT COUNT(cn.id) AS nbslugs
                    FROM AppBundle:CityName cn
                    WHERE cn.slug LIKE :newSlug'
                )->setParameter('newSlug', $newSlug . '%');
                $nbSlugs = $query->getOneOrNullResult()['nbslugs'];

                if ($nbSlugs > 0) $newSlug .= '-'.($nbSlugs+1);

                $cityName->setSlug($newSlug);
            }

            $output->writeln(++$i . ' : ' . $slugNotUnique . " --> " . $cityName->getSlug() );

            unset($otherNames);
            $em->flush();

        }

    }
}