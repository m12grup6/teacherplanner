<?php

namespace App\Controller;
use App\Entity\User;
use App\Form\TeacherType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;


/**
* @Route("/teachers")
*/

class TeacherController extends AbstractController
{

    public function __construct (UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }


    /**
    * @Route("/add", name="app_addTeacher")
    * Mètode per afegir un teacher i grabar-lo a la BBDD.
    * @param request $request informació del formulari per tal d'afegir el teacher.
    */
    public function addTeacher(Request $request){
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

            return $this->redirectToRoute('app_getTeachers');
        }

        return $this->render('teacher/add.html.twig', [
            'controller_name' => 'TeacherController',
            'form'=>$form->createView()
        ]);
    }

    /**
    * @Route("/", name="app_getTeachers")
    * Mètode per llistar tots els teachers donats d'alta.
    */
    public function showTeachers(){
        $entityManager = $this->getDoctrine()->getManager();
        $allTeachers = $entityManager->getRepository(User::class)->findAll();
        return $this->render('teacher/allTeachers.html.twig', [
            'allTeachers' => $allTeachers,
        ]);
    }

    /**
    * @Route("/delete/{id}", name="app_deleteTeachers")
    * Mètode per esborrar el teacher passat per paràmetre
    * @param Integer $id id del teacher a esborrar.
    */
    public function deleteTeacher($id){
        $entityManager = $this->getDoctrine()->getManager();
     
        $teacher = $entityManager->getRepository(User::class)->find($id);

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
    * @Route("/edit/{id}", name="app_updateTeachers")
    * Mètode per editar el teacher passat per paràmetre
    * @param Integer $id id del teacher a editar.
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


    /**
     * @Route("/{id}", name="app_detailTeacher")
     * Mètode per mostrar el detall d'un teacher. Mostra el valor dels seus atributs.
     * @param Subject $subject objecte subject amb les dades del teacher.
     */
    public function detailTeacher(Teacher $teacher): Response
    {
        return $this->render('teacher/detail.html.twig',
            ['teacher' => $teacher]);
    }
}
