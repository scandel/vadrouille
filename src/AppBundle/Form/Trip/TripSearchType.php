<?php

namespace AppBundle\Form\Trip;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\City\CityType;
use AppBundle\Form\DataTransformer\CityTransformer;
use Doctrine\ORM\EntityManager;

class TripSearchType extends AbstractType
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
        $builder->add('depCity', new CityType(), array(
            'required' => false,
            'label' => 'Départ : ',
            'invalid_message' => 'La ville de départ n\'est pas reconnue, merci d\'en choisir une parmi les propositions de l\'autocomplétion.' ,
        ));
        $builder->get('depCity')
            ->addModelTransformer(new CityTransformer($this->manager));

        $builder->add('arrCity', new CityType(), array(
            'required' => false,
            'label' => 'Arrivée : ',
            'invalid_message' => 'La ville d\'arrivée n\'est pas reconnue, merci d\'en choisir une parmi les propositions de l\'autocomplétion.' ,
        ));
        $builder->get('arrCity')
            ->addModelTransformer(new CityTransformer($this->manager));

        $builder->add('search', 'submit', array(
            'attr' => array('class' => 'btn btn-primary'),
            'label' => 'Chercher !'
        ));

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            // avoid to pass the csrf token in the url (but it's not protected anymore)
            'csrf_protection' => false,
            'data_class' => 'AppBundle\Entity\TripSearch'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_trip_search';
    }
}
