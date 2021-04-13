<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserCsvUploadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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


    /**
     * @Route("/charger-un-csv", name="admin_user_load_csv")
     */
    public function loadCsvFile(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $csvFileForm = $this->createForm(UserCsvUploadType::class);

        $csvFileForm->handleRequest($request);

        if ($csvFileForm->isSubmitted() && $csvFileForm->isValid()){
            $formData = $csvFileForm->getData();

            /** @var UploadedFile $csvFile */
            $csvFile = $formData['csv'];

            //ouvre le fichier temporaire... on ne le sauvegarde pas sur le serveur, on ne fait qu'en récupérer les données
            $handle = fopen($csvFile->getRealPath(), 'r');

            //on récupère les lignes une par une
            $i = -1;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                dump($data);
                $i++;
                //on skip la première ligne qui contient normalement les titres de colonnes
                if ($i === 0) continue;

                //on crée un user pour cette ligne, avec des valeurs par défaut d'abord
                $user = new User();
                $user->setIsActive(1);
                $user->setIsAdmin(false);
                $user->setCreatedDate(new \DateTime());
                $user->setRoles(["ROLE_USER"]);

                //on utilise les valeurs du csv
                $user->setEmail($data[0]);

                //mot de passe par défaut
                $plainPassword = "passwordounet";

                //mais si un mot de passe est renseigné, on l'utilise
                if (!empty($data[1])){
                    $plainPassword = $data[1];
                }

                //on le hash
                $hash = $passwordEncoder->encodePassword($user, $plainPassword);
                $user->setPassword($hash);

                $user->setFirstname($data[2]);
                $user->setLastname($data[3]);
                $user->setPhone($data[4]);

                //on affecte l'école choisie dans le form pour ce csv
                $user->setSchool($formData['school_site']);

                //on sauvegarde chaque user...
                $entityManager->persist($user);

            }

            //mais on ne flush qu'une fois
            $entityManager->flush();

            $this->addFlash('success', $i . " participants ajoutés !");
            //recharge la page pour éviter la resoumission du csv sur un f5 par exemple
            return $this->redirectToRoute('admin_user_load_csv');
        }

        return $this->render('admin/user/load_csv_file.html.twig', [
            'csvFileForm' => $csvFileForm->createView()
        ]);
    }

    /**
     * Permet à l'admin de télécharger le .csv tel qu'il est attendu par notre code...
     *
     * @Route("/telecharger-un-modele-de-csv", name="admin_user_download_csv_model")
     */
    public function downloadCsvFileModel(Request $request)
    {
        $titles = ["email", "mot de passe", "prénom", "nom", "téléphone"];
        $response = new Response(implode(", ", $titles));
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="modele-participants.csv"');

        return $response;
    }
}
