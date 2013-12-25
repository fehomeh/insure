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
            ->add('name', null, array('label' => 'Ваше имя:',
                'attr' => array(
                    'placeholder' => 'Введите Ваше имя',
                ),
            ))
           // ->add('timeToCall', 'text', array('attr' =>
            //    array(
            //      'class' => 'callback-time',
             //     'placeholder' => 'Введите желаемое время звонка',
             //     ),
             //   'label' => 'Желаемое время звонка:'
            //))
            ->add('phoneNumber', null, array('label' => 'Номер телефона:',
                'attr' => array(
                    'placeholder' => '(ХХХ)ХХХ-ХХ-ХХ',
                ),
            ))
            ->add('email', null, array('label' => 'Ваш e-mail:',
                'attr' => array(
                    'placeholder' => 'Введите e-mail адрес',
                ),
            ))
            ->add('question', null, array('label' => 'Вопрос:',
                'attr' => array(
                    'placeholder' => '',
                ),
            ))
            ->add('connectionType', null, array('label' => 'Тип связи',
                'attr' => array(
                    'placeholder' => '',
                ),
            ))
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
