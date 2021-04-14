<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPasswordType;
use App\Form\ProfileType;
use App\Form\ProfileUploadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 *
 * Affichage et modification des infos des users
 *
 * @Route("/profil")
 */
class UserController extends AbstractController
{
    /**
     * Affichage du profil
     *
     * @Route("/{id}", name="user_profile", requirements={"id": "\d+"})
     */
    public function profile(User $user): Response
    {
        if (!$user->getIsActive()){
            throw $this->createNotFoundException("Utilisateur banni !");
        }
        if ($user->getIsDeleted()){
            throw $this->createNotFoundException("Utilisateur supprimé !");
        }

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

        //on garde une référence vers l'éventuelle photo de profil précédente
        $previousProfilePicture = $user->getPicture();

        $form = $this->createForm(ProfileUploadType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                //génère un nom de fichier aléatoire et unique
                $safeFilename = bin2hex(random_bytes(10)) . uniqid();
                //on rajoute l'extension du fichier
                $newFilename = $safeFilename.'.'.$user->getPictureUpload()->guessExtension();

                //on déplace le fichier vers notre destination
                //profile_pic_dir est défini dans config/services.yaml pour éviter d'avoir un chemin qui ressemble à ../../../../uploads !
                $user->getPictureUpload()->move($this->getParameter('profile_pic_dir'), $newFilename);

                //on hydrate le nom du fichier dans le user
                $user->setPicture($newFilename);

                //on enlève l'UploadedFile sinon ça bugue avec la sérialization
                $user->setPictureUpload(null);

                //on persiste le user
                $em->persist($user);
                $em->flush();

                //s'il y avait une autre photo précédemment...
                if (!empty($previousProfilePicture)){
                    //supprime la photo précédente, physiquement
                    $filelocation = $this->getParameter('profile_pic_dir') . "/" . $previousProfilePicture;
                    if (file_exists($filelocation)){
                        unlink($filelocation);
                    }
                    $this->addFlash('success', 'Photo de profil modifiée !');
                }
                //sinon, c'est que c'est une nouvelle photo
                else {
                    $this->addFlash('success', 'Photo de profil ajoutée !');
                }

                //on redirige vers le profil de ce user
                return $this->redirectToRoute('user_profile', ["id" => $user->getId()]);
            }

            //on enlève l'UploadedFile sinon ça bugue avec la sérialization
            $user->setPictureUpload(null);
        }

        //on recharge le donnée du user depuis la bdd au cas où
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

    /**
     * Modification du profil
     *
     * @Route("/modification/mot-de-passe", name="user_edit_password")
     */
    public function editPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        //récupère le user en session
        //ne jamais récupérer le user en fonction de l'id dans l'URL !
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(EditPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $hash = $passwordEncoder->encodePassword($user, $form->get('new_password')->getData());
            $user->setPassword($hash);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Mot de passe modifié !');
            //sinon ça bugue dans la session, ça me déconnecte
            //refresh() permet de re-récupérer les données fraîches depuis la bdd
            $entityManager->refresh($user);

            return $this->redirectToRoute("user_profile", ["id" => $user->getId()]);
        }

        return $this->render('user/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
