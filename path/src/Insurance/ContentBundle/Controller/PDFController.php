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

    public function generatePDFPolicyAction($orderId)
    {
      $tcPdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, A4, true, 'UTF-8', false);
      $request = $this->getRequest();
      try {
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
      $policyHTML = file_get_contents($protocol . $request->server->get('HTTP_HOST') . $this->generateUrl('generate_html_policy', array('orderId' => $orderId)));
      error_reporting(E_ERROR);
      $tcPdf->SetFont('dejavusans', '', 10);
      $tcPdf->AddPage();
	  // set JPEG quality
      $tcPdf->setJPEGQuality(100);
// Image example with resizing
      $tcpdf->Image('{{asset('bundles/insurancecontent/images/our_logo.jpg')}}', '20', '40', 219, 54, 'JPG', 'http://polismarket.com.ua', 'left', false, 300, '', false, false, 0, false, false, false);
	   $tcpdf->Image('{{asset('bundles/insurancecontent/images/bbs_logo.jpg')}}', '20', '84', 219, 10, 'JPG', 'http://polismarket.com.ua', 'right', false, 300, '', false, false, 0, false, false, false);
      $tcPdf->writeHTML($policyHTML);
      $fileName = sha1(microtime());
      $file = $request->server->get('DOCUMENT_ROOT') . '/pdf/' . $fileName . '.pdf';
      $tcPdf->Output($file, 'F');
      $order = $this->getDoctrine()->getRepository('InsuranceContentBundle:InsuranceOrder')->findOneById($orderId);
      $httpFile = $protocol . $request->server->get('HTTP_HOST') . '/pdf/' . $fileName . '.pdf';
      $order->setPdfUrl($httpFile);
      $em = $this->getDoctrine()->getEntityManager();
      $em->persist($order);
      $em->flush();
      } catch (Exception $e) {
        return new Response($e->getMessage(), 500);
      }
      //return new Response($policyHTML);
      return $this->redirect($this->generateUrl('dashboard', array()));
    }
}
?>
