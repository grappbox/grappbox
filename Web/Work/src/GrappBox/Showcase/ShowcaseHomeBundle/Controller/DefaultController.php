<?php

namespace GrappBox\Showcase\ShowcaseHomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ShowcaseHomeBundle:Default:index.html.twig');
    }
}
