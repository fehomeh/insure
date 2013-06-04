<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

class RegionAdmin extends Admin{

  protected function configureFormFields(FormMapper $form)
  {
    $form->add('value', null, array('label' => 'Область'))
    ->add('city', 'sonata_type_collection', array(
      'label' => 'Города',
      'by_reference' => false,
    ),
      array('edit' => 'inline',
        'inline' => 'table',
        )
      );
  }

  protected function configureListFields(ListMapper $list)
  {
    $list->addIdentifier('id')
      ->addIdentifier('value', null, array('label' => 'Область'))
      ->add('city', null, array('label' => 'Город'));
  }

  protected function configureDatagridFilters(DatagridMapper $filter)
  {
    $filter->add('value', null, array('label' => 'Область'));
  }

  protected function configureShowField(ShowMapper $show)
  {
    $show->add('value', null, array('label' => 'Область'));
  }
}
?>
