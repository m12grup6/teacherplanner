<?php
//ini_set('memory_limit','-1');

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


define('DAYS', array('Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres'));
define('TIMETABLE', array('8-9', '9-10', '10-11', '11-12', '12-13', '13-14'));


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

        $franja = array(
            'dia' => 'Dilluns',
            'hora_inici' => '10:00',
            'hora_fi' => '11:00'
        );

        dump($this->testTeacherConstraints($franja, $allTeachers[0]->getTeacherConstraints()));

        return $this->redirectToRoute('app_getCourses');
    }

    /**
     * @Route("/generateSchedule", name="app_schedule")
    */

    public function generateSchedule(){
        $proposedSchedule= $this->generateProposedSchedule();

        return $this->render('course/showSchedule.html.twig', [
            'schedule' => $proposedSchedule,
        ]);
    }

    public function generateProposedSchedule(): array{
        $entityManager = $this->getDoctrine()->getManager();
        $courses = $entityManager->getRepository(Course::class)->findAll();
        $proposal = [];
        $k=0;
        $scheduleAvailability = array (
            array ('Dilluns','Dimarts','Dimecres','Dijous','Divendres'),
            array ('8-9','9-10','10-11','11-12','12-13','13-14'),
        );

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


        foreach ($courses as $c){
            $course_id = $c->getId();
            $courseSubjects = $this->getDoctrine()
                ->getRepository(Subject::class)
                ->findBySubjectsByCourseId($course_id);

            $arrLength = count($courseSubjects);

            for ($i = 0; $i < $arrLength; $i++) {
                $subject_id = $courseSubjects[$i]['id'];
                $subject_hours_week = $courseSubjects[$i]['hours_week'];
                $subject = $entityManager->getRepository(Subject::class)->find($subject_id);

                //TO DO - Conseguir profesores por subject id --> TO DO --> crear un metodo
                $teacher = $this->getTeacherBySubjectId($subject_id);
                //TO DO - Conseguir restricciones profes

                for ($j = 0; $j < $subject_hours_week; $j++) {
                    $dayRandom=array_rand(DAYS,1);
                    $hourRandom=array_rand(TIMETABLE,1);

                    while ($scheduleAvailability[$dayRandom][$hourRandom] === false){  // && TO DO agregar check restricciones profes
                        $dayRandom=array_rand(DAYS,1);
                        $hourRandom=array_rand(TIMETABLE,1);
                    }

                    $day = DAYS[$dayRandom];
                    $hour = TIMETABLE[$hourRandom];

                    $schedule = new Schedule();
                    $schedule->setDay($day);
                    $schedule->setHour($hour);
                    $schedule->setTeacher($teacher);
                    $schedule->setSubject($subject);
                    $scheduleAvailability[$dayRandom][$hourRandom] = false;

                    $proposal[$k] = $schedule;
                    $k++;
                }
            }
        }

        return $proposal;
    }

    //TODO - Pendiente de codificar. De momento es un mock
    public function getTeacherBySubjectId ($id){
        $entityManager = $this->getDoctrine()->getManager();

        switch ($id) {
            case 4:
                $teacher = $entityManager->getRepository(User::class)->find(1);
                break;
            case 7:
                $teacher = $entityManager->getRepository(User::class)->find(2);
                break;
            case 8:
                $teacher = $entityManager->getRepository(User::class)->find(3);
                break;
            default:
                $teacher = $entityManager->getRepository(User::class)->find(4);
                break;
        }
        return $teacher;
    }

    // Funció que retorna true si la franja és dins d'una restricció horària
    public function testTeacherConstraints(array $franja, array $constraints)
    {
        $constraintsOfTeacher = array();
        foreach($constraints as $constraint) {
            $constraintsOfTeacher[$constraint['dia']][] = array('hora_inici' => $constraint['hora_inici'], 'hora_fi' => $constraint['hora_fi']);
        }

        foreach($constraintsOfTeacher[$franja['dia']] as $constraintsDelDia){
            if(new \DateTime(date('Y-m-d') . ' ' . $constraintsDelDia['hora_inici']) >= new \DateTime(date('Y-m-d') . ' ' . $franja['hora_inici']) && new \DateTime(date('Y-m-d') . ' ' . $constraintsDelDia['hora_fi']) <= new \DateTime(date('Y-m-d') . ' ' . $franja['hora_fi'])){
                return true;
            }
        }
        return false;
    }
}
