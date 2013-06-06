<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

class RateAdmin extends Admin {

  protected function configureFormFields(FormMapper $form)
  {
    $form->add('value', null, array('label' => 'Значение'))
      ->add('code', null, array('label' => 'Символьный код'));
  }

  protected function configureListFields(ListMapper $list)
  {
    $list->addIdentifier('id')
      ->addIdentifier('value', null, array('label' => 'Значение'))
      ->addIdentifier('code', null, array('label' => 'Символьный код'));
  }

  protected function configureDatagridFilters(DatagridMapper $filter)
  {
    $filter->add('value', null, array('label' => 'Значение'))
      ->add('code', null, array('label' => 'Символьный код'));
  }
}
?>
