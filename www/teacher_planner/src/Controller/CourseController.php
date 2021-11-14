<?php

declare(strict_types=1);

namespace App\Controller;
use App\Entity\Subject;
use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Repository\SubjectRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/courses")
 */

class CourseController extends AbstractController
{   
    public function __construct (CourseRepository $courseRepository, EntityManagerInterface $entityManager)
    {
        $this->courseRepository = $courseRepository;
        $this->entityManager = $entityManager;
    }


    /**
    * @Route("/add", name="app_addCourse")
    */
    public function addCourse(Request $request){
        $course = new course();
        $form = $this->createForm(courseType::class, $course);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $course->setName($form['name']->getData());
            $course->setCicle($form['cicle']->getData());
            
            $entityManager = $this->getDoctrine()->getManager();

            // tell Doctrine you want to (eventually) save the course
            $entityManager->persist($course);

            // actually executes the action in the ddbb (in this case insert course)
            $entityManager->flush();
            
            return $this->redirectToRoute('app_getCourses');
        }

        return $this->render('course/add.html.twig', [
            'controller_name' => 'courseController',
            'form' => $form->createView()
        ]);
    }

    /**
    * @Route("/", name="app_getCourses")
    */
    public function showCourse(){
        $entityManager = $this->getDoctrine()->getManager();
        $allCourses = $entityManager->getRepository(Course::class)->findAll();
        return $this->render('course/allCourses.html.twig', [
            'allCourses' => $allCourses,
        ]);
    }

    /**
    * @Route("/delete/{id}", name="app_deleteCourses")
    */
    public function deleteCourse($id){
        $entityManager = $this->getDoctrine()->getManager();
     
        $course = $entityManager->getRepository(Course::class)->find($id);

        if (!$course) {
            throw $this->createNotFoundException(
                'No existeix cap curs amb id '.$id
            );
        }
        $entityManager->remove($course);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_getCourses');
    }

    /**
    * @Route("/edit/{id}", name="app_editCourses")
    */

    public function updateCourse(Course $course, Request $request): Response{
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_getCourses');
        }

        return $this->render('course/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{id}", name="app_detailCourses")
     */
    public function detail(Course $course): Response
    {
        return $this->render('course/detail.html.twig',
            ['course' => $course]);
    }


    /**
    * @Route("/{id}/showsubjects", name="app_subjectByCourse")
    */
    public function showSubjectsByCourse($id){
        $subjects = $this->getDoctrine()
        ->getRepository(Subject::class)
        ->findBySubjectsByCourseId($id);
        return $this->render('course/showSubjectsByCourse.html.twig', [
            'subjects' => $subjects,
        ]);
    }

}