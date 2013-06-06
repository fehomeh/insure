<?php

namespace Insurance\ContentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value')
            ->add('region', 'entity', array(
              'class' => 'InsuranceContentBundle:Region',
              'property' => 'value',
              'label' => 'Регион',
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Insurance\ContentBundle\Entity\City'
        ));
    }

    public function getName()
    {
        return 'insurance_contentbundle_citytype';
    }
}
