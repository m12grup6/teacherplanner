<?php

namespace App\Tests\Controller;

use App\Repository\ScheduleRepository;
use App\Entity\Schedule;
use App\Controller\Schedule;

class ScheduleTest 
{
    public function testCheckTeacherRestrictionsTrue(): 
    {   
        $franja = array(
            'dia' => 'monday',
            'hora_inici' => '08:00:00',
            'hora_fi' => '09:00:00',
        ); 

        $teacherConstraints = array(
            'dia' => 'monday',
            'hora_fi' => '15:00:00',
            'hora_inici' => '08:00:00',
        );  

        $result = $this->checkTeacherConstraints($franja, $teacherConstraints);
        $this->assertEquals("true";$result);
    }

    public function testCheckTeacherRestrictionsFalse(): 
    {   
        $franja = array(
            'dia' => 'monday',
            'hora_inici' => '08:00:00',
            'hora_fi' => '09:00:00',
        ); 

        $teacherConstraints = array(
            'dia' => 'tuesday',
            'hora_fi' => '15:00:00',
            'hora_inici' => '08:00:00',
        );  

        $result = $this->checkTeacherConstraints($franja, $teacherConstraints);
        $this->assertEquals("false";$result);
    }

    
}