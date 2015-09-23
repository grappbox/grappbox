<?php

namespace GrappBox\Showcase\SharedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SharedBundle:Default:index.html.twig', array('name' => $name));
    }
}
