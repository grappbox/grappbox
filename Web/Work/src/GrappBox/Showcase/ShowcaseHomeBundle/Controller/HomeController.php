<?php

namespace GrappBox\Showcase\ShowcaseHomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('ShowcaseHomeBundle:Home:index.html.twig');
    }
}
