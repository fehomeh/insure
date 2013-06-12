<?php

namespace Insurance\ContentBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PolicyGenerateType
 *
 * @author sly
 */
class PolicyGenerateType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('serie', null, array('mapped' => false, 'label' => 'Серия'))
      ->add('insuranceCompany', 'entity', array('mapped' => false, 'label' => 'Страховая компания',
          'class' => 'InsuranceContentBundle:InsuranceCompany',
          'property' => 'name'))
      ->add('start_no', null, array('mapped' => false, 'label' => 'Стартовый номер'))
      ->add('end_no', null, array('mapped' => false, 'label' => 'Конечный номер'));
      //->add('generate', 'save', array('label' => 'Сгенерировать')); starts in symfony 2.3
  }

  public function getName()
  {
    return 'policy_generator';
  }
}

?>
