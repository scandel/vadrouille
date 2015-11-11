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
        $builder->add('city', new CityType());


        $builder->get('city')
            ->addModelTransformer(new CityTransformer($this->manager));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Stop'
        ));
    }


    public function getName()
    {
        return 'app_stop_edit';
    }
}
