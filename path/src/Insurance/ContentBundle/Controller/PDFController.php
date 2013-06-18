<?php
namespace Insurance\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Insurance\ContentBundle\Entity\Feedback;
use Symfony\Component\HttpFoundation\Response;

class PDFController extends Controller {

    public function notifyFeedbackAction($feedbackId)
    {
        try{
            $feedback = $this->getDoctrine()->getRepository('InsuranceContentBundle:Feedback')->findOneById($feedbackId);
        } catch (Excepltion $e){
            return $this->render($e->getMessage(), 404);
        }
    }

    public function notifyUserPolicy($orderId)
    {
      try {
        $order = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOne($orderId);
        return $this->render('InsuranceContentBundle:PDFController:userPolicy.html.twig', array('order' => $order));
      } catch (Exception $e){
        return $this->render(new Response ($e->getMessage(), 404));
      }
    }
}

?>
