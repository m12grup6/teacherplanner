<?php

namespace App\Controller;
use App\Entity\Subject;
use App\Form\SubjectType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

class SubjectController extends AbstractController
{   
    /**
    * @Route("/add/subject", name="app_subject")
    */
    public function addSubject(Request $request){
        $subject = new Subject();
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $subject->setName($form['name']->getData());
            $subject->setCourseId($form['course_id']->getData());
            $subject->setHoursWeek($form['hours_week']->getData());
            
            $entityManager = $this->getDoctrine()->getManager();

            // tell Doctrine you want to (eventually) save the subject
            $entityManager->persist($subject);

            // actually executes the action in the ddbb (in this case insert subject)
            $entityManager->flush();
            
            return $this->redirectToRoute('app_subject', ['id' => $subject->getId()]);
            //return new Response("Assignatura creada amb ID: " .$subject->getId());
            //return $this->redirectToRoute('app_subject');
            
        }

        return $this->render('subject/subject.html.twig', [
            'controller_name' => 'SubjectController',
            'subjectForm' => $form->createView()
        ]);
    }

    /**
    * @Route("/get/subjects", name="app_getSubjects")
    */
    public function showSubject(Request $request){
        $entityManager = $this->getDoctrine()->getManager();
        $allSubjects = $entityManager->getRepository(Subject::class)->findAll();
        return $this->render('subject/allSubjects.html.twig', [
            'allSubjects' => $allSubjects,
        ]);  
    }

    /**
    * @Route("/delete/subject/{id}", name="app_deleteSubjects")
    */
    public function deleteSubject($id){
        $entityManager = $this->getDoctrine()->getManager();
     
        $subject = $entityManager->getRepository(Subject::class)->find($id);

        if (!$subject) {
            throw $this->createNotFoundException(
                'No existeix cap assignatura amb id '.$id
            );
        }
        $entityManager->remove($subject);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_subject', ['id' => $subject->getId()]);
    }


    /*public function updateSubject($id){
        $entityManager = $this->getDoctrine()->getManager();
        $subject = $entityManager->getRepository(Subject::class)->findId($id);
        if (!subject) {
            throw $this->createNotFoundException("L'assignatura amb ID " .$id " no existeix");
        }

        $subject->setName($form['name']->getData());
        $subject->setCourseId($form['course_id']->getData());
        $subject->setHoursWeek($form['hours_week']->getData());

    }*/



}