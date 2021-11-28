<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class TeacherConstraintType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dia', ChoiceType::class, [
                'choices'  => [
                    'Dilluns' => 'monday',
                    'Dimarts' => 'tuesday',
                    'Dimecres' => 'wednesday',
                    'Dijous' => 'thursday',
                    'Divendres' => 'friday',
                ],
                'mapped' => false,
            ])
            ->add('hora_inici', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'mapped' => false,
            ])
            ->add('hora_fi', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
