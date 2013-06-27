<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class RateValueAdmin extends Admin {

   protected function configureFormFields(FormMapper $form)
   {
     $form->add('valueFrom', null, array('label' => 'Нижнее значение'))
    ->add('valueTo', null, array('label' => 'Верхнее значение', ))
    ->add('valueEqual', null, array('label' => 'Точное значение', ));
   }

  protected function configureListFields(ListMapper $list)
  {
    $list->addIdentifier('id')
      ->add('valueFrom', null, array('label' => 'Нижнее значение'))
    ->add('valueTo', null, array('label' => 'Верхнее значение', ))
    ->add('valueEqual', null, array('label' => 'Точное значение', ));
  }
}
?>
