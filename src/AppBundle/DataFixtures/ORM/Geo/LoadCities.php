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
        //    DIRECTORY_SEPARATOR . 'Resources/data/Geo/Cities_*.csv';
            DIRECTORY_SEPARATOR . 'Resources/data/Geo/Cities_FR.csv';


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
            $header = (isset($header)) ? $header : array_shift($rows) ;
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
                    ->setPostCode($data['postal_code'])
                    ->setLat($data['lat'])
                    ->setLng($data['lng'])
                    ->setNote($data['note']);
                $em->persist($entity);

                // City Name(s)

                // Local name (french or not)
                $name = new CityName();
                $name->setCity($entity)
                    ->setName($data['name'])
                    ->setNormName($data['norm_name'])
                    ->setLanguage(strtolower($data['country_code']));
                $em->persist($name);

                // French name also defined
                if (!empty($data['french_name'])) {
                    $frenchName = new CityName();
                    $frenchName->setCity($entity)
                        ->setName($data['french_name'])
                        ->setNormName($data['norm_french_name'])
                        ->setLanguage('fr');
                    $em->persist($frenchName);
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
        return 6;
    }

}