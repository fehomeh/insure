<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

class PolicyAdmin extends Admin {

   protected function configureFormFields(FormMapper $form)
   {
     $form->add('serie', null, array('label' => 'Серия полиса'))
       ->add('value', null, array('label' => 'Номер полиса'))
       ->add('company', null, array('label' => 'Страховая компания'))
       ->add('status', null, array('label' => 'Статус'));
   }

   protected function configureListFields(ListMapper $list)
   {
     $list->addIdentifier('id')
       ->addIdentifier('serie', null, array('label' => 'Серия полиса'))
       ->addIdentifier('value', null, array('label' => 'Номер полиса'))
       ->addIdentifier('company', null, array('label' => 'Страховая компания'))
       ->addIdentifier('status', null, array('label' => 'Статус'));
   }

   protected function configureDatagridFilters(DatagridMapper $filter)
   {
     $filter->add('serie', null, array('label' => 'Серия полиса'))
       ->add('value', null, array('label' => 'Номер полиса'))
       ->add('company', null, array('label' => 'Страховая компания'));
   }
}
?>
