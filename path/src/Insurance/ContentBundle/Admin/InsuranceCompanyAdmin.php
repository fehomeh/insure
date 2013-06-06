<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
//use Sonata\AdminBundle\Admin\AdminInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;

class InsuranceCompanyAdmin extends Admin {

  protected function configureShowFields(ShowMapper $filter)
  {
    $filter->add('name', null, array('label' => 'Название'));
    $filter->add('description', null, array('label' => 'Описание'));
    $filter->add('logo', null, array('label' => 'Логотип'));
    $filter->add('default_rate', null, array('label' => 'Коэффициент по умолчанию'));
  }

  protected function configureFormFields(FormMapper $form)
  {
    $form->add('name', null, array('label' => 'Название'));
      //->add('description', null, array('label' => 'Описание'))
      //->add('logo', null, array('label' => 'Логотип'))
      //->add('defaultRate', null, array('label' => 'Коэффициент по умолчанию'));
      /*->add('companyRate', 'sonata_type_collection', array(
        'label' => 'Коэффициенты',
        'by_reference' => false,
      ), array(
        'edit' => 'inline',
        //'sortable' => 'pos',
        'inline' => 'table',
        'targetEntity' => 'Insurance\ContentBundle\Entity\CompanyRate',
      ))*/
      //->add('policy', null, array('label' => 'Номер полиса',));
  }

  protected function configureListFields(ListMapper $list)
  {
    $list->addIdentifier('id')
      ->addIdentifier('name', null, array('label' => 'Название компании'))
      ->add('default_rate', null, array('label' => 'Коэффициент по умолчанию'));
  }

  protected function configureDatagridFilters(DatagridMapper $filter)
  {
    $filter->add('name', null, array('label' => 'Название компании'));
  }

  /*protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
  {
    $menu->addChild($action == 'edit' ? 'Просмотр компании' : 'Редактирование компании',
      array('uri' => $this->generateUrl($action == 'edit' ? 'show' : 'edit',
        array('id' => $this->getRequest()->get('id'))))
    );
  }*/
}

?>
