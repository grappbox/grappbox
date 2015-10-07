<?php

namespace WhiteBoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WhiteBoardBundle:Default:index.html.twig', array('name' => $name));
    }
}
