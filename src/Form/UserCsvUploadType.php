<?php

namespace App\Form;

use App\Entity\SchoolSite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserCsvUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('csv', FileType::class, ['label' => 'Fichier CSV'])
            ->add('school_site', EntityType::class, [
                'class' => SchoolSite::class,
                'choice_label' => 'name',
                'label' => 'Campus de rattachement',
            ])
            ->add('ok', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
