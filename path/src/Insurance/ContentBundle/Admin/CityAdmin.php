<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

class CityAdmin extends Admin{

  protected function configureFormFields(FormMapper $form)
  {
    $form->add('region', null, array('label' => 'Область'))
      ->add('value', null, array('label' => 'Город'));
  }

  protected function configureListFields(ListMapper $list)
  {
    $list->addIdentifier('id')
      ->addIdentifier('value', null, array('label' => 'Город'))
      ->add('region', null, array('label' => 'Область'));
  }

  protected function configureDatagridFilters(DatagridMapper $filter)
  {
    $filter->add('value', null, array('label' => 'Город'))
      ->add('region', null, array('label' => 'Область'));
  }
  
  protected function configureShowField(ShowMapper $show)
  {
    $show->add('region', null, array('label' => 'Область'))
      ->add('value', null, array('label' => 'Город'));
  }
}
?>
