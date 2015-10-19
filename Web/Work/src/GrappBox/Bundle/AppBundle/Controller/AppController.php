<?php

namespace GrappBox\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AppController extends Controller
{
    public function indexAction()
    {
        return $this->render('AppBundle:Shared:app-base.html.twig');
    }
}
