<?php
namespace AppBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\GeoZone;
use AppBundle\Entity\GeoZoneName;
/**
 * Class LoadGeoZonesData
 * @package AppBundle\DataFixtures\ORM
 *
 * Loads GeoZones and GeoZonesName data from old structure GeoZones.csv
 *
 */
class LoadGeoZonesData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $filename =
            __DIR__ .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'Resources/data/Geo/GeoZones.csv';

        $em = $this->container->get('doctrine')->getManager('default');
        // Disable AI to set id as in csv
        $metadata = $em->getClassMetaData('AppBundle:GeoZone');
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $rows = array_map('str_getcsv', file($filename));
        $header = array_shift($rows) ;
        $csv = array();
        foreach ($rows as $row) {
            $csv[] = array_combine($header, $row);
        }

        $countryRepo = $em->getRepository('AppBundle:Country');
        $zoneRepo = $em->getRepository('AppBundle:GeoZone');

        foreach ($csv as $data) {

            $entity = new GeoZone();
            $country = $countryRepo->find($data['country']);

            $entity->setId(intval($data['id']))
                ->setCountry($country)
                ->setLevel($data['type'])
                ->setLocalId($data['num']);
            $em->persist($entity);

            // Parent
            if (!empty($data['zone_sup'])) {
                preg_match('/\d+/',$data['zone_sup'],$matches);
                if (count($matches) > 0) {
                    $parent_id = $matches[0];
                    $parent = $zoneRepo->find($parent_id);
                    $entity->setParent($parent);
                }
            }

            // MainCities
            if (!empty($data['main_cities'])) {
                $cities_id = explode(';', $data['main_cities']);
                $entity->setMainCities($cities_id);
            }

            // GeoZone Name(s)

            // Just local name in the csv (french)
            $name = new GeoZoneName();
            $name->setGeoZone($entity)
                ->setName($data['name'])
                ->setNormName($this->container->get('app.slug')->genericSlug($data['name']))
                ->setLanguage(strtolower($data['country']))
                ->setArticles(explode(',',$data['articles']));
            $em->persist($name);

            // Flush for every entity inserted, in order to find parents
            $em->flush();
            $em->clear(); // Detaches all objects from Doctrine!
        }

        // Now, another turn with adjacent zones
        foreach ($csv as $data) {
            if (!empty($data['zone_adj'])) {
                $entity = $zoneRepo->find($data['id']);
                $matches = array();
                preg_match_all('/\d+/',$data['zone_adj'],$matches);
                foreach($matches[0] as $zoneId) {
                    $entity->addAdjacentZone($zoneRepo->find($zoneId));
                }
                $em->flush();
                $em->clear();
            }
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