<?php

namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\Region;

class DefaultController extends Controller
{
    public function indexAction($name = 'Stranger')
    {
      $region = new Region();
      //$em = $this->getDoctrine()->getManager();
      //$region->setValue('Киевская');
      //$em->persist($region);
      //$em->flush();
      $productRep = $this->getDoctrine()->getRepository('InsuranceContentBundle:Region');
      $reg = $productRep->find('1');
        return $this->render('InsuranceContentBundle:Default:index.html.twig', array('name' => $name, 'id' => 1));
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
}
