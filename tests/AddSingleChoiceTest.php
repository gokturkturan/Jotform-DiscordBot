<?php

use PHPUnit\Framework\TestCase;

require_once './src/Command.php';
require_once './src/Commands/SingleChoiceCommand.php';
require_once './src/Receiver.php';
require_once './src/Invoker.php';

function output($receiver){
    $invoker = new Invoker(new SingleChoiceCommand($receiver));
    $output = $invoker->run();
    return $output;
}
final class AddSingleChoiceTest extends TestCase{

    //Success Test
    public function testIsAddedToFormWithOption(){
        $receiver = new Receiver("!SingleChoice:SingleChoice,1,a-b-c", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Single Choice component added successfully with Options.", $output);
    }

    public function testIsAddedToFormWithSpecial(){
        $receiver = new Receiver("!SingleChoice:SingleChoice,1,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Single Choice component added successfully with Special.", $output);
    }

    public function testIsAddedToFormWithOptionandRequired(){
        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:No,a-b-c", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Single Choice component added successfully with Required and Options.", $output);
    }

    public function testIsAddedToFormWithRequiredSpell(){
        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:YeS,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Single Choice component added successfully with Required and Special.", $output);
    }

    public function testIsAddedToFormWithOptionandAllow(){
        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Allow:No,a-b-c", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Single Choice component added successfully with Allow and Options.", $output);
    }

    public function testIsAddedToFormWithAllowSpell(){
        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Allow:YeS,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Single Choice component added successfully with Allow and Special.", $output);
    }

    public function testIsAddedToFormALLWithOption(){
        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:YeS,Allow:YeS,a-b-c", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Single Choice component added successfully with Required, Allow and Options.", $output);
    }

    public function testIsAddedToFormALLWithSpecial(){
        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:YeS,Allow:YeS,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Single Choice component added successfully with Required, Allow and Special.", $output);
    }


    //Error Test
    public function testMissing(){

        $receiver = new Receiver("!SingleChoice:", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testMissing2(){

        $receiver = new Receiver("!SingleChoice:SingleChoice", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testExcess(){
        //6
        $receiver = new Receiver("!SingleChoice:SingleChoice,1,4,5,6,7", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testIsNumericOrder(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,asd,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testIsNumericOrderWithOptional(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,asd,Required:Yes,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testRequiredSpell(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Requred:Yes,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testRequiredYesNoSpell(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:Yeee,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for required component.", $output);
    }

    public function testAllowSpell(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Allw:Yes,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testAllowYesNoSpell(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Allow:Yeee,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for allow component.", $output);
    }

    public function testWrongChoice(){
        $receiver = new Receiver("!SingleChoice:SingleChoice,1,a+a+a", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter option or special components.", $output);
    }
  
    public function testWrongSpecialRange(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,9", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You have to choose between 1-3 for pre-defined values. Look at predefined values from !9", $output);
    }

    public function testWrongChoiceWithReq(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:Yes,a+a+a", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testWrongChoiceWithAllow(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Allow:Yes,a+a+a", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component for Special/Optional component.", $output);
    }

    public function testMissChoiceWithReq(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter option or special components.", $output);
    }

    public function testMissChoiceWithAllow(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Allow:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter option or special components.", $output);
    }

    public function testWrongSpecialRangeWithReq(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:Yes,9", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You have to choose between 1-3 for pre-defined values. Look at predefined values from !9", $output);
    }

    public function testWrongSpecialRangeWithAllow(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Allow:Yes,9", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You have to choose between 1-3 for pre-defined values. Look at predefined values from !9", $output);
    }

    public function testMissChoiceWithAll(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:No,Allow:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter option or special components.", $output);
    }

    public function testWrongChoiceWithAll(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:No,Allow:Yes,a+a+a", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component for Special/Optional component.", $output);
    }

    public function testWrongSpecialRangeWithAll(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,Required:No,Allow:Yes,9", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You have to choose between 1-3 for pre-defined values. Look at predefined values from !9", $output);
    }

    public function testBothSpecialandOption(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,2,a-b-c", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter just one of them (special or options)", $output);
    }

    public function testBothSpecialandOption2(){

        $receiver = new Receiver("!SingleChoice:SingleChoice,1,a-b-c,2", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter just one of them (special or options)", $output);
    }

}