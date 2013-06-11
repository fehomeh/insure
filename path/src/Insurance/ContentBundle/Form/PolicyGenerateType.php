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
      ->add('insuranceCompany', null, array('mapped' => false))
      ->add('start_no', null, array('mapped' => false))
      ->add('end_no', null, array('mapped' => false));
      //->add('generate', 'save', array('label' => 'Сгенерировать'));
  }

  public function getName()
  {
    return 'policy_generator';
  }
}

?>
