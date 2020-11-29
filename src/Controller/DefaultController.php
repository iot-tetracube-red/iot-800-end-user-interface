<?php

namespace App\Controller;

use App\Entity\Home;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/ping", name="default")
     * @param Request $request
     * @param LoggerInterface $logger
     * @return JsonResponse|Response
     */
    public function index(Request $request, LoggerInterface $logger)
    {
	    $auth = $request->headers->get('x-penguins');

	    if ( ! isset( $auth ) || empty( $auth ) || $this->getParameter('SECURITY_HEADER') !== $auth ) {
		    $logger->info('Request without header');
	    	return new Response('', 401);
	    }
	    $ip = $request->getClientIp();
	    $regex = '/\d{2,3}\.\d{2,3}\.\d{2,3}\.\d{1,3}/';
	    if ( 1 !== preg_match( $regex, $ip ) ) {
		    $logger->info('Request from malformed ip address');
		    return new Response('', 400);
	    }
	    $em = $this->getDoctrine()->getManager();
	    $home = $em->getRepository(Home::class);
	    $smart_home = $home->findOneBy([]);
	    if (empty($smart_home)) {
		    $smart_home = new Home();
	    }
	    $smart_home->setIp($ip);
	    $smart_home->setDateModified(new \DateTime());
	    $em->persist($smart_home);
	    $em->flush();
	    return new JsonResponse([], 200);
    }
}
