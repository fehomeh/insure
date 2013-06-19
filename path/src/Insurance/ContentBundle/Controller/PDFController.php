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

    public function notifyUserPolicyAction($orderId)
    {
      try {
        $order = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneById($orderId);
        if (!is_null($order)) {
        return $this->render('InsuranceContentBundle:PDF:userPolicy.html.twig', array(
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
