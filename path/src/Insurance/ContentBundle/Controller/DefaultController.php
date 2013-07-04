<?php

namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\Region;
use Insurance\ContentBundle\Entity\City;
use Insurance\ContentBundle\Form\FeedbackType;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction($name = 'Stranger')
    {
      $regions = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region')->findAll();
      $carBrands = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarBrand')->findAll();
      $feedbackForm = $this->createForm(new FeedbackType());
      //$region->setValue('Киевская');
      //$em->persist($region);
      //$em->flush();
      //$productRep = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region');
      //$reg = $productRep->find('1');
        return $this->render('InsuranceContentBundle:Default:index.html.twig', array(
          'regions' => $regions,
          'brands' => $carBrands,
          'feedback_form' => $feedbackForm->createView()));
    }

    public function sendAction()
    {
        $name = 'sly';
        $message = \Swift_Message::newInstance()
        ->setSubject('Hello Email')
        ->setFrom('root@localhost')
        ->setTo('fomenkos@fomenko-s')
        ->setBody(
            $this->renderView(
                'InsuranceContentBundle:Default:ask_notify.html.twig',
                array('name' => $name)
            )
        );
    $this->get('mailer')->send($message);

    return $this->render('InsuranceContentBundle:Default:index.html.twig',  array('name' => $name, 'id' => 1));
    }

    public function getCitiesAction()
    {
      $request = $this->getRequest();
      $region_id = $request->get('region_id');
      $cities = $this->getDoctrine()->getRepository('InsuranceContentBundle:City')->findBy(
        array('region' => $region_id), array('value' => 'ASC'));
      foreach ($cities as $city)
      $city_list[$city->getId()] = $city->getValue();
      $response = new Response(json_encode($city_list));
      $response->headers->set('Content-Type', 'application/json');
      return $response;
    }
    public function getCarModelsAction()
    {
      $request = $this->getRequest();
      $brand_id = $request->get('brand_id');
      $models = $this->getDoctrine()->getRepository('InsuranceContentBundle:CarModel')->findBy(
        array('brand' => $brand_id), array('value' => 'ASC'));
      foreach ($models as $model)
      $models_list[$model->getId()] = $model->getValue();
      $response = new Response(json_encode($models_list));
      $response->headers->set('Content-Type', 'application/json');
      return $response;
    }

    public function goToStepOneAction()
    {
      $session = $this->getRequest()->getSession();
      $request = $this->getRequest();
      $session->set('carBrand', $request->request->get('carBrand'));
      $session->set('carModel', $request->request->get('carModel'));
      return $this->redirect($this->generateUrl('step1'));
    }

    public function stepOneAction()
    {
      var_dump($this->getRequest()->getSession()->get('carModel'));
      return new Response();
    }
}
