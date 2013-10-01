<?php

namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\Region;
use Insurance\ContentBundle\Entity\City;
use Insurance\ContentBundle\Form\FeedbackType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Insurance\ContentBundle\Entity\Feedback;
use Insurance\ContentBundle\Entity\InsuranceOrder;
use Application\Sonata\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    const DEFAULT_COMPANY_ID = 2;
    public function indexAction($name = 'Stranger')
    {
      //$regions = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll();
        //Check for stored calculation and if any redirect to calculator
//        if ($this->getStoredCalculation($this->getRequest())) {
//            return $this->redirect($this->generateUrl('step1'));
//        }
        $carBrands = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findAll();
        $feedbackForm = $this->createForm(new FeedbackType());
        $session = $this->getRequest()->getSession();
        if ($carBrand = $session->get('carBrand')) {
            $carModels = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')->findByBrand($carBrand);
        } else $carModels = array();
        //$region->setValue('Киевская');
        //$em->persist($region);
        //$em->flush();
        //$productRep = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region');
        //$reg = $productRep->find('1');
        if (!$session->get('timerEnd')) {
            $session->set('timerEnd', date('d M Y H:i:s', time() + $this->container->getParameter('timer')));
        }
        $savedSum = $this->container->getParameter('economy.sum') +
            ($this->container->getParameter('economy.step') * floor(((time() - strtotime($this->container->getParameter('economy.startdate')))/86400)));
        if (strlen($savedSum) > 6) {
            $tmpTime = (int)(((1000000 - $this->container->getParameter('economy.sum'))/$this->container->getParameter('economy.step')) * 86400);
            echo (floor(((time() - $tmpTime - strtotime($this->container->getParameter('economy.startdate')))/86400)));
            $savedSum = $this->container->getParameter('economy.sum') +
            ($this->container->getParameter('economy.step') * floor(((time() - $tmpTime - strtotime($this->container->getParameter('economy.startdate')))/86400)));
        }
        return $this->render('InsuranceContentBundle:Default:index.html.twig', array(
          //'regions' => $regions,
          'brands' => $carBrands,
          'carBrand' => $carBrand,
          'models' => $carModels,
          'carModel' => $session->get('carModel'),
          'feedback_form' => $feedbackForm->createView(),
          'callback_form' => $feedbackForm->createView(),
          'timerEnd' => $session->get('timerEnd'),
          'savedSum' => $savedSum,
          ));
    }

    /**
     * AJAX action to get cities list after select some region on page
     * @return JSON encoded list of cities
     */
    public function getCitiesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $request = $this->getRequest();
            $region_id = $request->get('region_id');
            if ($cities = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findBy(
                array('region' => $region_id), array('value' => 'ASC'))) {
            foreach ($cities as $city)
                $city_list[$city->getId()] = $city->getValue();
            } else $city_list = array();
            $response = new Response(json_encode($city_list));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    public function getCarModelsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $request = $this->getRequest();
            $brand_id = $request->get('brand_id');
            if ($models = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')->findBy(
                array('brand' => $brand_id), array('value' => 'ASC'))) {
            foreach ($models as $model)
                $models_list[$model->getId()] = $model->getValue();
            } else $models_list = array();
            $response = new Response(json_encode($models_list));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    /**
     * Action to save car model and brand into session and redirect user to calculator
     */
    public function goToStepOneAction()
    {
        $session = $this->getRequest()->getSession();
        $request = $this->getRequest();
        $securityContext = $this->container->get('security.context');
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
    // authenticated REMEMBERED, FULLY will imply REMEMBERED (NON anonymous)
        }
        if ($request->request->get('carBrand') > 0 && $request->request->get('carModel')) {
            $session->set('carBrand', $request->request->get('carBrand'));
            $session->set('carModel', $request->request->get('carModel'));
            return $this->redirect($this->generateUrl('step1'));
        } else {
            return $this->redirect($this->generateUrl('insurance_content_homepage'));
        }
    }

    /**
     * Action renders calculator template and processes it data: saves to session
     * check for errors, calculates prices and redirects user to next page - formalization
     */
    public function calculatorAction(Request $request)
    {
        $session = $this->getRequest()->getSession();
        $this->getStoredCalculation($this->getRequest());
        $calculator = $this->get('insurance.service.calculator');
        $feedbackForm = $this->createForm(new FeedbackType());
        if ($carBrand = $session->get('carBrand')) {
          $models = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')
              ->findByBrand($carBrand);
        } else $models = null;
        if ($registerRegion = $session->get('registerRegion')) {
            $cities = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findByRegion($registerRegion);
        } else $cities = null;
        if ($request->getMethod() == 'POST') {
            $error = array();
            $calculator = $this->get('insurance.service.calculator');
            $carBrand = $request->request->get('carBrand');
            $carModel = $request->request->get('hCarModel');
            $carAge = $request->request->get('carAge');
            //$experience = $request->request->get('hExperience');
            $experience = 1;
            $displacement = $request->request->get('hDisplacement');
            $registerRegion = $request->request->get('registerRegion');
            $registerCity = $request->request->get('registerCity');
            $insuranceTerm = $request->request->get('insuranceTerm');
            $passengersCount = $request->request->get('passengersCount');
            $dgoSum = $request->request->get('hDGOSum');
            $nsSum = $request->request->get('hNSSum');
            if ($carBrand > 0) $session->set('carBrand', $carBrand);
                else $error['carBrand'] = 'Не заполнено поле';
            if ($carModel > 0) $session->set('carModel', $carModel);
                else $error['hCarModel'] = 'Не заполнено поле';
            if ($displacement > 0) $session->set('displacement', $displacement);
                else $error['hDisplacement'] = 'Не заполнено поле';
            if ($carAge > 0) $session->set('carAge', $carAge);
                else $error['carAge'] = 'Не заполнено поле';
            if ($registerRegion > 0) $session->set('registerRegion', $registerRegion);
                else $error['registerRegion'] = 'Не заполнено поле';
            if ($registerCity > 0) $session->set('registerCity', $registerCity);
                else $error['registerCity'] = 'Не заполнено поле';
            if ($insuranceTerm > 0) $session->set('insuranceTerm', $insuranceTerm);
                else $error['insuranceTerm'] = 'Не заполнено поле';
            if ($request->request->get('cbDGO') == 'yes' && $dgoSum > 0)
            {
                $session->set('dgoSum', $dgoSum);
                if ($request->request->get('taxiUse') == 1)
                    $session->set('taxiUse', 1);
                $session->set('priceDGO', $calculator->calculateDgo(array(
                    'sum' => $dgoSum,
                    'displacement' => $displacement,
                    'experience' => $experience,
                    'term' => $insuranceTerm,
                    'taxi' => $request->request->get('taxiUse')? 1 : -1,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                ));
            } else if ($request->request->get('cbDGO') == 'yes')
                $error['dgoSum'] = 'Не заполнено поле';
            if ($request->request->get('cbNS') == 'yes' && $nsSum > 0) {
                $session->set('nsSum', $nsSum);
                $session->set('passengersCount', $passengersCount);
                $session->set('priceNs', $calculator->calculateNs(array(
                    'sum' => $nsSum,
                    'passenger_count' => $passengersCount,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                ));
                if ($passengersCount > 0) $session->set('passengersCount', $passengersCount);
                else $error['passengersCount'] = 'Не заполнено поле';
            } else if ($request->request->get('cbNS') == 'yes')
            {
                if ($passengersCount >= 0)
                    $session->set('passengersCount', $passengersCount);
                else
                    $error['passengersCount'] = 'Не заполнено поле';
                $error['nsSum'] = 'Не заполнено поле';
            }
            $calculator->setCompany(static::DEFAULT_COMPANY_ID)
                ->setRateType('base');
            $discountObj = $calculator->getRate(date('Y') - $carAge, 'year');
            if ($discountObj)
                $discount = $discountObj->getValue();
            else
                $discount = 1;
            $session->set('discount', $discount);
            $session->set('price', $calculator->calculateCommon(array(
                'region' => $registerCity,
                'displacement' => $displacement,
                'experience' => $experience,
                'term' => $insuranceTerm,
                'year' => $carAge,
                'company' => static::DEFAULT_COMPANY_ID,
            )) * $discount);
            if (count($error) == 0)
                return $this->redirect($this->generateUrl('step2'));
            else {
                $response = new Response(json_encode(array('error' => $error, 'message' => 'error')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }
        return $this->render('InsuranceContentBundle:Default:step_one.html.twig',array(
            'carBrand' => $session->get('carBrand'),
            'carModel' => $session->get('carModel'),
            'brands' => $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findAll(),
            'models' => $models,
            'regions' => $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll(),
            'cities' => $cities,
            'region' => (isset($registerRegion)?$registerRegion:null),
            'city' => (isset($registerCity)?$registerCity:null),
            'carAgeFrom' => $this->container->getParameter('car.age.from'),
            'carAgeTo' => (int)date('Y'),
            'displacement' => $this->container->getParameter('displacement'),
            'insureTerm' => $this->container->getParameter('insure.period'),
            'dgoSum' => $this->container->getParameter('dgo.sum'),
            'nsSum' => $this->container->getParameter('ns.sum'),
            'passengers' => $this->container->getParameter('passengers'),
            'error' => isset($error)?$error:null,
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }

    /**
     * Output registration and policy formalization form also process data from this form
     *
     */
    public function formalizationAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $session = $this->getRequest()->getSession();
            $feedbackForm = $this->createForm(new FeedbackType());
            if ($request->getMethod() == 'POST') {
                //init error container
                $error = array();
                $activeFrom = $request->request->get('activeFrom');
                $vinCode = $request->request->get('vinCode');
                $carNumber = $request->request->get('carNumber');
                $surname = $request->request->get('surname');
                $firstname = $request->request->get('firstname');
                $middlename = $request->request->get('middlename');
                $documentType = $request->request->get('documentType');
                $documentSerie = $request->request->get('documentSerie');
                $documentNumber = $request->request->get('documentNumber');
                $documentAuthority = $request->request->get('documentAuthority');
                $documentDate = $request->request->get('documentDate');
                $phone = $request->request->get('phone');
                $region = $request->request->get('region');
                $city = $request->request->get('city');
                $registerAddress = $request->request->get('registerAddress');
                $registerBuilding = $request->request->get('registerBuilding');            //Lets make custom validation for form variables
                $processPersonalData = $request->request->get('processPersonalData');
                if (strlen($activeFrom) === 0) {
                    $error['activeFrom'] = 'Не заполнено поле';
                } elseif (!preg_match('#[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}#', $activeFrom)) {
                    $error['activeFrom'] = 'Неправильный формат даты';
                } else {
                    $session->set('activeFrom', $activeFrom);
                }

                if (strlen($vinCode) === 0) {
                    $error['vinCode'] = 'Не заполнено поле';
                } else {
                    $session->set('vinCode', $vinCode);
                }

                if (strlen($carNumber) === 0) {
                    $error['carNumber'] = 'Не заполнено поле';
                } else {
                    $session->set('carNumber', $carNumber);
                }

                if (strlen($surname) === 0) {
                    $error['surname'] = 'Не заполнено поле';
                } else {
                    $session->set('surname', $surname);
                }

                if (strlen($firstname) === 0) {
                    $error['firstname'] = 'Не заполнено поле';
                } else {
                    $session->set('firstname', $firstname);
                }

                if (strlen($middlename) === 0) {
                    $error['middlename'] = 'Не заполнено поле';
                } else {
                    $session->set('middlename', $middlename);
                }

                if (strlen($documentType) === 0) {
                    $error['documentType'] = 'Не заполнено поле';
                } elseif ($documentType == 'P' && $documentType == 'D') {
                    $error['documentType'] = 'Неверный тип документа';
                } else {
                    $session->set('documentType', $documentType);
                }

                if (strlen($documentSerie) === 0) {
                    $error['documentSerie'] = 'Не заполнено поле';
                } else {
                    $session->set('documentSerie', $documentSerie);
                }

                if (strlen($documentNumber) === 0) {
                    $error['documentNumber'] = 'Не заполнено поле';
                } else {
                    $session->set('documentNumber', $documentNumber);
                }

                if (strlen($documentAuthority) === 0) {
                    $error['documentAuthority'] = 'Не заполнено поле';
                } else {
                    $session->set('documentAuthority', $documentAuthority);
                }

                if (strlen($documentDate) === 0) {
                    $error['documentDate'] = 'Не заполнено поле';
                } elseif (!preg_match('#[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}#', $documentDate)) {
                    $error['documentDate'] = 'Неправильный формат даты';
                } else {
                    $session->set('documentDate', $documentDate);
                }

                if ($region > 0) {
                    $session->set('phone', $phone);
                } else {
                    $error['phone'] = 'Не заполнено поле';
                }

                if ($region > 0) {
                    $session->set('region', $region);
                } else {
                    $error['region'] = 'Не заполнено поле';
                }

                if ($city > 0) {
                    $session->set('city', $city);
                } else {
                    $error['city'] = 'Не заполнено поле';
                }

                if (strlen($registerAddress) === 0) {
                    $error['registerAddress'] = 'Не заполнено поле';
                } else {
                    $session->set('registerAddress', $registerAddress);
                }

                if (strlen($registerBuilding) === 0) {
                    $error['registerBuilding'] = 'Не заполнено поле';
                } else {
                    $session->set('registerBuilding', $registerBuilding);
                }
                if ($processPersonalData == 'Y')
                if (count($error) == 0) return $this->redirect($this->generateUrl('step3'));
                else var_dump($error);
            }
            $carBrand = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findOneById($session->get('carBrand'));
            $carModel = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')->findOneById($session->get('carModel'));
            $usr = $this->get('security.context')->getToken()->getUser();
            $registerPersons = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findBy(
                array(
                    'user' => $usr->getId(),
                ),
                array(
                    'id' => 'desc',
                ));
            if (is_null($carBrand) || is_null($carModel)) {
                return $this->redirect($this->generateUrl('step1'));
            }
            return $this->render('InsuranceContentBundle:Default:step_two.html.twig', array(
                'carModel' => $carModel->getValue(),
                'carBrand' => $carBrand->getValue(),
                'regions' => $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll(),
                'cities' => (isset($region))?$this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findByRegion($region):null,
                'errors' => isset($error) ? $error : null,
                'registerPersons' => $registerPersons,
                'feedback_form' => $feedbackForm->createView(),
                'callback_form' => $feedbackForm->createView(),
                )
            );
        } else return $this->redirect($this->generateUrl('step1'));
    }


    public function deliveryAction(Request $request)
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        $session = $request->getSession();
        $region = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findOneById($session->get('region'));
        $regionId = $session->get('region');
        $cityId = $session->get('city');
        //If user visit this page right after register page than fill delivery address from register address stored in session
        $deliveryAddress = $session->get('registerAddress');
        $deliveryBuilding = $session->get('registerBuilding');
        if($request->getMethod() == 'POST') {
            $errors = array();
            $deliveryRegion = $request->request->get('deliveryRegion');
            $deliveryCity = $request->request->get('deliveryCity');
            $deliveryAddress = $request->request->get('deliveryAddress');
            $deliveryBuilding = $request->request->get('deliveryBuilding');
            $phone = $request->request->get('phone');
            $payType = $request->request->get('payType');
            $activity = $request->request->get('activity');
            if ($deliveryRegion > 0 && $deliveryCity > 0 && strlen($deliveryAddress) > 0 && strlen($deliveryBuilding) && strlen($phone) > 0 && strlen($payType) > 0 && strlen($payType) > 0 && ($activity === '1' || $activity === '0')) {
                $session->set('deliveryRegion', $deliveryRegion);
                $session->set('deliveryCity', $deliveryCity);
                $session->set('deliveryAddress', $deliveryAddress);
                $session->set('deliveryBuilding', $deliveryBuilding);
                $session->set('phone', $phone);
                $session->set('payType', $payType);
                $session->set('activity', $activity);
                $user = $this->getUser();
                //Here we store all data to database
                $order = new InsuranceOrder();
                if ($activity === '1') $order->setActive(1);
                $order->setStatus('W'); //W - waiting, P  - paid, C - confirmed
                $order->setPayStatus(0);
                $order->setOrderDate(new \DateTime('now'));
                //TODO add next free policy number!!!!!!!
                try {
                    if (is_null($user)) $user = $this->getDoctrine()->getRepository('ApplicationSonataUserBundle:User')->findOneById(1);
                    $order->setUser($user);
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['user'] = "Не найден пользователь";
                }

                try {
                    $company = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceCompany')->findOneById(static::DEFAULT_COMPANY_ID);
                    $order->setCompany($company);
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['company'] = "Не найдена компания";
                }

                try {
                $carModel = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')->findOneById($session->get('carModel'));
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['carModel'] = "Не найдена компания";
                }
                $order->setCarModel($carModel);

                $order->setDisplacement($session->get('displacement'));

                $order->setCarAge($session->get('carAge'));

                try {
                    $registerCity = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($session->get('registerCity'));
                    $order->setRegisterCity($registerCity);
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['registerCity'] = "Не найден город регистрации ТС";
                }

                $order->setInsuranceTerm($session->get('insuranceTerm'));

                $order->setSumDgo($session->get('dgoSum'));

                $order->setPriceDgo($session->get('priceDGO'));

                $order->setSumNs($session->get('nsSum'));

                $order->setPassengerCount($session->get('passengerCount'));

                $order->setPriceNs($session->get('priceNs'));

                $order->setDiscount($session->get('discount'));

                $order->setPrice($session->get('price'));

                $order->setActiveFrom(new \DateTime($session->get('activeFrom')));

                $order->setVinCode($session->get('vinCode'));

                $order->setCarNumber($session->get('carNumber'));

                $order->setSurname($session->get('surname'));

                $order->setFirstname($session->get('firstname'));

                $order->setMiddlename($session->get('middlename'));

                $order->setDocumentType($session->get('documentType'));

                $order->setDocumentSerie($session->get('documentSerie'));

                $order->setDocumentNumber($session->get('documentNumber'));

                $order->setDocumentAuthority($session->get('documentAuthority'));

                $order->setDocumentDate(new \DateTime($session->get('documentDate')));

                $order->setPhone($session->get('phone'));

                try {
                    $city = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($session->get('city'));
                    $order->setCity($city);
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['city'] = "Не найден город регистрации документа";
                }

                $order->setRegisterAddress($session->get('registerAddress'));

                $order->setRegisterBuilding($session->get('registerBuilding'));

                try {
                    $deliveryCity = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($session->get('deliveryCity'));
                    $order->setDeliveryCity($deliveryCity);
                } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                    $errors['city'] = "Не найден город доставки полиса";
                }

                $order->setDeliveryAddress($session->get('deliveryAddress'));

                $order->setDeliveryBuilding($session->get('deliveryBuilding'));

                $order->setPayType($payType);
//$order->
                $policy = $this->getDoctrine()->getRepository('InsuranceContentBundle:Policy')->findOneBy(
                    array(
                      'status' => 0,
                      'company' => static::DEFAULT_COMPANY_ID,
                  ),
                    array('id' => 'ASC')
                    );
                if ($policy) {
                    //$orderPolicy = array_shift($policy);
                    $policy->setStatus(1);
                    $order->setPolicy($policy);
                }
                if (count($errors) == 0) {
                    try {
                    $em = $this->getDoctrine()->getEntityManager();
                    //$em->persist($order);
                    //$em->flush();
                    } catch (\Exception $e) {
                        $errors['message'] = $e->getMessage();
                    }
                }
//                switch($payType) {
//                    case 'cash':
//                        return $this->redirect($this->generateUrl('success'));
//                        break;
//                }
                $resp = new Response();
                $resp->headers->set('Content-Type', 'application/json');
                if ($activity === '1') {
                    $resp->setContent(json_encode(array('href' => 'go.to.payment')));
                    return $resp;
                } elseif ($activity === '0') {
                    $resp->setContent(json_encode(array('message' => 'success')));
                    return $resp;
                }
            } else $errors['message'] = 'Все поля обязательны к заполнению';
            if (count($errors) > 0) return $this->redirect($this->generateUrl('step3'));
            }

        $city = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($session->get('city'));
        if (is_null($region) || is_null($city)) return $this->redirect($this->generateUrl('step2'));
        return $this->render('InsuranceContentBundle:Default:step_three.html.twig', array(
            'regions' => $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll(),
            'cities' => (!is_null($regionId)?$this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findByRegion($regionId):null),
            'region' => $region,
            'city' => $city,
            'deliveryAddress' => $deliveryAddress,
            'deliveryBuilding' => $deliveryBuilding,
            'errors' => null,
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }
    /**
     * Creates a new Feedback entity.
     *
     */
    public function createFeedbackAction(Request $request)
    {
        $entity  = new Feedback();
        $form = $this->createForm(new FeedbackType(), $entity);
        $form->bind($request);
        try {
        //if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $resp = new Response(json_encode(array('message' => 'success')));
            $resp->headers->set('Content-Type', 'application/json');
            return $resp;
        } catch (\Exception $e) {
            $response = new Response(json_encode(array('error' => $e->getMessage(), 'message' => 'error')));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    /**
     * Process calculator (save data to session) and redirect to next step - filling personal data
     *
     */
     //TODO Kill this method - it is useless
    public function processCalculatorAction(Request $request)
    {
        $calculator = $this->get('insurance.service.calculator');
        $calculator->setRateType('base')
            ->setCompany(2);
        $k1 = $calculator->getRate('Киев', 'region');
        $k2 = $calculator->getRate('1.7', 'displacement');
        $discount = $calculator->getRate(date('Y') - 2005, 'discount');
        $city = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById(78);
        //var_dump($k1->getValue(), $k2->getValue(), $discount->getValue(), $city->getValue());
        //$calculator->setRateType('base')
        //    ->setCompany(2);
        //$k1 = $calculator->getRate('Киев', 'region');
        //$k2 = $calculator->getRate('1.7', 'displacement');
        var_dump($request->getSession()->all());
        return new Response();
    }

    /**
     * Function processes discount using user input data (car age)
     * @return JSON object with "discount" param
     */
    public function getDiscountAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $carAge = $request->request->get('carAge');
            $calculator = $this->get('insurance.service.calculator');
            $calculator->setCompany(static::DEFAULT_COMPANY_ID)
                ->setRateType('base');
            $discount = $calculator->getRate(date('Y') - $carAge, 'year');
            if ($discount !== null) {
                $respString = json_encode(array('discount' => $discount->getValue()));
            } else $respString = json_encode(array('discount' => 0));
            $response = new Response($respString);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    /**
     * Calculate general insurance price
     * @return JSON object with "price" key
     */
    public function calculateInsuranceAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $calculator = $this->get('insurance.service.calculator');
            $registerCity = $request->request->get('registerCity');
            $displacement = $request->request->get('hDisplacement');
            $experience = $request->request->get('hExperience');
            $insuranceTerm = $request->request->get('insuranceTerm');
            if ($registerCity > 0 && $displacement > 0 && $experience > 0 &&
            $insuranceTerm > 0) {
                $city = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($registerCity);
                $price = $calculator->calculateCommon(array(
                    'region' => $city->getValue(),
                    'displacement' => $displacement,
                    'experience' => $experience,
                    'term' => $insuranceTerm,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                );
            }
            else $price = 0;
            $response = new Response(json_encode(array('price' => $price)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    /**
     * Calculate price of additional civil responsibility
     * @return JSON object with "priceDgo" key
     */
    public function calculateDgoAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $calculator = $this->get('insurance.service.calculator');
            $dgoSum = $request->request->get('hDGOSum');
            $displacement = $request->request->get('hDisplacement');
            $experience = $request->request->get('hExperience');
            $insuranceTerm = $request->request->get('insuranceTerm');
            if ($dgoSum > 0 && $displacement > 0 && $insuranceTerm > 0 && $experience > 0)
                $priceDgo = $calculator->calculateDgo(array(
                    'sum' => $dgoSum,
                    'displacement' => $displacement,
                    'experience' => $experience,
                    'term' => $insuranceTerm,
                    'taxi' => $request->request->get('taxiUse')? 1 : -1,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                );
            else $priceDgo = 0;
            $response = new Response(json_encode(array('priceDgo' => $priceDgo)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    /**
     * Calculate casualty insurance
     * @return JSON object with "priceNs" key
     */
    public function calculateNsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $calculator = $this->get('insurance.service.calculator');
            $nsSum = $request->request->get('hNSSum');
            $passengersCount = $request->request->get('passengersCount');
            if ($nsSum > 0)
                $priceNs = $calculator->calculateNs(array(
                    'sum' => $nsSum,
                    'passenger_count' => $passengersCount,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                );
            else $priceNs = 0;
            $response = new Response(json_encode(array('priceNs' => $priceNs)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }

    public function saveCalculationAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            /*$carBrand = $request->request->get('carBrand');
            $carModel = $request->request->get('hCarModel');
            $carAge = $request->request->get('carAge');
            $displacement = $request->request->get('hDisplacement');
            $registerRegion = $request->request->get('registerRegion');
            $registerCity = $request->request->get('registerCity');
            $insuranceTerm = $request->request->get('insuranceTerm');
            if ($request->request->get('cbDGO') == 'yes') {
                $dgoSum = $request->request->get('hDGOSum');
                $taxiUse = ($request->request->get('taxiUse') == '1' ? 1 : null);
            }
            if ($request->request->get('cbNS') == 'yes') {
                $nsSum = $request->request->get('hNSSum');
                $passengersCount = $request->request->get('passengersCount');
            }*/
            $cookieData = array(
                'carBrand' => $request->request->get('carBrand'),
                'carModel' => $request->request->get('hCarModel'),
                'carAge' => $request->request->get('carAge'),
                'displacement' => $request->request->get('hDisplacement'),
                'registerRegion' => $request->request->get('registerRegion'),
                'registerCity' => $request->request->get('registerCity'),
                'insuranceTerm' => $request->request->get('insuranceTerm'),
            );
            if ($request->request->get('cbDGO') == 'yes') {
                $cookieData['dgoSum'] = $request->request->get('hDGOSum');
                if ($request->request->get('taxiUse') == '1') $cookieData['taxiUse'] = '1';
            }
            if ($request->request->get('cbNS') == 'yes') {
                $cookieData['nsSum'] = $request->request->get('hNSSum');
                $cookieData['passengersCount'] = $request->request->get('passengersCount');
            }
            $calculationCookie = new Cookie('sc', serialize($cookieData), strtotime('+1 month'));
            $response = new Response(json_encode(array('message' => 'success')));
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->setCookie($calculationCookie);
            return $response;
        }
    }

    /**
     * Check for stored calculation cookie and restores data to session
     * @param \Symfony\Component\HttpFoundation\Request $request Symfony's request object
     * @return type Description
     */
    public function getStoredCalculation(\Symfony\Component\HttpFoundation\Request $request)
    {
        if ($calculationCookie = $request->cookies->get('sc')) {
            if ($calculationCookie = unserialize($calculationCookie)){
                $session = $request->getSession();
                //$session->start();
                foreach ($calculationCookie as $k => $v) {
                    $session->set($k, $v);
                }
                return true;
            } else return false;
        } else
            return false;
    }
}
