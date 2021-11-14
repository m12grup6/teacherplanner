<?php

declare(strict_types=1);

namespace App\Controller;
use App\Entity\Subject;
use App\Form\SubjectType;
use App\Repository\SubjectRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/subjects")
 */

class SubjectController extends AbstractController
{   
    public function __construct (SubjectRepository $subjectRepository, EntityManagerInterface $entityManager)
    {
        $this->subjectRepository = $subjectRepository;
        $this->entityManager = $entityManager;
    }

    /**
    * @Route("/add", name="app_addSubject")
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
            
            return $this->redirectToRoute('app_getSubjects');            
        }

        return $this->render('subject/add.html.twig', [
            'controller_name' => 'SubjectController',
            'form' => $form->createView()
        ]);
    }

    /**
    * @Route("/", name="app_getSubjects")
    */
    public function showSubject(Request $request){
        $entityManager = $this->getDoctrine()->getManager();
        $allSubjects = $entityManager->getRepository(Subject::class)->findAll();
        return $this->render('subject/allSubjects.html.twig', [
            'allSubjects' => $allSubjects,
        ]);  
    }

    /**
    * @Route("/delete/{id}", name="app_deleteSubjects")
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
        
        return $this->redirectToRoute('app_getSubjects');
    }

     /**
    * @Route("/edit/{id}", name="app_updateSubjects")
    */

    public function updateSubject(Subject $subject, Request $request): Response{
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_getSubjects');
        }

        return $this->render('subject/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{id}", name="app_detailSubject")
     */
    public function detail(Subject $subject): Response
    {
        return $this->render('subject/detail.html.twig',
            ['subject' => $subject]);
    }

}