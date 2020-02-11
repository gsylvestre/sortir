<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ['label' => 'Titre de la sortie'])
            ->add('infos', null, ['label' => "Plus d'infos"])
            ->add('startDate', null, ['label' => 'Débute le...', 'date_widget' => 'single_text'])
            ->add('duration', IntegerType::class, ['label' => 'Durée, en heures'])
            ->add('registrationLimitDate', null, ['label' => "Date limite d'inscription", 'date_widget' => 'single_text'])
            ->add('maxRegistrations', IntegerType::class, ['label' => 'Nombre max de participants'])
            ->add('submit', SubmitType::class, ['label' => 'Créer'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
