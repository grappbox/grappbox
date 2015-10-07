<?php

namespace WhiteBoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WhiteboardController extends Controller
{
    public function indexAction()
    {
        return $this->render('WhiteBoardBundle:Whiteboard:index.html.twig');
    }
}