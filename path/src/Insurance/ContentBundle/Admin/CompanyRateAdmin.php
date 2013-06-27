<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

class CompanyRateAdmin extends Admin {

  protected function configureFormFields(FormMapper $form)
  {
    $form->add('value', null, array('label' => 'Значение'))
    ->add('rate', 'sonata_type_model_list', array('label' => 'Коэф-т', ))
    ->add('company', null, array('label' => 'Страховая'))
    ->add('rateValue', 'sonata_type_model_list', array('label' => 'Значение коэф-та',));
  }

  protected function configureListFields(ListMapper $list)
  {
    $list->addIdentifier('id')
      ->add('rate', null, array('label' => 'Коэф-т'))
      ->add('value', null, array('label' => 'Значение'))
      ->add('rateValue', null, array('label' => 'Значение коэф-та'));
  }

  protected function configureDatagridFilters(DatagridMapper $filter)
  {
    $filter->add('rate', null, array('label' => 'Коэф-т'))
      ->add('value', null, array('label' => 'Значение'))
      ->add('rateValue', null, array('label' => 'Значение коэф-та'));
  }

  protected function configureShowField(ShowMapper $show)
  {
    $show->add('rate', null, array('label' => 'Коэф-т'))
      ->add('value', null, array('label' => 'Значение'))
      ->add('rateValue', null, array('label' => 'Значение коэф-та'));
  }

  /*protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
  {
    $menu->addChild($action == 'edit' ? 'Просмотр коэф-тов компании' : 'Редактирование коэф-тов компании',
      array('uri' => $this->generateUrl($action == 'edit' ? 'show' : 'edit',
        array('id' => $this->getRequest()->get('id'))))
    );
  }*/
}
