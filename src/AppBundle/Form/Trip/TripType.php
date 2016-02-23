<?php

namespace AppBundle\Form\Trip;

use AppBundle\Form\Stop\StopType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Craue\FormFlowBundle\Event\FormFlowEvent;
use Craue\FormFlowBundle\Form\FormFlowEvents;
use AppBundle\Form\Person\PersonType;
use AppBundle\Form\Person\PersonGuestType;
use AppBundle\Form\Stop\StopHiddenType;

class TripType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Underlying $trip object...
        $trip = $builder->getData();

        // This form is splitted in two steps, so we use the 'fow_step' option
        // from form flow to cnstruct the fields of the form
        if (isset($options['flow_step'])) {
            switch ($options['flow_step']) {
                case 1:

                    //== Departure, arrival, and intermediate stops
                    // (Cities and addresses)
                    $builder->add('stops', 'collection', array(
                        'type' => 'app_stop_edit',
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false
                    ));

                    //== Date, time, regularity...
                    // One-shot or regular
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
                            $trip->setRegular($trip->getRegular() == false ? 0 : 1);
                            $event->setData($trip);
                        }
                    );

                    // If one-shot, departure date
                    $builder->add('depDate', 'datePicker', array(
                        'label' => 'Départ le : ',
                    ));

                    // If regular, departure days of the week
                    $builder->add('days', 'daysOfWeek', array(
                        'label' => 'Jours du trajet : ',
                    ));

                    // If regular, begin and end  date
                    $builder->add('beginDate', 'datePicker', array(
                        'label' => 'Début : ',
                    ));
                    $builder->add('endDate', 'datePicker', array(
                        'label' => 'Fin : ',
                    ));


                    // Departure time
                    $hours = range(0, 23);
                    $minutes = range(0, 55, 5);

                    $builder->add('depTime', 'time', array(
                        'label' => 'à : ',
                        'placeholder' => '---',
                        'input' => 'datetime',
                        'widget' => 'choice',
                        'hours' => $hours,
                        'minutes' => $minutes,
                    ));
                    break;
                case 2:
                    // 2nd part of the form
                    // Comment
                    $builder->add('comment', null, array('required' => false));

                    // Person
                    if (!$trip->getPerson() || $trip->getPerson()->isGuest()) {
                        // The Guest Person Form
                        $builder->add("person", PersonGuestType::class, array(
                            'label' => false
                        ));
                    }

                    // Put again stops to modify stop times
                    $builder->add('stops', 'collection', array(
                        'type' => StopHiddenType::class ,
                        'by_reference' => false
                    ));

                    break;
            }
        }
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
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
