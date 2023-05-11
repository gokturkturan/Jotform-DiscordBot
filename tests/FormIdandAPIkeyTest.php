<?php

use PHPUnit\Framework\TestCase;

require_once './src/Command.php';
require_once './src/Commands/FullNameCommand.php';
require_once './src/ReceiverTestClass.php';
require_once './src/Invoker.php';


final class FormIdandAPIkeyTest extends TestCase{

    public function testFullNameFormIdException(){
            

            $this->expectException(Exception::class);

            $question = array(
                'type' => 'control_fullname',
                'text' => 'name',
                'order' => 1,
            );
            
            $jotformAPI = new JotForm("a760833a60fa19c089fc7b9c7b75688c");
            $jotformAPI->createFormQuestion(2217035708840, $question);

    }

    public function testFullNameAPIkeyException(){
        

        $this->expectException(Exception::class);

        $question = array(
            'type' => 'control_fullname',
            'text' => 'name',
            'order' => 1,
        );
        
        $jotformAPI = new JotForm("a760833a60fa19c089fc7b9c7b75688");
        $jotformAPI->createFormQuestion(221703570884054, $question);

    }


    public function testNumberFormIdException(){
            

        $this->expectException(Exception::class);
        $question = array(
            'type' => 'control_number',
            'text' => 'number',
            'order' => 1,
            'required' => 'Yes',
            'minValue' => 2,
            'maxValue' => 6
        );
        
        $jotformAPI = new JotForm("a760833a60fa19c089fc7b9c7b75688c");
        $jotformAPI->createFormQuestion(2217035708840, $question);

    }

    public function testNumberAPIkeyException(){
        

        $this->expectException(Exception::class);

        $question = array(
            'type' => 'control_number',
            'text' => 'number',
            'order' => 1,
            'required' => 'Yes',
            'minValue' => 2,
            'maxValue' => 6
        );
        
        $jotformAPI = new JotForm("a760833a60fa19c089fc7b9c7b75688");
        $jotformAPI->createFormQuestion(221703570884054, $question);

    }
}