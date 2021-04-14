<?php

namespace App\Form;

use App\Entity\SchoolSite;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire de création de compte utilisateur, réservé aux admins
 *
 * Class RegistrationFormType
 * @package App\Form
 */
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('firstname', null, ['label' => 'Prénom'])
            ->add('lastname', null, ['label' => 'Nom'])
            ->add('phone', null, ['label' => 'Téléphone'])
            ->add('school', EntityType::class, [
                'label' => 'Votre école de rattachement',
                'class' => SchoolSite::class,
                'choice_label' => 'name'
            ])
            //ce champ est mapped à false, car la propriété n'existe pas vraiment dans l'entité
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                //la validation est faite directement ici, pourquoi pas
                //sinon, il fallait créer une propriété "bidon" dans l'entité pour utiliser les Assert()
                //downside : on a une grosse duplication de la validation dans le form de changement de mdp
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe devrait avoir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('isActive', null, ['label' => 'Actif ?', 'required' => false])
            ->add('isAdmin', CheckboxType::class, ['label' =>'Administrateur ?', 'required' => false])

            ->add('submit', SubmitType::class, ['label' => 'Go'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
