<?php

use PHPUnit\Framework\TestCase;

require_once './src/Command.php';
require_once './src/Commands/DropdownCommand.php';
require_once './src/Receiver.php';
require_once './src/Invoker.php';

function output($receiver){
    $invoker = new Invoker(new DropdownCommand($receiver));
    $output = $invoker->run();
    return $output;
}
final class AddDropdownTest extends TestCase{

    //Success Test
    public function testIsAddedToFormWithOption(){
        $receiver = new Receiver("!Dropdown:Dropdown,1,a-b-c", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Dropdown component added successfully with Options.", $output);
    }

    public function testIsAddedToFormWithSpecial(){
        $receiver = new Receiver("!Dropdown:Dropdown,1,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Dropdown component added successfully with Special.", $output);
    }

    public function testIsAddedToFormWithOptionandRequired(){
        $receiver = new Receiver("!Dropdown:Dropdown,1,Required:No,a-b-c", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Dropdown component added successfully with options with Required and Options.", $output);
    }

    public function testIsAddedToFormWithRequiredSpell(){
        $receiver = new Receiver("!Dropdown:Dropdown,1,Required:YeS,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Dropdown component added successfully with Required and Special.", $output);
    }

    //Error Test
    public function testMissing(){

        $receiver = new Receiver("!Dropdown:", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testMissing2(){

        $receiver = new Receiver("!Dropdown:Dropdown", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testExcess(){
        //5
        $receiver = new Receiver("!Dropdown:Dropdown,1,aas,ffh,sfsf", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testIsNumericOrder(){

        $receiver = new Receiver("!Dropdown:Dropdown,asd,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testIsNumericOrderWithOptional(){

        $receiver = new Receiver("!Dropdown:Dropdown,asd,Required:Yes,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testRequiredSpell(){

        $receiver = new Receiver("!Dropdown:Dropdown,1,Requred:Yes,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testRequiredYesNoSpell(){

        $receiver = new Receiver("!Dropdown:Dropdown,1,Required:Yeee,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for required component.", $output);
    }

    public function testWrongOptional(){

        $receiver = new Receiver("!Dropdown:Dropdown,1,asdf", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testWrongDrop(){
        $receiver = new Receiver("!Dropdown:Dropdown,1,a+a+a", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }
  
    public function testWrongSpecialRange(){

        $receiver = new Receiver("!Dropdown:Dropdown,1,9", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You have to choose between 1-7 for pre-defined values. Look at predefined values from !8", $output);
    }

    public function testWrongDropWithReq(){

        $receiver = new Receiver("!Dropdown:Dropdown,1,Required:Yes,a+a+a", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component for Special/Optional component.", $output);
    }

    public function testMissDropWithReq(){

        $receiver = new Receiver("!Dropdown:Dropdown,1,Required:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter option or special components.", $output);
    }

    public function testWrongSpecialRangeWithReq(){

        $receiver = new Receiver("!Dropdown:Dropdown,1,Required:Yes,9", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You have to choose between 1-7 for pre-defined values. Look at predefined values from !8", $output);
    }

    public function testBothSpecialandOption(){

        $receiver = new Receiver("!Dropdown:Dropdown,1,2,a-b-c", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter just one of them (special or options)", $output);
    }

    public function testBothSpecialandOption2(){

        $receiver = new Receiver("!Dropdown:Dropdown,1,a-b-c,2", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter just one of them (special or options)", $output);
    }
    

}