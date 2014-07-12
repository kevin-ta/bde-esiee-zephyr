<?php

namespace Application\Sonata\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Application\Sonata\AdminBundle\Form\SendMailType;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;

class MailController extends Controller
{
    /**
     * @Secure(roles="ROLE_COMMUNICATION")
     */
    public function sendMailAction()
    {

    	$post_list = $this->getDoctrine()
                      ->getManager()
                      ->getRepository('ApplicationSonataNewsBundle:Post')
                      ->findBy(
                      	array(), 
                      	array('publicationDateStart' => 'DESC')
                      );

		$form = $this->createForm(new SendMailType(), null, array(
		    'action' => $this->generateUrl('application_sonata_admin_send_mail'),
		    'method' => 'POST',
		));

		$request = $this->get('request');
		$posts = null;
		$message = null;
		$mail = null;
		$bind = false;

		if ($request->getMethod() == 'POST') {
			$form->bind($request);

			if ($form->isValid()) {
				$bind = true;
				if ($form->get('aperçu')->isClicked())
				{
					$posts = $form->get('news')->getData();
					$message = $form->get('intro')->getData();
					$mail = $form->get('email')->getData();
				} elseif ($form->get('envoyer')->isClicked()) {
			        $message = \Swift_Message::newInstance()
			            ->setSubject('N\'OUBLIE PAS DE MODIFIER LE SUJET :p (et d\'enlever le TR)')
			            ->setFrom('annales.esiee@gmail.com')
			            ->setTo($form->get('email')->getData())
			            ->setBody($this->renderView(
			                'ApplicationSonataAdminBundle:Mail:newsletter.html.twig', array(
								'posts'   => $form->get('news')->getData(),
								'message' => $form->get('intro')->getData(),
			                )),
			                'text/html'
			            )
			        ;
			        $this->get('mailer')->send($message);

					$this->get('session')
		            	 ->getFlashBag()
		            	 ->add('sonata_flash_success', 'Le mail a bien été envoyé.');
				}
			}
		}

		if (!$bind && $post_list !== null)
		{
    		$now = new \DateTime();
    		$oneWeek = (new \DateTime())->modify('+1 week');
			$checked = array();
			foreach($post_list as $post) {
				if ($post->getPublicationDateStart() >= $now && $post->getPublicationDateStart() <= $oneWeek)
					$checked[] = $post;
			}
			$form->get('news')->setData($checked);
		}

		return $this->render('ApplicationSonataAdminBundle:Admin:mail.html.twig', array(
			'admin_pool' => $this->container->get('sonata.admin.pool'),
			'form'       => $form->createView(),
			'posts'      => $posts,
			'message'    => $message,
			'mail'		 => $mail,
        ));
    }
}
