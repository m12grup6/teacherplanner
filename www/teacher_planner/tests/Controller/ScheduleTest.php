<?php

namespace App\Tests\Controller;

use App\Controller\ScheduleController;
use PHPUnit\Framework\TestCase;


class ScheduleTest extends TestCase {

    public function testTeacherWithoutRestrictionsIsAvailable() {   
        $franja = array(
            'dia' => 'monday',
            'hora_inici' => '08:00:00',
            'hora_fi' => '09:00:00',
        ); 

        $teacherConstraints = array();

        $mockSubjectRepository = $this->createMock(\App\Repository\SubjectRepository::class);
        $mockUserRepository = $this->createMock(\App\Repository\UserRepository::class);
        $mockCourseRepository = $this->createMock(\App\Repository\CourseRepository::class);
        $mockEntityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $schedule = new ScheduleController($mockSubjectRepository, $mockUserRepository, $mockCourseRepository, $mockEntityManager);
        
        $result = $schedule->checkTeacherConstraints($franja, $teacherConstraints);
        $this->assertTrue(false===$result);
    }

    public function testTeacherWithRestrictionsIsUnavailable() {   
        $franja = array(
            'dia' => 'monday',
            'hora_inici' => '08:00:00',
            'hora_fi' => '09:00:00',
        ); 

        $teacherConstraints = array(
            array (
                'dia' => 'monday',
                'hora_fi' => '15:00:00',
                'hora_inici' => '08:00:00',
            ),
            array (
                'dia' => 'friday',
                'hora_fi' => '15:00:00',
                'hora_inici' => '08:00:00',
            )
        );
       

        $mockSubjectRepository = $this->createMock(\App\Repository\SubjectRepository::class);
        $mockUserRepository = $this->createMock(\App\Repository\UserRepository::class);
        $mockCourseRepository = $this->createMock(\App\Repository\CourseRepository::class);
        $mockEntityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $schedule = new ScheduleController($mockSubjectRepository, $mockUserRepository, $mockCourseRepository, $mockEntityManager);
        
        $result = $schedule->checkTeacherConstraints($franja, $teacherConstraints);
        $this->assertTrue(true===$result);
    }

}