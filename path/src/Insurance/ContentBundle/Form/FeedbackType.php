<?php

namespace Insurance\ContentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('timeToCall')
            ->add('phoneNumber')
            ->add('email')
            ->add('question')
            ->add('connectionType')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Insurance\ContentBundle\Entity\Feedback'
        ));
    }

    public function getName()
    {
        return 'insurance_contentbundle_feedbacktype';
    }
}
