<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;

class CompanyRateAdmin extends Admin {

  /**
   *
   * @param \Sonata\AdminBundle\Form\FormMapper $form
   */
  protected function configureFormFields(FormMapper $form)
  {
    $form->add('rate', null, array('label' => 'Коэффициент', 'property' => 'value'));
      //->add('rateValue', null, array('label' => 'Значение', 'property' => 'value'));
  }

  protected function configureListFields(ListMapper $list)
  {
    $list->addIdentifier('id')
      ->addIdentifier('rate')
      ->addIdentifier('company')
      ->add('rateValue');
  }

  protected function configureShowFields(ShowMapper $filter)
  {
    $filter->add('rate', null, array('label' => 'Коэффициент'))
      ->add('company', null, array('label' => 'Страховая'))
      ->add('rateValue', null, array('label' => 'Значение'));
  }

  protected function configureDatagridFilters(DatagridMapper $filter)
  {
    $filter->add('rate', null, array('label' => 'Коэффициент'))
      ->add('company', null, array('label' => 'Компания'));
  }
  protected function configureSideMenu(\Knp\Menu\ItemInterface $menu, $action, AdminInterface $childAdmin = null)
  {
    $menu->addChild($action == 'edit' ? 'Просмотр коэф-та компании' : 'Редактирование коф-та компании', array(
      'uri' => $this->generateUrl($action == 'edit' ? 'show' : 'edit', array('id' => $this->getRequest()->get('id')))
    ));
  }
}