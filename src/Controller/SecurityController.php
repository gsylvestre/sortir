<?php

namespace App\Controller;

use App\Entity\ForgotPasswordToken;
use App\Entity\User;
use App\Form\ForgotPasswordStep1Type;
use App\Form\NewPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Connexion et déconnexion. Généré par Symfony presque à 100%.
 * L'inscription du user se fait dans Controller/Admin/UserController.php
 *
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * Cette page affiche et traite le formulaire d'oubli de mot de passe
     * On y receuille le mail du user et on envoie un message si tout est valide
     *
     * @Route("/mot-de-passe-oublie", name="security_forgot_password_step_1")
     */
    public function forgotPasswordStep1(Request $request, MailerInterface $mailer)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);

        $form = $this->createForm(ForgotPasswordStep1Type::class, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $email = $form->getData()['email'];

            //cherche un utilisateur ayant cet email
            $foundUser = $userRepo->findOneBy(["email" => $email]);
            //si on ne le trouve pas... errreur
            if (empty($foundUser)){
                $this->addFlash("danger", "Oups ! Cet email n'a pas été trouvé !");
                $this->redirectToRoute('security_forgot_password_step_1');
            }

            $em = $this->getDoctrine()->getManager();
            $tokenRepo = $this->getDoctrine()->getRepository(ForgotPasswordToken::class);

            //supprime les éventuels tokens précédents de ce user
            $userPreviousTokens = $tokenRepo->findBy(['user' => $foundUser]);
            foreach($userPreviousTokens as $tokenToRemove){
                $em->remove($tokenToRemove);
            }

            //crée un nouveau token
            $token = new ForgotPasswordToken($foundUser);

            //sauvegarde le token
            $em->persist($token);
            $em->flush();

            //génère l'URL à cliquer qui sera envoyée dans le mail
            //l'URL doit être absolue
            $emailUrl = $this->generateUrl('security_forgot_password_step_3', [
                'selector' => $token->getSelector(),
                'token' => $token->getClearToken()
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            //prépare l'email
            //j'Utilise le nouveau composant Mailer de Symfony ici
            $email = (new Email())
                ->from('support@sortir.com')
                ->to($email)
                ->subject('Mot de passe oublié ?')
                ->html('<a href="'.$emailUrl.'">Cliquez ici pour générer un nouveau mot de passe !</a>');

            //envoie le mail (envoi désactivé pour le moement)
            $mailer->send($email);

            //affiche un autre fichier twig (pas super recommandé mais bon)
            return $this->render('security/forgot_step_2.html.twig', ['url_sent' => $emailUrl]);
        }

        return $this->render('security/forgot_step_1.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * URL où revient l'utilisateur après avoir cliqué sur le lien dans son mail
     * On y vérifie que le token (présent dans l'URL) est bon
     * On identifie le token par un "selector" impossible à deviner (plus sécuritaire que l'id ou l'email)
     *
     * @Route("/mot-de-passe-oublie/nouveau/{selector}/{token}", name="security_forgot_password_step_3")
     */
    public function forgotPasswordStep3(string $selector, string $token, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $em = $this->getDoctrine()->getManager();

        //cherche la token reçue dans l'URL dans la bdd, en fonction du selector
        $tokenRepo = $this->getDoctrine()->getRepository(ForgotPasswordToken::class);
        $foundToken = $tokenRepo->findOneBy(['selector' => $selector], ['dateCreated' => 'DESC']);

        //si on ne l'a pas trouvée, erreur
        if (!$foundToken){
            throw $this->createAccessDeniedException("Cette URL n'est pas valide 1!");
        }

        //vérifie que le token dans l'URL correspond au token hashé dans la bdd
        $hashedToken = $foundToken->getToken();
        if (!password_verify($token, $hashedToken)){
            //supprime le token par sécurité
            $em->remove($foundToken);
            $em->flush();

            throw $this->createAccessDeniedException("Cette URL n'est pas valide 2!");
        }

        //si on se rend ici, c'est que c'est tout bon
        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            //retrouve l'utilisateur à partir du token
            $user = $foundToken->getUser();

            //change son mdp (hashé)
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $em->persist($user);
            $em->flush();

            //supprime le token par sécurité
            $em->remove($foundToken);
            $em->flush();

            $this->addFlash('success', 'Votre changement de mot de passe a bien été pris en compte !');
            //on redirige vers la liste des sorties. Il n'est pas connecté, donc sera redirigé vers le login, puis vers les sorties !
            //sinon, il sera redirigé ici même après le login, ce qui fera une erreur
            return $this->redirectToRoute('event_list');
        }

        return $this->render('security/forgot_step_3.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
