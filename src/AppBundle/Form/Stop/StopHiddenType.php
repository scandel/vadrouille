<?php

namespace AppBundle\Form\Stop;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * All fields hidden, for 2nd page of trip page
 *
 * Class StopHiddenType
 * @package AppBundle\Form\Stop
 */
class StopHiddenType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('city','hidden')
            ->add('delta', 'hidden')
            //->add('place', 'hidden')
            ->add('time', 'hidden')
            ->add('price', 'hidden')
            ->add('lat', 'hidden')
            ->add('lng', 'hidden')
        ;
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
        return 'app_hidden_stop_edit';
    }
}
