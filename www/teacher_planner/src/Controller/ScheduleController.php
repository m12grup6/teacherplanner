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


define('DAYS', array('monday', 'tuesday', 'wednesday', 'thursday', 'friday'));
define('TIMETABLE', array('08:00:00-09:00:00', '09:00:00-10:00:00', '10:00:00-11:00:00', '11:00:00-12:00:00', '12:00:00-13:00:00', '14:00:00-15:00:00'));


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
     * @Route("/generateSchedule", name="app_schedule")
    */

    public function generateSchedule(){
        $this->deleteSchedules();
        $schedule= $this->generateProposedSchedule();

        return $this->render('course/showSchedule.html.twig', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * Mètode que genera una proposta d'horari tenint en compte les restriccions dels profes i els espais lliures de cada curs. 
     * @return Array $proposal retorna una array d'horaris, entenent com a horari el dia-hora en que una assignatura es donara per un professor.
    */

    public function generateProposedSchedule(): array{
        $entityManager = $this->getDoctrine()->getManager();
        $courses = $entityManager->getRepository(Course::class)->findAll();
        $proposal = [];
        $k=0;
        $scheduleAvailability = array (
            array ('Dilluns','Dimarts','Dimecres','Dijous','Divendres'),
            array ('8-9','9-10','10-11','11-12','12-13','13-14'),
        );

        foreach ($courses as $c){
            $scheduleAvailability[0][0] = true;
            $scheduleAvailability[0][1] = true;
            $scheduleAvailability[0][2] = true;
            $scheduleAvailability[0][3] = true;
            $scheduleAvailability[0][4] = true;
            $scheduleAvailability[0][5] = true;
            $scheduleAvailability[1][0] = true;
            $scheduleAvailability[1][1] = true;
            $scheduleAvailability[1][2] = true;
            $scheduleAvailability[1][3] = true;
            $scheduleAvailability[1][4] = true;
            $scheduleAvailability[1][5] = true;
            $scheduleAvailability[2][0] = true;
            $scheduleAvailability[2][1] = true;
            $scheduleAvailability[2][2] = true;
            $scheduleAvailability[2][3] = true;
            $scheduleAvailability[2][4] = true;
            $scheduleAvailability[2][5] = true;
            $scheduleAvailability[3][0] = true;
            $scheduleAvailability[3][1] = true;
            $scheduleAvailability[3][2] = true;
            $scheduleAvailability[3][3] = true;
            $scheduleAvailability[3][4] = true;
            $scheduleAvailability[3][5] = true;
            $scheduleAvailability[4][0] = true;
            $scheduleAvailability[4][1] = true;
            $scheduleAvailability[4][2] = true;
            $scheduleAvailability[4][3] = true;
            $scheduleAvailability[4][4] = true;
            $scheduleAvailability[4][5] = true;
            
            $course_id = $c->getId();
            $courseSubjects = $this->getDoctrine()
                ->getRepository(Subject::class)
                ->findBySubjectsByCourseId($course_id);

            $arrLength = count($courseSubjects);

            for ($i = 0; $i < $arrLength; $i++) {
                $subject_id = $courseSubjects[$i]['id'];
                $subject_hours_week = $courseSubjects[$i]['hours_week'];
                $subject = $entityManager->getRepository(Subject::class)->find($subject_id);
                
                $teacher_id = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->findTeacherBySubjectId($subject_id);

                $teacher =  $entityManager->getRepository(User::class)->findTeacherById($teacher_id);
                
                $restrictions = $teacher->getTeacherConstraints();
                               
                for ($j = 0; $j < $subject_hours_week; $j++) {
                    $dayRandom=array_rand(DAYS,1);
                    $hourRandom=array_rand(TIMETABLE,1);

                    $day = DAYS[$dayRandom];
                    $hour = TIMETABLE[$hourRandom];

                    $dayHours = explode("-", $hour);
                  
                    $franja = array(
                        'dia' => $day,
                        'hora_inici' => $dayHours[0],
                        'hora_fi' => $dayHours[1],
                    );

                    while ($scheduleAvailability[$dayRandom][$hourRandom] === false
                            && $this->checkTeacherConstraints($franja, $restrictions) === true) {  // && TO DO agregar check restricciones profes
                        $dayRandom=array_rand(DAYS,1);
                        $hourRandom=array_rand(TIMETABLE,1);
                    }

                    $dayAvailable = DAYS[$dayRandom];
                    $hourAvailable = TIMETABLE[$hourRandom];

                    $schedule = new Schedule();
                    $schedule->setDay($dayAvailable);
                    $schedule->setHour($hourAvailable);
                    $schedule->setTeacher($teacher);
                    $schedule->setSubject($subject);
                    $scheduleAvailability[$dayRandom][$hourRandom] = false;

                    $proposal[$k] = $schedule;
                    $k++;

                    $entityManager->persist($schedule);
                    $entityManager->flush();
                }
            }
        }
        return $proposal;
    }
    
    /**
     * Mètode que Esborra els schedules actuals a la base de dades.
    */
    
    public function deleteSchedules() {
        $entityManager = $this->getDoctrine()->getManager();
        $schedules = $entityManager->getRepository(Schedule::class)->findAll();
        foreach ($schedules as $scheduleObject) {
            $entityManager->remove($scheduleObject);
        }
        $entityManager->flush();
    }

    /**
     * Mètode que Esborra els schedules actuals a la base de dades.
    */
    public function deleteSchedules()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $schedules = $entityManager->getRepository(Schedule::class)->findAll();
        foreach ($schedules as $scheduleObject) {
            $entityManager->remove($scheduleObject);
        }
        $entityManager->flush();
    }

    /**
     * Mètode que retorna true si el dia i hora proposats per una assignatura i un professor és dins d'una restricció horària del professor que donarà l'assignatura.
     * @param Array $franja dia i hora proposats per una assignatura
     * @param Array $constraints indisponibilitat del professor
     * @return Boolean false si no hi ha incompatibilitat; true si es solapa el dia i hora amb les restriccions del professor. 
    */
   
    public function checkTeacherConstraints(array $franja, array $constraints)
    {   
        if (!empty ($constraints)){
            $constraintsOfTeacher = array();
            foreach (DAYS as $day){
                $constraintsOfTeacher[$day][0] = array(
                    'hora_inici' => null,
                    'hora_fi' => null, 
                ); 
            }
            
            foreach($constraints as $constraint) {
                $constraintsOfTeacher[$constraint['dia']][] = array('hora_inici' => $constraint['hora_inici'], 'hora_fi' => $constraint['hora_fi']);
            }

            foreach ($constraintsOfTeacher[$franja['dia']] as $constraintsDelDia) {
                if (new \DateTime(date('Y-m-d') . ' ' . $constraintsDelDia['hora_inici']) >= new \DateTime(date('Y-m-d') . ' ' . $franja['hora_inici']) && new \DateTime(date('Y-m-d') . ' ' . $constraintsDelDia['hora_fi']) <= new \DateTime(date('Y-m-d') . ' ' . $franja['hora_fi'])) {
                    return true;
                }
            }
            return false;
            
           
        } else {
            return false;
        }
    }
}
