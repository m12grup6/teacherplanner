<?php

namespace App\Controller;
use App\Entity\User;
use App\Form\TeacherType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TeacherController extends AbstractController
{
    /**
    * @Route("/teacher", name="teacher")
    */
    public function index(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(TeacherType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $user->setIsActive(true);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'El professor ha sigut registrat');
            return $this->redirectToRoute('teacher');
        }
        return $this->render('teacher/index.html.twig', [
            'controller_name' => 'Hola profes',
            'formulario'=>$form->createView()
        ]);
    }

    /**
    * @Route("/showTeachers", name="app_getTeachers")
    */
    public function showTeacher(){
        $entityManager = $this->getDoctrine()->getManager();
        $allTeachers = $entityManager->getRepository(User::class)->findAll();
        return $this->render('teacher/allTeachers.html.twig', [
            'allTeachers' => $allTeachers,
        ]);
    }

    /**
    * @Route("/deleteTeacher/{id}", name="app_deleteTeachers")
    */
    public function deleteTeacher($id){
        $entityManager = $this->getDoctrine()->getManager();
     
        $teacher = $entityManager->getRepository(Teacher::class)->find($id);

        if (!$teacher) {
            throw $this->createNotFoundException(
                'No existeix cap professor amb id '.$id
            );
        }
        $entityManager->remove($teacher);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_getTeachers');
    }

    /**
    * @Route("/editTeacher/{id}", name="app_editCourses")
    */

    public function updateTeacher(Teacher $teacher, Request $request): Response{
        $form = $this->createForm(TeacherType::class, $teacher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_getTeachers');
        }

        return $this->render('teacher/add.html.twig', ['form' => $form->createView()]);
    }
}
