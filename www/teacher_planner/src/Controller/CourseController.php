<?php

namespace App\Controller;
use App\Entity\Course;
use App\Form\CourseType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

class CourseController extends AbstractController
{   
    /**
    * @Route("/courses/add", name="app_course")
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
            
            return $this->redirectToRoute('app_course', ['id' => $course->getId()]);
            //return new Response("Assignatura creada amb ID: " .$course->getId());
            //return $this->redirectToRoute('app_course');
            
        }

        return $this->render('course/course.html.twig', [
            'controller_name' => 'courseController',
            'courseForm' => $form->createView()
        ]);
    }

    /**
    * @Route("/courses", name="app_getCourses")
    */
    public function showCourse(){
        $entityManager = $this->getDoctrine()->getManager();
        $allCourses = $entityManager->getRepository(Course::class)->findAll();
        return $this->render('course/allCourses.html.twig', [
            'allCourses' => $allCourses,
        ]);  
    }

    /**
    * @Route("/courses/delete/{id}", name="app_deleteCourses")
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
        
        return $this->redirectToRoute('app_course', ['id' => $course->getId()]);
    }

    /**
    * @Route("/courses/edit/{id}", name="app_editCourses")
    */

    public function updateCourse(Request $request, $id){
        $entityManager = $this->getDoctrine()->getManager();
        $course = $entityManager->getRepository(Course::class)->find($id);
        if (!$course) {
            throw $this->createNotFoundException(
                'No existeix cap curs amb id '.$id
            );
        }

        $course->setName($request['name']->getData());
        $course->setCicle($request['cicle']->getData());
  
        return $this->redirectToRoute('app_getCourses', [
            'id' => $course->getId()
        ]);
    }

    /**
    * @Route("/courses/subjects/{id}", name="app_subjectByCourse")
    */
    public function showSubjectsByCourse($id){
        $subjects = $this->getDoctrine()
        ->getRepository(Course::class)
        ->findBySubjectsByCourseId($id);
    }

}