<?php

namespace GrappboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('GrappboxBundle:Default:index.html.twig', array('name' => $name));
    }
}
