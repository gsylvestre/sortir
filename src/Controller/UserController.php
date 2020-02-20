<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\ProfileUploadType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * Affichage et modification des infos des users
 *
 * @Route("/profil")
 */
class UserController extends AbstractController
{
    /**
     * Chargement d'une photo de profil
     *
     * @Route("/{id}", name="user_profile", requirements={"id": "\d+"})
     */
    public function profile(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }


    /**
     * Chargement d'une photo de profil
     *
     * @Route("/modification/photo", name="user_upload")
     */
    public function upload(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $form = $this->createForm(ProfileUploadType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $safeFilename = bin2hex(random_bytes(10)) . uniqid();
                $newFilename = $safeFilename.'.'.$user->getPictureUpload()->guessExtension();

                $user->getPictureUpload()->move($this->getParameter('profile_pic_dir'), $newFilename);

                $user->setPicture($newFilename);

                $user->setPictureUpload(null);
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Photo de profil bien ajoutée !');
                return $this->redirectToRoute('user_profile', ["id" => $user->getId()]);
            }

            $user->setPictureUpload(null);
        }


        $em->refresh($user);

        return $this->render('user/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification du profil
     *
     * @Route("/modification", name="user_edit")
     */
    public function edit(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        //récupère le user en session
        //ne jamais récupérer le user en fonction de l'id dans l'URL !
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //séparé en 2 if pour pouvoir faire le refresh si le form n'est pas valide
            if ($form->isValid()){
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Profil modifié !');
                return $this->redirectToRoute("user_upload");
            }
            else {
                //sinon ça bugue dans la session, ça me déconnecte
                //refresh() permet de re-récupérer les données fraîches depuis la bdd
                $em->refresh($user);
            }
        }

        return $this->render('user/edit.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
