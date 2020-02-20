<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Page d'accueil du back-office
 *
 * @Route("/admin")
 */
class MainController extends AbstractController
{
    /**
     * @Route("", name="admin_dashboard")
     */
    public function dashboard()
    {
        return $this->render('admin/main/dashboard.html.twig', []);
    }
}
