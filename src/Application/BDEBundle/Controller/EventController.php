<?php

namespace Application\BDEBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations as Rest;

class EventController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  description="Retrieves the list of events",
     *  resource=true,
     *  output={"class"="Application\BDEBundle\Entity\Event"},
     *  statusCodes={
     *      200="Returned when successful"
     *  }
     * )
     * @Rest\View(statusCode=200)
     */
    public function indexAction($_format)
    {
    	$em = $this->getDoctrine()->getManager();
    	$repository = $em->getRepository('ApplicationBDEBundle:Event');

        $all = ($_format != 'html' || $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED'));

    	$event_list = $repository->findBy(($all) ? array() : array('private' => false), array('dateStart' => 'ASC'));
    	$event_json = array();

    	foreach ($event_list as $event) {
    		$event_json[] = array(
                'id'        => $event->getId(),
                'title'     => $event->getTitle(),
                'start'     => $event->getDateStart()->format('Y-m-d H:i:s'),
                'end'       => $event->getDateEnd()->format('Y-m-d H:i:s'),
                'allDay'    => /*$event->getDateEnd()->diff($event->getDateStart())->h > 4*/ false,
                'place'     => $event->getPlace(),
                'color'     => $event->getCategory()->getBackgroundColor(),
                'textColor' => $event->getCategory()->getTextColor(),
                'club_id'   => (null !== $event->getClub() ? $event->getClub()->getId() : null) ,
    		);
    	}

        $view = $this->view($event_json, 200)
            ->setTemplate("ApplicationBDEBundle:Event:index.html.twig")
            ->setTemplateVar('events')
        ;

        return $this->handleView($view);
    }
}
