<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class RateValueAdmin extends Admin {

   protected function configureFormFields(FormMapper $form)
   {
     $form->add('value', null, array('label' => 'Значение коэффициента'));
   }

  protected function configureListFields(ListMapper $list)
  {
    $list->addIdentifier('id')
      ->addIdentifier('value', null, array('label' => 'Значение коэффициента'));
  }
}
?>
