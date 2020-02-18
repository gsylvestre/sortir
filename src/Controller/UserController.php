<?php

namespace App\Controller;

use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profil")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/modification", name="user_edit")
     */
    public function edit(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()){
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Profil modifié !');
                return $this->redirectToRoute("home");
            }
            else {
                //sinon ça bugue dans la session, ça me déconnecte
                $em->refresh($user);
            }
        }

        return $this->render('user/edit.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
