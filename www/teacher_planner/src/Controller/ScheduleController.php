<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Course;
use App\Entity\Subject;
use App\Entity\Schedule;
use App\Form\CourseType;
use App\Repository\UserRepository;
use App\Repository\CourseRepository;
use App\Repository\SubjectRepository;
use App\Repository\ScheduleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


define ('DAYS', array ('Dilluns','Dimarts','Dimecres','Dijous','Divendres'));
define ('TIMETABLE', array ('8-9','9-10','10-11','11-12','12-13','13-14'));


/**
 * @Route("/schedule")
 */
class ScheduleController extends AbstractController
{
    public function __construct(SubjectRepository $subjectRepository, UserRepository $userRepository, CourseRepository $courseRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->courseRepository = $courseRepository;
        $this->subjectRepository = $subjectRepository;
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/build", name="app_buildSchedule")
     */
    public function buildSchedule(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $allCourses = $entityManager->getRepository(Course::class)->findAll();
        $allSubjects = $entityManager->getRepository(Subject::class)->findAll();
        $allTeachers = $entityManager->getRepository(User::class)->findByRoleField('ROLE_USER');
        return $this->redirectToRoute('app_getCourses');
    }


    /**
     * @Route("/generateSchedule", name="app_schedule")
    */

    function generateSchedule(){
        $proposedSchedule= generateProposedSchedule();
        $finalSchedule= validateProposedSchedule($proposedSchedule);
    
        while (is_null($finalSchedule)){
            $proposedSchedule = createProposedSchedule();
            $finalSchedule= validateProposedSchedule($proposedSchedule);
        }
       
        return $finalSchedule;
    }

    
    public function generateProposedSchedule(){
        $entityManager = $this->getDoctrine()->getManager();
        $courses = $entityManager->getRepository(Course::class)->findAll();
        $proposal = array();
        $k=0;

        $teacher1 = $entityManager->getRepository(User::class)->find(1);
        $teacher2 = $entityManager->getRepository(User::class)->find(2);
        $teacher3 = $entityManager->getRepository(User::class)->find(3);
        $teacher4 = $entityManager->getRepository(User::class)->find(4);

        foreach ($courses as $c){
            $course_id = $c->getId();
            $courseSubjects = $this->getDoctrine()
            ->getRepository(Subject::class)
            ->findBySubjectsByCourseId($course_id);
            
            $arrLength = count($courseSubjects);
            for ($i = 0; $i < $arrLength; $i++) {
                $subject = $entityManager->getRepository(Subject::class)->find($i);
                $subject_id = $courseSubjects[$i]['id'];
                $subject_hours_week = $courseSubjects[$i]['hours_week'];

                //Conseguir profesores por subject id --> TO DO --> crear un metodo
                $teacher = "";
                switch ($subject_id) {
                    case 4:
                        $teacher = $teacher1;
                        break;
                    case 7:
                        $teacher = $teacher2;
                        break;
                    case 8:
                        $teacher = $teacher3;
                        break;
                    case 10:
                        $teacher = $teacher4;
                        break;
                }
                return $teacher;
                
                for ($j = 0; $j < $subject_hours_week; $j++) {
                    $day=array_rand(DAYS,1);
                    $hour=array_rand(TIMETABLE,1);
                    
                    $schedule = new Schedule();
                    $schedule->setDay($day);
                    $schedule->setHour($hour);
                    $schedule->setTeacher($teacher);
                    $schedule->setSubject($subject);

                    echo "dia al azar = $day   ";
                    echo "hora al azar = $hour   ";
                    echo "teacher = $teacher   ";
                    echo "subject = $subject   ";
                    
                    $proposal[$k] = $schedule;
                    $k++;
                }
            }
        }
        
        echo "Propuesta generada     ";
        var_dump($proposal);
        //return $proposal; 
        
    }


    public function validateProposedSchedule($proposal){
        $arrLength = count($proposal);

        $teacher1 = $entityManager->getRepository(User::class)->find(1);
        $teacher2 = $entityManager->getRepository(User::class)->find(2);
        $teacher3 = $entityManager->getRepository(User::class)->find(3);
        $teacher4 = $entityManager->getRepository(User::class)->find(4);

        for ($i = 0; $i < $arrLength; $i++) {
            $dayAssigned = $propuesta[$i]['day'];
            $hourAssigned = $propuesta[$i]['hour'];
            $teacherAssigned = $proposal[$i]['teacher'];
            
            echo "Posicion array = $i    ";
            echo "Teacher = $teacherAssigned    ";
            echo "Dia = $dayAssigned   ";
            echo "Hora = $hourAssigned   ";

            //obtener restricciones del teacher.
          
            if ($teacherAssigned == $teacher1) {
                $restrictionDay = 'Dimecres';
                $restrictionHour = '9-10';
            } else {
                $restrictionDay = 'Dijous';
                $restrictionHour = '9-10';
            }
            
            //Comparar si las restricciones del teacher son incompatibles con el horario propuesto
            if ($restrictionDay == $dayAssigned && $restrictionHour == $hourAssigned) {
                echo "Restricción = $restrictionDay   ";
                echo "Dia = $dayAssigned   ";
                echo "Restricción = $restrictionHour   ";
                echo "Dia = $hourAssigned   ";

                echo "Incompatibilidad. Crear otro horario     ";
                unset($proposal);
                break;
            }
        }
        return $proposal; 
    }   
}
