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
        if (!$session->get('timerEnd')) {
            $session->set('timerEnd', date('d M Y H:i:s', time() + $this->container->getParameter('timer')));
        }
        $savedSum = $this->container->getParameter('economy.sum') +
            ($this->container->getParameter('economy.step') * floor(((time() - strtotime($this->container->getParameter('economy.startdate')))/86400)));
        if (strlen($savedSum) > 6) {
            $tmpTime = (int)(((1000000 - $this->container->getParameter('economy.sum'))/$this->container->getParameter('economy.step')) * 86400);
            $savedSum = $this->container->getParameter('economy.sum') +
            ($this->container->getParameter('economy.step') * floor(((time() - $tmpTime - strtotime($this->container->getParameter('economy.startdate')))/86400)));
        }
        return $this->render('InsuranceContentBundle:Default:index.html.twig', array(
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
                else
                    $session->set('taxiUse', 0);
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
                if ($passengersCount >= 0) $session->set('passengersCount', $passengersCount);
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
            if ($insuranceTerm > 7) {
                $discountObj = $calculator->getRate(date('Y') - $carAge, 'year');
                if ($discountObj)
                    $discount = $discountObj->getValue();
                else
                    $discount = 1;
            } else
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
            if (count($error) == 0) {
                //return $this->redirect($this->generateUrl('step2'));
                $response = new Response(json_encode(array('message' => 'success')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
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
        if($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
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
                $birthDate = $request->request->get('birthDate');
                $documentType = $request->request->get('documentType');
                $documentSerie = $request->request->get('documentSerie');
                $documentNumber = $request->request->get('documentNumber');
                $documentAuthority = $request->request->get('documentAuthority');
                $documentDate = $request->request->get('documentDate');
                $documentInn = $request->request->get('documentInn');
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

                if (strlen($birthDate) === 0) {
                    $error['birthDate'] = 'Не заполнено поле';
                } elseif (!preg_match('#[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}#', $birthDate)) {
                    $error['birthDate'] = 'Неправильный формат даты';
                } else {
                    $session->set('birthDate', $birthDate);
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

                if (strlen($documentInn) === 0) {
                    $error['documentInn'] = 'Не заполнено поле';
                } else {
                    $session->set('documentInn', $documentInn);
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
        //If user is authorized and URL has hash than get data from database and push it to session
        $securityContext = $this->container->get('security.context');
        $hash = $request->query->get('hash', null);
        $session = $request->getSession();
        if($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') && !is_null($hash)) {
            $savedOrder = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneByHash($hash);
            $session->set('carBrand', $savedOrder->getCarModel()->getBrand()->getId());
            $session->set('carModel', $savedOrder->getCarModel()->getId());
            $session->set('displacement', $savedOrder->getDisplacement());
            $session->set('carAge', $savedOrder->getCarAge());
            $session->set('registerRegion', $savedOrder->getRegisterCity()->getRegion()->getId());
            $session->set('registerCity', $savedOrder->getRegisterCity()->getId());
            $session->set('insuranceTerm', $savedOrder->getInsuranceTerm());
            $session->set('dgoSum', $savedOrder->getSumDgo());
            $session->set('priceDGO', $savedOrder->getPriceDgo());
            $session->set('taxiUse', $savedOrder->getTaxiUse());
            $session->set('nsSum', $savedOrder->getSumNs());
            $session->set('passengersCount', $savedOrder->getPassengerCount());
            $session->set('priceNs', $savedOrder->getPriceNs());
            $session->set('discount', $savedOrder->getDiscount());
            $session->set('price', $savedOrder->getPrice());

            $session->set('activeFrom', $savedOrder->getActiveFrom()->format('d.m.Y'));
            $session->set('vinCode', $savedOrder->getVinCode());
            $session->set('carNumber', $savedOrder->getCarNumber());
            $session->set('surname', $savedOrder->getSurname());
            $session->set('firstname', $savedOrder->getFirstname());
            $session->set('middlename', $savedOrder->getMiddlename());
            $session->set('birthDate', $savedOrder->getBirthDate()->format('d.m.Y'));
            $session->set('documentType', $savedOrder->getDocumentType());
            $session->set('documentSerie', $savedOrder->getDocumentSerie());
            $session->set('documentNumber', $savedOrder->getDocumentNumber());
            $session->set('documentAuthority', $savedOrder->getDocumentAuthority());
            $session->set('documentDate', $savedOrder->getDocumentDate()->format('d.m.Y'));
            $session->set('documentInn', $savedOrder->getDocumentInn());
            $session->set('phone', $savedOrder->getPhone());
            $session->set('region', $savedOrder->getCity()->getRegion()->getId());
            $session->set('city', $savedOrder->getCity()->getId());
            $session->set('registerAddress', $savedOrder->getRegisterAddress());
            $session->set('registerBuilding', $savedOrder->getRegisterBuilding());

            $session->set('deliveryRegion', $savedOrder->getDeliveryCity()->getRegion()->getId());
            $session->set('deliveryCity', $savedOrder->getDeliveryCity()->getId());
            $session->set('deliveryAddress', $savedOrder->getDeliveryAddress());
            $session->set('deliveryBuilding', $savedOrder->getDeliveryBuilding());
            $session->set('phone', $savedOrder->getPhone());
            $session->set('payType', $savedOrder->getPayType());
            $session->set('activity', $savedOrder->getActive());
            $needAuth = false;
        } elseif (!is_null($hash)) $needAuth = true;
            else $needAuth = false;
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
                elseif ($activity === '0') {
                    //Means that user decided to hold order for a while
                    $order->setActive(0);
                    $order->setHash(sha1(microtime()));
                }
                $order->setStatus('W'); //W - waiting, P  - paid, C - confirmed
                $order->setPayStatus(0);
                $order->setOrderDate(new \DateTime('now'));
                try {
                    $user = $this->get('security.context')->getToken()->getUser();
                    //if (is_null($user)) $user = $this->getDoctrine()->getRepository('ApplicationSonataUserBundle:User')->findOneById($user->getId());
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

                $order->setTaxiUse(($session->get('taxiUse') == '1' ? 1 : 0));

                $order->setSumDgo($session->get('dgoSum'));

                $order->setPriceDgo($session->get('priceDGO'));

                $order->setSumNs($session->get('nsSum'));

                $order->setPassengerCount($session->get('passengerCount'));

                $order->setPriceNs($session->get('priceNs'));

                $order->setDiscount($session->get('discount'));

                $order->setPrice($session->get('price'));

                $order->setTotalPrice($session->get('price') + $session->get('priceDGO') + $session->get('priceNs'));

                $order->setActiveFrom(new \DateTime($session->get('activeFrom')));

                $order->setVinCode($session->get('vinCode'));

                $order->setCarNumber($session->get('carNumber'));

                $order->setSurname($session->get('surname'));

                $order->setFirstname($session->get('firstname'));

                $order->setMiddlename($session->get('middlename'));

                $order->setBirthDate(new \DateTime($session->get('birthDate')));

                $order->setDocumentType($session->get('documentType'));

                $order->setDocumentSerie($session->get('documentSerie'));

                $order->setDocumentNumber($session->get('documentNumber'));

                $order->setDocumentAuthority($session->get('documentAuthority'));

                $order->setDocumentDate(new \DateTime($session->get('documentDate')));

                $order->setDocumentInn($session->get('documentInn'));

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

                $policy = $this->getDoctrine()->getRepository('InsuranceContentBundle:Policy')->findOneById($session->get('policy'));
                if ($policy) {
                    $policy->setStatus(1);
                    $order->setPolicy($policy);
                }
                if (count($errors) == 0) {
                    try {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($order);
                        $em->flush();
                        if ($activity == '1')
                            $session->set('orderState', 'success');
                        elseif ($activity == '0')
                            $session->set('orderState', 'delayed');
                        $session->remove('policy');
                        switch($payType) {
                            case 'cash':
                                return $this->redirect($this->generateUrl('finish'));
                                break;
                            case 'terminal':
                                return $this->redirect($this->generateUrl('finish'));
                                break;
                        }
                    } catch (\Exception $e) {
                        $errors['message'] = $e->getMessage();
                    }
                }
            } else $errors['message'] = 'Все поля обязательны к заполнению';
            if (count($errors) > 0) return $this->redirect($this->generateUrl('step3'));
            }

        $city = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findOneById($session->get('city'));
        if ($session->get('policy') != null) {
            $policy = $this->getDoctrine()->getRepository('InsuranceContentBundle:Policy')->findOneById($session->get('policy'));
        } elseif (!isset($policy)) {
            $policy = $this->getDoctrine()->getRepository('InsuranceContentBundle:Policy')->findOneBy(
                array(
                    'status' => 0,
                    'company' => static::DEFAULT_COMPANY_ID,
                ),
                array('id' => 'ASC')
                );
            if ($policy) {
                $session->set('policy', $policy->getId());
                $policy->setStatus(2);
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($policy);
                    $em->flush();
                } catch (\Exception $e) {
                    $errors['message'] = $e->getMessage();
                }
            }
        }
        if ((is_null($region) || is_null($city)) && is_null($hash)) return $this->redirect($this->generateUrl('step2'));
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
            'policy' => $policy,
            'showLogin' => $needAuth,
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
            $insuranceTerm = $request->request->get('insuranceTerm', 0);
            if ($insuranceTerm > 7) {
                $calculator = $this->get('insurance.service.calculator');
                $calculator->setCompany(static::DEFAULT_COMPANY_ID)
                    ->setRateType('base');
                $discount = $calculator->getRate(date('Y') - $carAge, 'year');
                if ($discount !== null) {
                    $respString = json_encode(array('discount' => $discount->getValue()));
                } else $respString = json_encode(array('discount' => 1));
            } else
                $respString = json_encode(array('discount' => 1));
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
            //Send email to user to remember about saved calculation
            $from = $this->container->getParameter('email.send.from');
            $emailName = $this->container->getParameter('email.name');
            $siteName = $this->container->getParameter('site.name');
            $siteDomain = $this->container->getParameter('site.domain');
            $contactEmail = $this->container->getParameter('contact.email');
            $contactPhone = $this->container->getParameter('contact.phone');
            $user = $this->get('security.context')->getToken()->getUser();
            $to = $user->getEmail();
            $message = \Swift_Message::newInstance()
                ->setSubject(strtoupper($siteDomain) . ': Ваш заказ принят!')
                ->setFrom(array($from => $emailName))
                ->setTo($to)
                ->setBody(
                      $this->render(
                          'InsuranceContentBundle:Notifications:savedCalculationNotification.html.twig',
                          array(
                          'siteName' => $siteName,
                          'siteDomain' => $siteDomain,
                          'contactEmail' => $contactEmail,
                          'contactPhone' => $contactPhone,
                          )
                  ),
                  'text/html'
            );
          $this->container->get('mailer')->send($message);
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

    public function clearStoredDataAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $timerEnd = $request->getSession()->get('timerEnd');
            $request->getSession()->clear();
            $request->getSession()->set('timerEnd', $timerEnd);
            $response = new Response();
            $response->headers->clearCookie('sc');
            return $response;
        }
    }

    public function finishAction(Request $request)
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        if ($request->getSession()->get('orderState') == 'success') {
            //$request->getSession()->clear();
            $response = new Response();
            $response->headers->clearCookie('sc');
            $message = 'Ваш заказ принят!<br>Спасибо за то, что воспользовались нашими услугами!';
        } elseif ($request->getSession()->get('orderState') == 'delayed') {
            //$request->getSession()->clear();
            $response = new Response();
            $response->headers->clearCookie('sc');
            $message = 'Ваш заказ сохранен!<br>На Ваш электронный адрес выслано письмо со ссылкой для завершения заказа.<br>Спасибо за то, что воспользовались нашими услугами!';
        } else $message = 'Возникла ошибка при оформлении, попробуйте, пожалуйста, позже!';
        return $this->render('InsuranceContentBundle:Default:finish.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
            'message' => $message,
        ));
    }

    public function aboutAction()
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        return $this->render('InsuranceContentBundle:Default:about.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }

    public function contactsAction()
    {
        $feedbackForm = $this->createForm(new FeedbackType());
        return $this->render('InsuranceContentBundle:Default:contacts.html.twig', array(
            'feedback_form' => $feedbackForm->createView(),
            'callback_form' => $feedbackForm->createView(),
        ));
    }
}
