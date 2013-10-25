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
      $to = $this->sc->getParameter('contact.email');
      $from = $this->sc->getParameter('email.send.from');
      $conType = $entity->getConnectionType();
      if($conType == Feedback::CALLBACK) $feedbackTypeText = 'запрос на обратный звонок';
        elseif ($conType == Feedback::FEEDBACK) $feedbackTypeText = 'вопрос';
      $message = \Swift_Message::newInstance()
        ->setSubject('Поступил новый ' . $feedbackTypeText)
        ->setFrom($conType == Feedback::CALLBACK ? $from : $entity->getEmail())
        ->setTo($to)
        ->setBody(
            $this->sc->get('templating')->render(
                'InsuranceContentBundle:Notifications:feedbackNotification.txt.twig',
                array(
                'contact' => $entity,
                'feedbackTypeText' => $feedbackTypeText,
                )
            )
		)
        //->attach(\Swift_Attachment::fromPath('my-document.pdf'))
    ;
    $this->sc->get('mailer')->send($message);
    }
    if ($entity instanceof InsuranceOrder) {
        $from = $this->sc->getParameter('email.send.from');
        $siteName = $this->sc->getParameter('site.name');
        $siteDomain = $this->sc->getParameter('site.domain');
        $contactEmail = $this->sc->getParameter('contact.email');
        $contactPhone = $this->sc->getParameter('contact.phone');
      if ($entity->getPayStatus() == 0 && $entity->getPayType() == 'cash') {
          $to = $entity->getUser()->getEmail();
          $message = \Swift_Message::newInstance()
              ->setSubject(strtoupper($siteDomain) . ': Ваш заказ принят!')
              ->setFrom($from)
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
            $to = $entity->getUser()->getEmail();
            $message = \Swift_Message::newInstance()
                ->setSubject(strtoupper($siteDomain) . 'Оплата за Ваш заказ получена!')
                ->setFrom($from)
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
        } elseif ($entity->getActive() == 0 && strlen($entity->getHash()) == 40) {
            //Send notification to user that he has stored order without confirmation
            $to = $entity->getUser()->getEmail();
            $message = \Swift_Message::newInstance()
                ->setSubject(strtoupper($siteDomain) . 'Вы отложили решение по покупке полиса.')
                ->setFrom($from)
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
      $to = $this->sc->getParameter('admin.emails');
      $messageToAdmin = \Swift_Message::newInstance()
            ->setSubject('Поступил новый заказ!')
            ->setFrom($from)
            ->setTo($to)
            ->setBody(
            $this->sc->get('templating')->render(
                'InsuranceContentBundle:Notifications:orderAdminNotification.txt.twig',
                array(
                'order' => $entity,
                )
            )
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
                    $to = $entity->getUser()->getEmail();
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Ваш полис успешно оплачен. В скоре Вы получите оригинал.')
                        ->setFrom($from)
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
