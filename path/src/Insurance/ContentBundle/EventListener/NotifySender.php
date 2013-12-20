<?php
namespace Insurance\ContentBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Insurance\ContentBundle\Entity\Feedback;
use Insurance\ContentBundle\Entity\InsuranceOrder;

class NotifySender
{
    protected $sc;

    public function __construct($sc)
    {
        $this->sc = $sc;
    }

    public function generatePDFPolicy($orderEntity)
    {
        $tcPdf = new \TCPDF();
        $request = $this->sc->get('request');
        $router = $this->sc->get('router');
        try {
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
            $policyHTML = file_get_contents($protocol . $request->server->get('HTTP_HOST') . $router->generate('generate_html_policy', array('orderId' => $orderEntity->getId())));
            error_reporting(E_ERROR);
            $tcPdf->SetFont('dejavusans', '', 10);
            $tcPdf->AddPage();
            $tcPdf->writeHTML($policyHTML);
            $fileName = sha1(microtime());
            $file = $request->server->get('DOCUMENT_ROOT') . '/pdf/' . $fileName . '.pdf';
            $tcPdf->Output($file, 'F');
            $httpFile = $protocol . $request->server->get('HTTP_HOST') . '/pdf/' . $fileName . '.pdf';
            $orderEntity->setPdfUrl($httpFile);
        } catch (Exception $e) {
            die($e->getMessage());
            return false;
        }
        return $file;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Feedback) {
        //var_dump($this->sc->getParameter('admin_emails'));exit;
            $from = $this->sc->getParameter('email.send.from');
            $emailName = $this->sc->getParameter('email.name');
            $to = $this->sc->getParameter('contact.email');
            $conType = $entity->getConnectionType();
            if($conType == Feedback::CALLBACK) $feedbackTypeText = 'запрос на обратный звонок';
                elseif ($conType == Feedback::FEEDBACK) $feedbackTypeText = 'вопрос';
            $message = \Swift_Message::newInstance()
                ->setSubject('Поступил новый ' . $feedbackTypeText)
                ->setFrom(array($from => $emailName))
                ->setTo($to)
                ->setBody(
                $this->sc->get('templating')->render(
                        'InsuranceContentBundle:Notifications:feedbackNotification.html.twig',
                        array(
                        'contact' => $entity,
                        'feedbackTypeText' => $feedbackTypeText,
                        )
                    ),
                    'text/html'
                )
            //->attach(\Swift_Attachment::fromPath('my-document.pdf'))
            ;
            $this->sc->get('mailer')->send($message);
            }
            if (
            $entity instanceof InsuranceOrder && $entity->getActive() === 1
            && ($entity->getPayType() == 'terminal' || $entity->getPayType() == 'cash')
            ) {
                $from = $this->sc->getParameter('email.send.from');
                $emailName = $this->sc->getParameter('email.name');
                $siteName = $this->sc->getParameter('site.name');
                $siteDomain = $this->sc->getParameter('site.domain');
                $contactEmail = $this->sc->getParameter('contact.email');
                $contactPhone = $this->sc->getParameter('contact.phone');
                /*if ($entity->getPayStatus() == 0 && ($entity->getPayType() == 'terminal' || $entity->getPayType() == 'cash') && $entity->getActive() == 1) {
                    //If cash (terminal) payment processed but isn't payed than send notification about unpayed order with atteched electronical version of policy
                    $to = $entity->getUser()->getEmail();
                    $message = \Swift_Message::newInstance()
                        ->setSubject(strtoupper($siteDomain) . ': Ваш заказ принят!')
                        ->setFrom(array($from => $emailName))
                        ->setTo($to)
                        ->setBody(
                            $this->sc->get('templating')->render(
                                'InsuranceContentBundle:Notifications:unpayedOrderNotification.html.twig',
                                array(
                                    'order' => $entity,
                                    'siteName' => $siteName,
                                    'siteDomain' => $siteDomain,
                                    'contactEmail' => $contactEmail,
                                    'contactPhone' => $contactPhone,
                                )
                            ),
                            'text/html'
                        );
                    if ($pdfFile = $this->generatePDFPolicy($entity->getId())) {
                        $message->attach(\Swift_Attachment::fromPath($pdfFile)->setContentType('application/pdf')->setFilename('Полис ОСАГО.pdf'));
                    }
                    $this->sc->get('mailer')->send($message);
                } elseif ($entity->getPayType() != 'cash' && $entity->getPayStatus() == 1) {
                //After payment succesfully verified send message to user with attached electronical policy version and order details
                $to = $entity->getUser()->getEmail();
                $message = \Swift_Message::newInstance()
                    ->setSubject(strtoupper($siteDomain) . ': Оплата за Ваш заказ получена!')
                    ->setFrom(array($from => $emailName))
                    ->setTo($to)
                    ->setBody(
                        $this->sc->get('templating')->render(
                            'InsuranceContentBundle:Notifications:payedOrderNotification.html.twig',
                            array(
                            'order' => $entity,
                            'siteName' => $siteName,
                            'siteDomain' => $siteDomain,
                            'contactEmail' => $contactEmail,
                            'contactPhone' => $contactPhone,
                            )
                    ),
                    'text/html'
              );
              if ($pdfFile = $this->generatePDFPolicy($entity->getId())) {
                  $message->attach(\Swift_Attachment::fromPath($pdfFile)->setContentType('application/pdf')->setFilename('Полис ОСАГО.pdf'));
              }
              $this->sc->get('mailer')->send($message);
            } elseif ($entity->getActive() == 0 && $entity->getPayStatus() == 0 && strlen($entity->getHash()) == 40) {
                    //Send notification to user that he has stored order without confirmation
                    $to = $entity->getUser()->getEmail();
                    $message = \Swift_Message::newInstance()
                        ->setSubject(strtoupper($siteDomain) . ': Ваш заказ ожидает подтверждения...')
                        ->setFrom(array($from => $emailName))
                        ->setTo($to)
                        ->setBody(
                            $this->sc->get('templating')->render(
                                'InsuranceContentBundle:Notifications:delayedOrderNotification.html.twig',
                                array(
                                    'order' => $entity,
                                    'siteName' => $siteName,
                                    'siteDomain' => $siteDomain,
                                    'contactEmail' => $contactEmail,
                                    'contactPhone' => $contactPhone,
                                )
                            ),
                            'text/html'
                        );
                    if ($pdfFile = $this->generatePDFPolicy($entity->getId())) {
                        $message->attach(\Swift_Attachment::fromPath($pdfFile)->setContentType('application/pdf')->setFilename('Полис ОСАГО.pdf'));
                    }
                    $this->sc->get('mailer')->send($message);
                }*/
                //Notify admin
                $to = $this->sc->getParameter('admin.emails');
                $messageToAdmin = \Swift_Message::newInstance()
                    ->setSubject('Поступил новый заказ!')
                    ->setFrom(array($from => $emailName))
                    ->setTo($to)
                    ->setBody(
                        $this->sc->get('templating')->render(
                            'InsuranceContentBundle:Notifications:orderAdminNotification.html.twig',
                            array(
                                'order' => $entity,
                            )
                        ),
                        'text/html'
                    );
                $this->sc->get('mailer')->send($messageToAdmin);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof InsuranceOrder) {
            $from = $this->sc->getParameter('email.send.from');
            $emailName = $this->sc->getParameter('email.name');
            $siteName = $this->sc->getParameter('site.name');
            $siteDomain = $this->sc->getParameter('site.domain');
            $contactEmail = $this->sc->getParameter('contact.email');
            $contactPhone = $this->sc->getParameter('contact.phone');
            if ($args->hasChangedField('payStatus') && !$args->hasChangedField('payType')) {
                if ($args->getOldValue('payStatus') == 0 && $args->getNewValue('payStatus') == 1
                    && $entity->getPayType() != 'cash' && $entity->getPayType() != 'terminal'
                ) {
                    $to = $entity->getUser()->getEmail();
                    $message = \Swift_Message::newInstance()
                        ->setSubject(strtoupper($siteDomain) . ': Оплата за Ваш заказ получена!')
                        ->setFrom(array($from => $emailName))
                        ->setTo($to)
                        ->setBody(
                            $this->sc->get('templating')->render(
                                'InsuranceContentBundle:Notifications:payedOrderNotification.html.twig',
                                array(
                                    'order' => $entity,
                                    'siteName' => $siteName,
                                    'siteDomain' => $siteDomain,
                                    'contactEmail' => $contactEmail,
                                    'contactPhone' => $contactPhone,
                                )
                            ),
                            'text/html'
                        );
                    if ($pdfFile = $this->generatePDFPolicy($entity)) {
                        $message->attach(\Swift_Attachment::fromPath($pdfFile)->setContentType('application/pdf')->setFilename('Полис ОСАГО.pdf'));
                    }
                    $this->sc->get('mailer')->send($message);
                    //And after successfull payment notify admin
                    $to = $this->sc->getParameter('admin.emails');
                    $messageToAdmin = \Swift_Message::newInstance()
                        ->setSubject('Оплачен новый заказ!')
                        ->setFrom(array($from => $emailName))
                        ->setTo($to)
                        ->setBody(
                            $this->sc->get('templating')->render(
                                'InsuranceContentBundle:Notifications:orderAdminNotification.html.twig',
                                array(
                                    'order' => $entity,
                                )
                            ),
                            'text/html'
                        );
                    $this->sc->get('mailer')->send($messageToAdmin);
                }
            }
        }
    }
}

?>
