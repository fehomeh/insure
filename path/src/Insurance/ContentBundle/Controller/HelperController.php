<?php

namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\InsuranceCompany;
use Insurance\ContentBundle\Entity\Policy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Insurance\ContentBundle\Form\PolicyGenerateType;

class HelperController extends Controller
{
  public function generatePolicyAction (Request $request = null)
  {
    //sprintf('%08d', 1234567);
    $form = $this->createForm(new PolicyGenerateType());
    if ('POST' === $request->getMethod()){
      $form->bindRequest($request);
      if ($form->isValid()){
        $postData = $request->request->get('policy_generator');
        $em = $this->getDoctrine()->getEntityManager();
        $validator = $this->get('validator');
        $company = $em->getRepository('InsuranceContentBundle:InsuranceCompany')->findOneBy(array(
            'id' => $postData['insuranceCompany']
        ));
        $counter = 0; //generated polices counter
        for($i = (int)$postData['start_no'];$i<=$postData['end_no']; $i++) {
          $policy = new Policy();
          $policy->setCompany($company)
          ->setStatus(0)
          ->setSerie($postData['serie'])
          ->setValue(sprintf("%d", $i));
          $errors = $validator->validate($policy);
          if (count($errors) == 0) {
            $em->persist($policy);
            $counter++;
          }
          unset($policy);
        }
        //foreach($errors as $errorV) foreach ($errorV as $error)
          //var_dump($error->getMessage(), $error->getCode(), $error->getInvalidValue(), $error->getMessageTemplate());
        $em->flush();
        $request->getSession()->getFlashBag()->add('generate_success_message', 'Номера полисов сгенерированы успешно! Количество добавленных записей: ' . $counter . '.');
        return $this->redirect($this->generateUrl('sonata_admin_dashboard'));
      }
    }
    return $this->render('InsuranceContentBundle:Helper:generate_policy.html.twig', array('form' => $form->createView()));
  }

  /**
   * Generates HTML document - Policy to render it into PDF or display in popup like html
   * @param integer $orderId
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function generateHTMLPolicyAction($orderId)
    {
      try {
        $order = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneById($orderId);
        if (!is_null($order)) {
        return $this->render('InsuranceContentBundle:Helper:userPolicy.html.twig', array(
            'order' => $order,
            'date_from' => date('00:00 d.m.Y'),
            'date_to' => date('23:59 d.m.Y', strtotime('+11 months 30 days')),
            'pay_date' => date('d.m.Y H г. i хв. s с'),
            ));
            }
        else return new Response('<html><head></head><body>Not found!</body></html>', 404);
      } catch (Exception $e){
        return $this->render(new Response ($e->getMessage(), 404));
      }
    }
}

?>
