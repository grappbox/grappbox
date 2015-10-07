<?php

namespace GrappBox\Showcase\ShowcaseSharedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ShowcaseSharedBundle:Default:index.html.twig', array('name' => $name));
    }
}
