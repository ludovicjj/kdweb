<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserAccountAreaController extends AbstractController
{
    /**
     * @Route("/user/account/area", name="user_account_area")
     */
    public function index(): Response
    {
        return $this->render('user_account_area/index.html.twig', [
            'controller_name' => 'UserAccountAreaController',
        ]);
    }
}
