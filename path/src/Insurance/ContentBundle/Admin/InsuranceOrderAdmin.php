<?php
namespace Insurance\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class InsuranceOrderAdmin extends Admin {

  public function configureFormFields(FormMapper $form)
  {
    $form->add('active', null, array('label' => 'Активность', 'required'=>false))
      ->add('user', null, array('label' => 'Пользователь'))
      ->add('company', null, array('label' => 'Страховая'))
      ->add('city', null, array('label' => 'Город'))
      ->add('carModel', null, array('label' => 'Модель авто', 'property' => 'value'))
      ->add('activeFrom', null, array('label' => 'Активен с'))
      ->add('vinCode', null, array('label' => 'VIN код'))
      ->add('carNumber', null, array('label' => 'Номерной знак'))
      ->add('surname', null, array('label' => 'Фамилия'))
      ->add('firstname', null, array('label' => 'Имя'))
      ->add('middlename', null, array('label' => 'Отчество'))
      ->add('documentType', 'choice', array('choices' => array('P' => 'Паспорт', 'D' => 'Водительское удостоверение'),'label' => 'Тип документа'))
      ->add('documentSerie', null, array('label' => 'Серия документа'))
      ->add('documentNumber', null, array('label' => 'Номер документа'))
      ->add('documentAuthority', null, array('label' => 'Кем выдан документ'))
      ->add('documentDate', null, array('label' => 'Дата выдачи документа'))
      ->add('phone', null, array('label' => 'Телефон'))
      ->add('registerCity', null, array('label' => 'Город регистрации'))
      ->add('registerAddress', null, array('label' => 'Адрес регистрации'))
      ->add('registerBuilding', null, array('label' => 'Дом регистрации'))
      ->add('deliveryCity', null, array('label' => 'Город доставки'))
      ->add('deliveryAddress', null, array('label' => 'Адрес доставки'))
      ->add('deliveryBuilding', 'text', array('label' => 'Дом доставки'))
      ->add('payStatus', null, array('label' => 'Состояние оплаты'))
      ->add('payType', null, array('label' => 'Тип оплаты'));
  }

  public function configureListFields(ListMapper $list)
  {
    $list->addIdentifier('id')
      ->add('user', null, array('label' => 'Пользователь'))
      ->add('company', null, array('label' => 'Страховая'))
      ->add('city', null, array('label' => 'Город'))
      ->add('carModel', null, array('label' => 'Модель авто', 'property' => 'value'))
      ->add('surname', null, array('label' => 'Фамилия'))
      ->add('firstname', null, array('label' => 'Имя'))
      ->add('middlename', null, array('label' => 'Отчество'))
      ->add('phone', null, array('label' => 'Телефон'))
      ->add('payStatus', null, array('label' => 'Состояние оплаты'))
      ->add('payType', null, array('label' => 'Тип оплаты'));
  }

  public function configureDatagridFilters(DatagridMapper $filter)
  {
    $filter->add('id')
      ->add('user', null, array('label' => 'Пользователь'))
      ->add('company', null, array('label' => 'Страховая'))
      ->add('city', null, array('label' => 'Город'))
      ->add('carModel', null, array('label' => 'Модель авто',), null, array('property' => 'value'))
      ->add('surname', null, array('label' => 'Фамилия'))
      ->add('firstname', null, array('label' => 'Имя'))
      ->add('middlename', null, array('label' => 'Отчество'))
      ->add('phone', null, array('label' => 'Телефон'))
      ->add('payStatus', null, array('label' => 'Состояние оплаты'))
      ->add('payType', null, array('label' => 'Тип оплаты'));
  }
  public function configureShowFields(ShowMapper $filter)
  {
    $filter->add('id')
      ->add('user', null, array('label' => 'Пользователь'))
      ->add('company', null, array('label' => 'Страховая'))
      ->add('city', null, array('label' => 'Город'))
      ->add('carModel', null, array('label' => 'Модель авто', 'property' => 'value'))
      ->add('surname', null, array('label' => 'Фамилия'))
      ->add('firstname', null, array('label' => 'Имя'))
      ->add('middlename', null, array('label' => 'Отчество'))
      ->add('phone', null, array('label' => 'Телефон'))
      ->add('payStatus', null, array('label' => 'Состояние оплаты'))
      ->add('payType', null, array('label' => 'Тип оплаты'));
  }
}