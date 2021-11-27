<?php

namespace App\Form;

use App\Entity\Subject;
use App\Entity\Course;
use App\Repository\CourseRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class SubjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {   
        $selectedCourseId = $options['selected_course_id'];

        $builder
            ->add('name')
            ->add('course', EntityType::class, array(
                'class'   => Course::class,
                'query_builder' => function(CourseRepository $cr) use ($selectedCourseId) {
                    if ($selectedCourseId){
                        return $cr->createQueryBuilder('c')->where('c.id='.$selectedCourseId);
                    }
                    return $cr->createQueryBuilder('c')->getFirstResult();
                },
                'choice_label' => function(Course $course) {
                    return $course->getName().' - '.$course->getCicle();
                }
            ))
            ->add('hours_week')
            ->add('submit', SubmitType::class)
        ;
    }

  
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subject::class,
            'selected_course_id' => null,
        ]);
    }
}
