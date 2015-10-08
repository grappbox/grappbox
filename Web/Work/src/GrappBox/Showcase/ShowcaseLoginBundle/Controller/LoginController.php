<?php

namespace GrappBox\Showcase\ShowcaseLoginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LoginController extends Controller
{
    public function indexAction()
    {
        return $this->render('ShowcaseLoginBundle:Login:index.html.twig');
    }
}
