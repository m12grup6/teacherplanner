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

ini_set('max_execution_time', '1500'); //300 seconds = 5 minutes 

define('DAYS', array('monday', 'tuesday', 'wednesday', 'thursday', 'friday'));
define('TIMETABLE', array('08:00:00-09:00:00', '09:00:00-10:00:00', '10:00:00-11:00:00', '11:00:00-12:00:00', '12:00:00-13:00:00', '13:00:00-14:00:00'));


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
     * @Route("/", name="app_showSchedule")
     * Mètode que retorna l'horari per mostrar-ho al front.
    */
    public function showSchedule()
    {
        $schedules = $this->entityManager->getRepository(Schedule::class)->findAll();
        $orderedSchedules = [];
        $primaria = [];
        $secundaria = [];

        foreach ($schedules as $key => $schedule) {
            switch ($schedule->getDay()) {
                case 'monday':
                    $schedules[$key]->setDay('Dilluns');
                    break;
                case 'tuesday':
                    $schedules[$key]->setDay('Dimarts');
                    break;
                case 'wednesday':
                    $schedules[$key]->setDay('Dimecres');
                    break;
                case 'thursday':
                    $schedules[$key]->setDay('Dijous');
                    break;
                case 'friday':
                    $schedules[$key]->setDay('Divendres');
                    break;
            }

            $hours = array();
            $franja = explode('-', $schedule->getHour());
            foreach ($franja as $hora) {
                $timeParts = explode(':', $hora);
                $hours[] = $timeParts[0];
            }
            $schedules[$key]->setHour(join('-', $hours));

            // Agrupa per cicle i curs
            $curs = $schedule->getSubject()->getCourse()->getName();
            $cicle = $schedule->getSubject()->getCourse()->getCicle();
            if ($cicle == 'Primaria') {
                $primaria['Primària'][$curs][] = $schedule;
            } else if ($cicle == 'Secundaria') {
                $secundaria['Secundària'][$curs][] = $schedule;
            }
        }

        $orderedSchedules = array_merge($primaria, $secundaria);
        return $this->render('course/showSchedule.html.twig', [
            'schedule' => $orderedSchedules,
            'raw' => $schedules,
        ]);
    }

    /**
     * @Route("/generate", name="app_generateSchedule")
     * Mètode que borra l'horari anterior i crida al mètode per generar un nou horari. Redirecciona a la pàgina que mostra l'horari.
    */
    public function generateSchedule()
    {
        $this->deleteSchedules();
        $this->generateProposedSchedule();

        return $this->redirectToRoute('app_showSchedule');
    }

    /**
     * Mètode que genera una proposta d'horari tenint en compte les restriccions dels profes i els espais lliures de cada curs. 
     * @return Array $proposal retorna una array d'horaris, entenent com a horari el dia-hora en que una assignatura es donara per un professor.
     */

    public function generateProposedSchedule(): array
    {
        $entityManager = $this->getDoctrine()->getManager();
        $courses = $entityManager->getRepository(Course::class)->findAll();
        $proposal = [];
        $k = 0;
        $teacherScheduleAvailability = $this->createTeacherScheduleAvailability();
        
        foreach ($courses as $c) {            
            $scheduleAvailability = array(
                array('day' => 'monday', 'hour' => '08:00:00-09:00:00'),
                array('day' => 'monday', 'hour' => '09:00:00-10:00:00'),
                array('day' => 'monday', 'hour' => '10:00:00-11:00:00'),
                array('day' => 'monday', 'hour' => '11:00:00-12:00:00'),
                array('day' => 'monday', 'hour' => '12:00:00-13:00:00'),
                array('day' => 'monday', 'hour' => '13:00:00-14:00:00'),
                array('day' => 'tuesday', 'hour' => '08:00:00-09:00:00'),
                array('day' => 'tuesday', 'hour' => '09:00:00-10:00:00'),
                array('day' => 'tuesday', 'hour' => '10:00:00-11:00:00'),
                array('day' => 'tuesday', 'hour' => '11:00:00-12:00:00'),
                array('day' => 'tuesday', 'hour' => '12:00:00-13:00:00'),
                array('day' => 'tuesday', 'hour' => '13:00:00-14:00:00'),
                array('day' => 'wednesday', 'hour' => '08:00:00-09:00:00'),
                array('day' => 'wednesday', 'hour' => '09:00:00-10:00:00'),
                array('day' => 'wednesday', 'hour' => '10:00:00-11:00:00'),
                array('day' => 'wednesday', 'hour' => '11:00:00-12:00:00'),
                array('day' => 'wednesday', 'hour' => '12:00:00-13:00:00'),
                array('day' => 'wednesday', 'hour' => '13:00:00-14:00:00'),
                array('day' => 'thursday', 'hour' => '08:00:00-09:00:00'),
                array('day' => 'thursday', 'hour' => '09:00:00-10:00:00'),
                array('day' => 'thursday', 'hour' => '10:00:00-11:00:00'),
                array('day' => 'thursday', 'hour' => '11:00:00-12:00:00'),
                array('day' => 'thursday', 'hour' => '12:00:00-13:00:00'),
                array('day' => 'thursday', 'hour' => '13:00:00-14:00:00'),
                array('day' => 'friday', 'hour' => '08:00:00-09:00:00'),
                array('day' => 'friday', 'hour' => '09:00:00-10:00:00'),
                array('day' => 'friday', 'hour' => '10:00:00-11:00:00'),
                array('day' => 'friday', 'hour' => '11:00:00-12:00:00'),
                array('day' => 'friday', 'hour' => '12:00:00-13:00:00'),
                array('day' => 'friday', 'hour' => '13:00:00-14:00:00'),

            );

            $scheduleCourseAvailability = array(
                'days' => array('monday', 'tuesday', 'wednesday', 'thursday', 'friday'),
                'hour'=> array('08:00:00-09:00:00', '09:00:00-10:00:00', '10:00:00-11:00:00', '11:00:00-12:00:00', '12:00:00-13:00:00', '13:00:00-14:00:00'),
            );

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

                $teacher = $entityManager->getRepository(User::class)->findTeacherById($teacher_id);

                if (!$teacher) {
                    $this->addFlash('error', 'No hi han docents per a l\'assignatura ' . $subject->getName() . ' (' . $subject->getCourse()->getName() . ' de ' . $subject->getCourse()->getCicle() . ')');
                } else {
                    $restrictions = $teacher->getTeacherConstraints();

                    for ($j = 0; $j < $subject_hours_week; $j++) {
                        do{ 
                            $dayHourRandom= array_rand($scheduleAvailability, 1);
                            
                            $day = $scheduleAvailability[$dayHourRandom]['day'];
                            $hour = $scheduleAvailability[$dayHourRandom]['hour'];
                            
                            $dayHours = explode("-", $hour);

                            $franja = array(
                                'dia' => $day,
                                'hora_inici' => $dayHours[0],
                                'hora_fi' => $dayHours[1],
                            );
                                                        
                        }  while ( $this->checkTeacherConstraints($franja, $restrictions) === true);

                        $schedule = new Schedule();
                        $schedule->setDay($day);
                        $schedule->setHour($hour);
                        $schedule->setTeacher($teacher);
                        $schedule->setSubject($subject);
                        
                        unset($scheduleAvailability[$dayHourRandom]);
                        unset($teacherScheduleAvailability[$teacher_id][$day][$hour]);
           
                        $proposal[$k] = $schedule;
                        $k++;

                        $entityManager->persist($schedule);
                        $entityManager->flush();
                    }
                }
            }
        }
        return $proposal;
    }
  
    /**
     * Mètode que esborra els schedules actuals a la base de dades.
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
     * Mètode que crea la matriu horaria de cada professor. Aquesta matriu serveix per bloquejar el seu horari quan se'ls hi assigni una hora.
     * @return Array matriu horaria de cada professor. 
    */
    public function createTeacherScheduleAvailability()    {
        $entityManager = $this->getDoctrine()->getManager();
        $teachers = $entityManager->getRepository(User::class)->findAll();
        $teacherScheduleAvailability = array();
        
        foreach ($teachers as $t) {
            $teacher_id = $t->getId();
            $teacherScheduleAvailability[$teacher_id][0][0] = true;
            $teacherScheduleAvailability[$teacher_id][0][1] = true;
            $teacherScheduleAvailability[$teacher_id][0][2] = true;
            $teacherScheduleAvailability[$teacher_id][0][3] = true;
            $teacherScheduleAvailability[$teacher_id][0][4] = true;
            $teacherScheduleAvailability[$teacher_id][0][5] = true;
            $teacherScheduleAvailability[$teacher_id][1][0] = true;
            $teacherScheduleAvailability[$teacher_id][1][1] = true;
            $teacherScheduleAvailability[$teacher_id][1][2] = true;
            $teacherScheduleAvailability[$teacher_id][1][3] = true;
            $teacherScheduleAvailability[$teacher_id][1][4] = true;
            $teacherScheduleAvailability[$teacher_id][1][5] = true;
            $teacherScheduleAvailability[$teacher_id][2][0] = true;
            $teacherScheduleAvailability[$teacher_id][2][1] = true;
            $teacherScheduleAvailability[$teacher_id][2][2] = true;
            $teacherScheduleAvailability[$teacher_id][2][3] = true;
            $teacherScheduleAvailability[$teacher_id][2][4] = true;
            $teacherScheduleAvailability[$teacher_id][2][5] = true;
            $teacherScheduleAvailability[$teacher_id][3][0] = true;
            $teacherScheduleAvailability[$teacher_id][3][1] = true;
            $teacherScheduleAvailability[$teacher_id][3][2] = true;
            $teacherScheduleAvailability[$teacher_id][3][3] = true;
            $teacherScheduleAvailability[$teacher_id][3][4] = true;
            $teacherScheduleAvailability[$teacher_id][3][5] = true;
            $teacherScheduleAvailability[$teacher_id][4][0] = true;
            $teacherScheduleAvailability[$teacher_id][4][1] = true;
            $teacherScheduleAvailability[$teacher_id][4][2] = true;
            $teacherScheduleAvailability[$teacher_id][4][3] = true;
            $teacherScheduleAvailability[$teacher_id][4][4] = true;
            $teacherScheduleAvailability[$teacher_id][4][5] = true;
        }
        return $teacherScheduleAvailability;
    }

    /**
     * Mètode que retorna true si el dia i hora proposats per una assignatura i un professor és dins d'una restricció horària del professor que donarà l'assignatura.
     * @param Array $franja dia i hora proposats per una assignatura
     * @param Array $constraints indisponibilitat del professor
     * @return Boolean false si no hi ha incompatibilitat; true si es solapa el dia i hora amb les restriccions del professor. 
    */
    public function checkTeacherConstraints(array $franja, array $constraints)
    {
        if (!empty($constraints)) {
            $constraintsOfTeacher = array();
            foreach (DAYS as $day) {
                $constraintsOfTeacher[$day][0] = array(
                    'hora_inici' => null,
                    'hora_fi' => null,
                );
            }

            foreach ($constraints as $constraint) {
                $constraintsOfTeacher[$constraint['dia']][] = array('hora_inici' => $constraint['hora_inici'], 'hora_fi' => $constraint['hora_fi']);
            }

            foreach ($constraintsOfTeacher[$franja['dia']] as $constraintsDelDia) {
                $constraintHoraInici = new \DateTime(date('Y-m-d') . ' ' . $constraintsDelDia['hora_inici']);
                $constraintHoraFi = new \DateTime(date('Y-m-d') . ' ' . $constraintsDelDia['hora_fi']);
                $claseHoraInici = new \DateTime(date('Y-m-d') . ' ' . $franja['hora_inici']);
                $claseHoraFi = new \DateTime(date('Y-m-d') . ' ' . $franja['hora_fi']);
                
                if ($constraintHoraInici <= $claseHoraInici && $constraintHoraFi >= $claseHoraFi) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }
}
