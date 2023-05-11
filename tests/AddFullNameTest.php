<?php

use PHPUnit\Framework\TestCase;

require_once './src/Command.php';
require_once './src/Commands/FullNameCommand.php';
require_once './src/Receiver.php';
require_once './src/Invoker.php';

function output($receiver){
    $invoker = new Invoker(new FullNameCommand($receiver));
    $output = $invoker->run();
    return $output;
}
final class AddFullNameTest extends TestCase{

    //Success Test
    public function testIsAddedToForm(){
        $receiver = new Receiver("!FullName:Full Name,1", "926112958449328179");
        $output = output($receiver);
        
        $this->assertEquals("Full Name component added successfully.", $output);
    }

    public function testIsAddedToFormWithRequired(){
        $receiver = new Receiver("!FullName:Full Name,1,Required:No", "926112958449328179");
        $output = output($receiver);
    
        $this->assertEquals("Full Name component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithRequiredSpell(){
        $receiver = new Receiver("!FullName:Full Name,1,Required:YeS", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Full Name component added successfully with Required.", $output);
    }


    //Error Test
    public function testMissing(){
        $receiver = new Receiver("!FullName:", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testMissing2(){
        $receiver = new Receiver("!FullName:Name", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testExcess(){
        $receiver = new Receiver("!FullName:Name,1,aas,ffh", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testIsNumericOrder(){
        $receiver = new Receiver("!FullName:Full Name,asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testIsNumericOrderWithRequired(){
        $receiver = new Receiver("!FullName:Full Name,asd,Required:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testRequiredSpell(){
        $receiver = new Receiver("!FullName:Full Name,1,Requred:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testRequiredYesNoSpell(){
        $receiver = new Receiver("!FullName:Full Name,1,Required:Yeee", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for required component.", $output);
    }

    
}


