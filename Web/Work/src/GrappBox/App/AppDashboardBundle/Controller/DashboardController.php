<?php

namespace GrappBox\App\AppDashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller
{
    public function dashboardAction()
    {
        return $this->render('AppDashboardBundle:Dashboard:dashboard.html.twig');
    }
}
