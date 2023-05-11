<?php

use PHPUnit\Framework\TestCase;

require_once './src/Command.php';
require_once './src/Commands/ShortTextCommand.php';
require_once './src/Receiver.php';
require_once './src/Invoker.php';

function output($receiver){
    $invoker = new Invoker(new ShortTextCommand($receiver));
    $output = $invoker->run();
    return $output;
}
final class AddShortTextTest extends TestCase{

    //Success Test
    public function testIsAddedToForm(){
        $receiver = new Receiver("!ShortText:ShortText,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Short Text component added successfully.", $output);
    }

    public function testIsAddedToFormWithRequired(){
        $receiver = new Receiver("!ShortText:ShortText,1,Required:No", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Short Text component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithRequiredSpell(){
        $receiver = new Receiver("!ShortText:ShortText,1,Required:YeS", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Short Text component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithMaxSize(){
        $receiver = new Receiver("!ShortText:ShortText,1,100", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Short Text component added successfully with Maxsize.", $output);
    }

    public function testIsAddedToFormWithRequiredandMaxSize(){
        $receiver = new Receiver("!ShortText:ShortText,1,Required:No,100", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Short Text component added successfully with Required and Maxsize.", $output);
    }

    //Error Test
    public function testMissing(){

        $receiver = new Receiver("!ShortText:", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testMissing2(){

        $receiver = new Receiver("!ShortText:ShortText", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testExcess(){
        //5
        $receiver = new Receiver("!ShortText:ShortText,1,aas,ffh,sfsf", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testIsNumericOrder(){

        $receiver = new Receiver("!ShortText:ShortText,asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testIsNumericOrderWithOptional(){

        $receiver = new Receiver("!ShortText:ShortText,asd,Required:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testRequiredSpell(){

        $receiver = new Receiver("!ShortText:ShortText,1,Requred:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testRequiredYesNoSpell(){

        $receiver = new Receiver("!ShortText:ShortText,1,Required:Yeee", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for required component.", $output);
    }

    public function testWrongOptional(){

        $receiver = new Receiver("!ShortText:ShortText,1,asdf", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }


    public function testIsNumericMaxSizeWithReq(){

        $receiver = new Receiver("!ShortText:ShortText,1,Required:Yes,asds", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You should enter number for Maxsize component!", $output);
    }


}