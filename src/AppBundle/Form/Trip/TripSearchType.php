<?php

namespace AppBundle\Form\Trip;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TripSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('depCityName', null, array(
            'required' => false
        ));

        $builder->add('arrCityName', null, array(
            'required' => false
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
