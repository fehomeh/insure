<?php

namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\Region;
use Insurance\ContentBundle\Entity\City;
use Insurance\ContentBundle\Form\FeedbackType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Insurance\ContentBundle\Entity\Feedback;


class DefaultController extends Controller
{
    const DEFAULT_COMPANY_ID = 2;
    public function indexAction($name = 'Stranger')
    {
      $regions = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll();
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
        return $this->render('InsuranceContentBundle:Default:index.html.twig', array(
          'regions' => $regions,
          'brands' => $carBrands,
          'carBrand' => $carBrand,
          'models' => $carModels,
          'carModel' => $session->get('carModel'),
          'feedback_form' => $feedbackForm->createView(),
          'callback_form' => $feedbackForm->createView(),
          ));
    }

    /**
     * AJAX action to get cities list after select some region on page
     * @return JSON encoded list of cities
     */
    public function getCitiesAction()
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

    public function getCarModelsAction()
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
        $session->set('carBrand', $request->request->get('carBrand'));
        $session->set('carModel', $request->request->get('carModel'));
        return $this->redirect($this->generateUrl('step1'));
    }
    
    /**
     * Action renders calculator template and processes it data: saves to session
     * check for errors, calculates prices and redirects user to next page - formalization
     */
    public function calculatorAction(Request $request)
    {
        $session = $this->getRequest()->getSession();
        $calculator = $this->get('insurance.service.calculator');
        if ($carBrand = $session->get('carBrand')) {
          $models = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')
              ->findByBrand($carBrand);
        } else $models = null;
        if ($region = $session->get('region')) {
            $cities = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findByRegion($region);
        } else $cities = null;
        if ($request->getMethod() == 'POST') {
            $session = $request->getSession();
            $calculator = $this->get('insurance.service.calculator');
            $carBrand = $request->request->get('carBrand');
            $carModel = $request->request->get('carModel');
            $carAge = $request->request->get('carAge');
            $experience = $request->request->get('hExperience');
            $displacement = $request->request->get('hDisplacement');
            $registerRegion = $request->request->get('registerRegion');
            $registerCity = $request->request->get('registerCity');
            $insuranceTerm = $request->request->get('insuranceTerm');
            $dgoSum = $request->request->get('hDGOSum');
            $nsSum = $request->request->get('hNSSum');
            if ($carBrand > 0) $session->set('carBrand', $carBrand);
            if ($carModel > 0) $session->set('carModel', $carModel);
            if ($displacement > 0) $session->set('displacement', $displacement);
            if ($carModel > 0) $session->set('carAge', $carModel);
            if ($registerRegion > 0) $session->set('registerRegion', $registerRegion);
            if ($registerCity > 0) $session->set('registerCity', $registerCity);
            if ($insuranceTerm > 0) $session->set('insuranceTerm', $insuranceTerm);
            if ($request->request->get('cbDGO') == 'yes' && $dgoSum > 0) 
            {
                $session->set('dgoSum', $dgoSum);
                $session->set('priceDGO', $calculator->calculateDgo(array(
                    'sum' => $dgoSum,
                    'displacement' => $displacement,
                    'experience' => $experience,
                    'term' => $insuranceTerm,
                    'taxi' => $request->request->get('taxiUse')? 1 : -1,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                ));
            }
            if ($request->request->get('cbNS') == 'yes' && $nsSum > 0) {
                $session->set('nsSum', $nsSum);
                $session->set('priceNs', $calculator->calculateNs(array(
                    'sum' => $nsSum,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                ));
            }
            $session->set('price', $calculator->calculateCommon(array(
                'region' => $registerCity,
                'displacement' => $displacement,
                'experience' => $experience,
                'term' => $insuranceTerm,
                'year' => $carAge,
                'company' => static::DEFAULT_COMPANY_ID,
            )));
            $this->redirect($this->generateUrl('form'));
        }
        return $this->render('InsuranceContentBundle:Default:step_one.html.twig',array(
            'carBrand' => $session->get('carBrand'),
            'carModel' => $session->get('carModel'),
            'brands' => $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findAll(),
            'models' => $models,
            'regions' => $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll(),
            'cities' => $cities,
            'region' => $region,
            'city' => $session->get('city'),
            'carAgeFrom' => $this->container->getParameter('car.age.from'),
            'carAgeTo' => (int)date('Y'),
            'displacement' => $this->container->getParameter('displacement'),
            'insureTerm' => $this->container->getParameter('insure.period'),
            'dgoSum' => $this->container->getParameter('dgo.sum'),
            'nsSum' => $this->container->getParameter('ns.sum'),
            'passengers' => $this->container->getParameter('passengers'),
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

        //if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('feedback_show', array('id' => $entity->getId())));
        //}

        return $this->render('InsuranceContentBundle:Default:index.html.twig', array(
            'regions' => null,
            'brands' => null,
            'entity' => $entity,
            'feedback_form'   => $form->createView(),
            'callback_form'   => $form->createView(),
        ));
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
        var_dump($k1->getValue(), $k2->getValue());
        //$calculator->setRateType('base')
        //    ->setCompany(2);
        //$k1 = $calculator->getRate('Киев', 'region');
        //$k2 = $calculator->getRate('1.7', 'displacement');
        var_dump($calculator->calculateCommon(array(
          'region' => 'Киев',
          'displacement' => '1.7',
          'experience' => '2',
          'term' => '12',
          'year' => '2005',
          'company' => static::DEFAULT_COMPANY_ID,
        )));
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
            $discount = $calculator->getRate($carAge, 'year');
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
    public function calculateInsurance(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $calculator = $this->get('insurance.service.calculator');
            $registerCity = $request->request->get('registerCity');
            $displacement = $request->request->get('hDisplacement');
            $experience = $request->request->get('hExperience');
            $insuranceTerm = $request->request->get('insuranceTerm');
            $carAge = $request->request->get('carAge');
            if ($registerCity > 0 && $displacement > 0 && $experience > 0 &&
            $insuranceTerm > 0 && $carAge > 0)
                $price = $calculator->calculateCommon(array(
                    'region' => $registerCity,
                    'displacement' => $displacement,
                    'experience' => $experience,
                    'term' => $insuranceTerm,
                    'year' => $carAge,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                );
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
    public function calculateDgo(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $calculator = $this->get('insurance.service.calculator');
            $dgoSum = $request->request->get('hDgoSum');
            $displacement = $request->request->get('hDisplacement');
            $experience = $request->request->get('hExperience');
            $insuranceTerm = $request->request->get('insuranceTerm');
            if ($dgoSum > 0 && $displacement > 0 && $insuranceTerm > 0 && $experience > 0)
                $priceDgo = $calculator->calculateCommon(array(
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
    public function calculateNs(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $calculator = $this->get('insurance.service.calculator');
            $nsSum = $request->request->get('hNSSum');
            if ($nsSum > 0)
                $priceNs = $calculator->calculateCommon(array(
                    'sum' => $nsSum,
                    'company' => static::DEFAULT_COMPANY_ID,
                    )
                );
            else $priceNs = 0;
            $response = new Response(json_encode(array('priceNs' => $priceNs)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else throw $this->createNotFoundException('Wrong request!');
    }
    
    /**
     * Output registration and policy formalization form also process data from this form
     * 
     */
    public function formalizationAction()
    {
        
    }
}
