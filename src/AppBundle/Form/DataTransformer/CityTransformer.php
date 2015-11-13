<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\City;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CityTransformer implements DataTransformerInterface {

    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    /**
    * Transforms an object (city) to an array with name and id.
    *
    * @param  City|null $city
    * @return string
    */
    public function transform($city) {
        if (!($city instanceof City)) {
            return null;
        }

        $cityArray = array(
            'id' => $city->getId(),
            'name' => $city->getMainName()->getName(),
        );
        return $cityArray;
    }

    /**
    * Transforms an array with id and name  to an object (city).
     * todo: if id is null but not name, check if exists, and if not, send an appropriate message
    *
    * @param  array $cityArray
    * @return City|null
    * @throws TransformationFailedException if object (city) is not found.
    */
    public function reverseTransform($cityArray) {
        // no city Id ? It's optional, so that's ok
        if (!$cityArray['id']) {
            return null;
        }

        $city = $this->manager->getRepository('AppBundle:City')->find($cityArray['id']);

        if (null === $city) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
            'A city with id "%s" does not exist!',
            $cityArray['id']
            ));
        }

        return $city;
    }
}
