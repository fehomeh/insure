<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;

class RateValueAdmin extends Admin {

   protected function configureFormFields(FormMapper $form)
   {
     $form->add('value', null, array('label' => 'Значение коэффициента'));
   }

}
?>
