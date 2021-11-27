<?php

namespace App\Form;
use App\Entity\User;
use App\Entity\Restriction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;

class RestrictionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('day')
            ->add('hora_inici')
            ->add('hora_fi')
            ->add('teacher', EntityType::class, array(
                'class' => User::class,
                'label' => 'Name',
                'choice_label' => function(User $teacher){
                    return $teacher->getName();
                }
            ))
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Restriction::class,
        ]);
    }
}
