<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Gestion des utilisateurs par les admins
 *
 * @Route("/admin/utilisateurs")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/{id}/bannir", name="admin_user_ban")
     */
    public function ban(User $user)
    {
        //si c'est un admin qui est en train de se faire bannir...
        if ($user->getIsAdmin()){
            $this->addFlash('danger', "Vous ne pouvez pas bannir un admin dude.");
            return $this->redirectToRoute('admin_user_list');
        }

        $em = $this->getDoctrine()->getManager();

        //on donne la valeur inverse à la valeur actuelle
        $user->setIsActive(!$user->getIsActive());
        $em->persist($user);
        $em->flush();

        $newStatus = $user->getIsActive() ? "débanni" : "banni";
        $this->addFlash('success', "L'utilisateur ".$user->getEmail()." a bien été $newStatus !");
        return $this->redirectToRoute('admin_user_list');
    }

    /**
     * @Route("", name="admin_user_list")
     */
    public function list()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepo->findBy([], ["lastname" => "ASC"]);

        return $this->render('admin/user/list.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/ajout", name="admin_user_create")
     */
    public function create(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute("admin_user_list");
        }

        return $this->render('admin/user/create.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
