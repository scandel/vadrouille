<?php

namespace AppBundle\Form\Stop;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\City\CityType;
use AppBundle\Form\DataTransformer\CityTransformer;
use Doctrine\ORM\EntityManager;

class StopType extends AbstractType
{

    private $manager;

    public function __construct(EntityManager $manager) {
        $this->manager = $manager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('city', new CityType(), array(
            'label' => 'Ville :',
            'invalid_message' => 'Cette ville n\'est pas reconnue, merci d\'en choisir une parmi les propositions de l\'autocomplétion.' ,
        ));
        $builder->get('city')
            ->addModelTransformer(new CityTransformer($this->manager));

        $builder->add('place', 'text', array(
            'label' => 'Lieu ou adresse :',
            'required' => false,
            'attr' => array('placeholder' => 'N\'importe où'),
        ));

        $builder->add('delta', 'hidden');
        $builder->add('time', 'hidden');
        $builder->add('price', 'hidden');
        $builder->add('lat', 'hidden');
        $builder->add('lng', 'hidden');

        // For geolocalistaion
        $builder->add('city_details', 'hidden', array(
            'mapped' => false,
        ));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Stop',
        ));
    }


    public function getName()
    {
        return 'app_stop_edit';
    }
}
