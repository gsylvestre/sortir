<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Location;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Ajout de lieu
 *
 * Class LocationType
 * @package App\Form
 */
class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('zip', null, ['label' => 'Code postal'])
            ->add('city', EntityType::class, [
                'label' => 'Ville',
                'class' => City::class,
                'choice_label' => 'name',
                //permet de définir comment sont chargées les données depuis la bdd
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC')
                        ->andWhere('c.department = 44');
                },
            ])
            ->add('street', null, ['label' => 'Adresse'])
            ->add('name', null, ['label' => 'Donner un nom à ce lieu plz'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
