<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Subject;
use App\Entity\Schedule;
use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Repository\SubjectRepository;
use App\Repository\ScheduleRepository;
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
    public function __construct(CourseRepository $courseRepository, EntityManagerInterface $entityManager)
    {
        $this->courseRepository = $courseRepository;
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/add", name="app_addCourse")
     * Mètode per afegir un curs i grabar-lo a la BBDD.
     * @param request $request informació del formulari per tal d'afegir el curs.
     */
    public function addCourse(Request $request)
    {
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
     * Mètode per llistar tots els cursos donats d'alta.
     */
    public function showCourses()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $allCourses = $entityManager->getRepository(Course::class)->findAll();
        return $this->render('course/allCourses.html.twig', [
            'allCourses' => $allCourses,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="app_deleteCourses")
     * Mètode per borrar el curs passat per paràmetre
     * @param Integer $id id del curs a borrar.
     */
// @Jaume: Trobo excepcions al intentar executar aquest mètode, retiro el botó fins revisar
    public function deleteCourse($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $course = $entityManager->getRepository(Course::class)->find($id);

        if (!$course) {
            throw $this->createNotFoundException(
                'No existeix cap curs amb id ' . $id
            );
        }
        $entityManager->remove($course);
        $entityManager->flush();

        return $this->redirectToRoute('app_getCourses');
    }

    /**
     * @Route("/edit/{id}", name="app_editCourses")
     * Mètode per editar el curs per paràmetre
     * @param Integer $id id del curs a editar.
     */

    public function updateCourse(Course $course, Request $request): Response
    {
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
     * Mètode per mostrar el detall d'un curs. Mostra el valor dels seus atributs.
     * @param Course $course objecte subject amb les dades de l'assignatura.
     */
    public function detail(Course $course): Response
    {
        return $this->render(
            'course/detail.html.twig',
            ['course' => $course]
        );
    }


    /**
     * @Route("/{id}/showsubjects", name="app_subjectByCourse")
     * Mètode que retorna les assignatures associades a un curs. 
     * @param Integer $id id del curs del qual es vol obtenir el llistat d'assignatures que pengen d'ell.
     */
    public function showSubjectsByCourse($id)
    {
        $subjects = $this->getDoctrine()
            ->getRepository(Subject::class)
            ->findBySubjectsByCourseId($id);
        return $this->render('course/showSubjectsByCourse.html.twig', [
            'subjects' => $subjects,
        ]);
    }

    /**
     * @Route("/{id}/showschedule", name="app_scheduleByCourse")
     * Mètode que retorna l'horari associat a un curs. 
     * @param Integer $id id del curs del qual es vol obtenir l'horari.
     */
    public function showScheduleByCourse($id)
    {
        $schedule = array();
        $scheduleRegisters = $this->getDoctrine()
            ->getRepository(Schedule::class)
            ->findByScheduleByCourseId($id);

        foreach ($scheduleRegisters as $register) {
            $schedule[$register->getDay()][$register->getHour()] = array(
                'teacher' => $register->getTeacher()->getName(),
                'subject' => $register->getSubject()->getName()
            );
        }

        return $this->render('course/showSchedule.html.twig', [
            'schedule' => $schedule,
        ]);
    }
}
