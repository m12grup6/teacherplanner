<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Subject;
use App\Repository\SubjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;


class TeacherType extends AbstractType
{
    private $subjectRepository;

    public function __construct(SubjectRepository $subjectRepository)
    {
        $this->subjectRepository = $subjectRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('name')
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                    'Coordinador' => 'ROLE_PARTNER',
                    'Profesor' => 'ROLE_ADMIN',
                ],
            ])
            ->add('subjects', EntityType::class, [
                'class' => Subject::class,
                'required' => true,
                'multiple' => true,
                'expanded' => false,
                'choices' => $this->subjectRepository->findAll(),
                'choice_value' => function (?Subject $subject) {
                    return $subject ? $subject->getId() : '';
                },
                'choice_label' => function (?Subject $subject) {
                    return $subject ? $subject->getName() . ' (' . $subject->getCourse()->getName() . ' de ' . $subject->getCourse()->getCicle() . ')' : '';
                },
            ])
            ->add('submit', SubmitType::class);

        // Transforma
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transforma l'array a string
                    return count($rolesArray) ? $rolesArray[0] : null;
                },
                function ($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
