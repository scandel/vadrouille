<?php
namespace AppBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\City;
use AppBundle\Entity\CityName;
use AppBundle\Entity\Country;

/**
 * Class LoadCitiesData
 * @package AppBundle\DataFixtures\ORM
 *
 * Loads City and CityName data from old structure Cities.csv
 *
 */
class LoadCitiesData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $fileMask =
            __DIR__ .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'Resources/data/Cities/Cities_*.csv';
        //    DIRECTORY_SEPARATOR . 'Resources/data/Cities/Cities100.csv';


        $em = $this->container->get('doctrine')->getManager('default');
        // Disable AI to set id as in csv
        $metadata = $em->getClassMetaData('AppBundle:City');
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        // Turn off Doctrine logging to avoid memory problems
        $conn = $em->getConnection();
        $conn->getConfiguration()->setSQLLogger(null);

        // Proceeds bulk inserts in batches
        $batchSize = 20;

        foreach (glob($fileMask) as $filename) {

            echo "Cities : Processing $filename ...\n";

            $rows = array_map('str_getcsv', file($filename));
            $header = array_shift($rows) ;
            $csv = array();
            foreach ($rows as $row) {
                $csv[] = array_combine($header, $row);
            }
            $count = 0;
            $total = count($csv);

            foreach ($csv as $data) {
                $count++;
                if ( $count % 1000 == 0 ) {
                    echo "$count/$total Cities procedeed...\n";
                }

                $entity = new City();
                $country = $em->getRepository('AppBundle:Country')->find($data['country_code']);

                $entity->setId(intval($data['id']))
                    ->setCountry($country)
                    ->setPostCode($data['postCode'])
                    ->setCenter($data['point'])
                    ->setNote($data['note']);
                $em->persist($entity);

                // Geozones
                if (!empty($data['zone1_id']) && $data['zone1_id']!='NULL') {
                    $zone1 = $em->getRepository('AppBundle:GeoZone')->find($data['zone1_id']);
                    $entity->setZone1($zone1);
                }
                if (!empty($data['zone2_id']) && $data['zone2_id']!='NULL') {
                    $zone2 = $em->getRepository('AppBundle:GeoZone')->find($data['zone2_id']);
                    $entity->setZone2($zone2);
                }

                // City Name(s)
                $names = explode(',', $data['names']);
                $slugs = explode(',', $data['slugs']);
                $languages = explode(',', $data['languages']);
                $mains = explode(',', $data['mains']);
                $citiesCount = min(count($names),count($slugs),count($languages),count($mains));

                for($i=0; $i<$citiesCount; $i++) {
                    $name = new CityName();
                    $name->setCity($entity)
                        ->setName($names[$i])
                        ->setSlug($slugs[$i])
                        ->setMain($mains[$i])
                        ->setLanguage($languages[$i]);
                    $em->persist($name);
                }

                 if (($count % $batchSize) === 0) {
                    $em->flush();
                    $em->clear(); // Detaches all objects from Doctrine!
                }
            }
            $em->flush(); //Persist objects that did not make up an entire batch
            $em->clear();
         }
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 7;
    }

}