<?php

namespace GrappBox\Bundle\ShowcaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('ShowcaseBundle:Home:index.html.twig');
    }
}
