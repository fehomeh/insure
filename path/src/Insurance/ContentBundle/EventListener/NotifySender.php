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
    if ($entity instanceof InsuranceOrder) {
        $from = $this->sc->getParameter('email.send.from');
		$emailName = $this->sc->getParameter('email.name');
        $siteName = $this->sc->getParameter('site.name');
        $siteDomain = $this->sc->getParameter('site.domain');
        $contactEmail = $this->sc->getParameter('contact.email');
        $contactPhone = $this->sc->getParameter('contact.phone');
      if ($entity->getPayStatus() == 0 && ($entity->getPayType() == 'cash' || $entity->getPayType() == 'terminal') && $entity->getActive() == 1) {
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
          $this->sc->get('mailer')->send($message);
        } elseif ($entity->getPayType() != 'cash' && $entity->getPayStatus() == 1) {
            //After payment succesfully verified send message to user with attached electronical policy version and order details
            $to = $entity->getUser()->getEmail();
            $message = \Swift_Message::newInstance()
                ->setSubject(strtoupper($siteDomain) . 'Оплата за Ваш заказ получена!')
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
          $this->sc->get('mailer')->send($message);
        } elseif ($entity->getActive() == 0 && $entity->getPayStatus() == 0 && strlen($entity->getHash()) == 40) {
            //Send notification to user that he has stored order without confirmation
            $to = $entity->getUser()->getEmail();
            $message = \Swift_Message::newInstance()
                ->setSubject(strtoupper($siteDomain) . 'Вы отложили решение по покупке полиса.')
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
            $this->sc->get('mailer')->send($message);
        }
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
            if ($args->hasChangedField('payStatus')) {
                if ($args->getOldValue('payStatus') == 0 && $args->getNewValue('payStatus') == 1) {
                    $from = $this->sc->getParameter('email.send.from');
					$emailName = $this->sc->getParameter('email.name');
                    $to = $entity->getUser()->getEmail();
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Ваш полис успешно оплачен. В скоре Вы получите оригинал.')
                        ->setFrom(array($from => $emailName))
                        ->setTo($to)
                        ->setBody(
                            $this->sc->get('templating')->render(
                                'InsuranceContentBundle:Notifications:policySendNotification.html.twig',
                                array('order' => $entity)
                                ),
                                'text/html'
                        )
              //->attach(\Swift_Attachment::fromPath('my-document.pdf'))
                ;
                $this->sc->get('mailer')->send($message);
                }
            }
        }
    }
}

?>
