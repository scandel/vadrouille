<?php

namespace AppBundle\Form\Trip;

use AppBundle\Form\Stop\StopType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TripType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('stops', 'collection', array(
                'type' => 'app_stop_edit',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ));

        $builder->add('regular', 'choice', array(
           'choices' => array(
               0 => 'Une seule fois',
               1 => 'Régulier',
           ),
            'expanded' => true,
            'multiple' => false,
            'label' => 'Fréquence : ',
        ));
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $trip = $event->getData();
                $trip->setRegular( $trip->getRegular() === false ? 0 : 1 );
                $event->setData($trip);
            }
        );

        $builder->add('depDate', 'datePicker', array(
            'label' => 'Départ le : ',
        ));

        $hours = range(0,23);
        $minutes = range(0,55,5);

        $builder->add('depTime', 'time', array(
            'label' => 'à : ',
            'placeholder' => '---',
            'input'  => 'datetime',
            'widget' => 'choice',
            'hours' => $hours,
            'minutes' => $minutes,
        ));


        $builder->add('comment', null, array('required' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Trip'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_trip_edit';
    }
}
