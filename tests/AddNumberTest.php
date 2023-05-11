<?php

use PHPUnit\Framework\TestCase;

require_once './src/Command.php';
require_once './src/Commands/NumberCommand.php';
require_once './src/Receiver.php';
require_once './src/Invoker.php';

function output($receiver){
    $invoker = new Invoker(new NumberCommand($receiver));
    $output = $invoker->run();
    return $output;
}
final class AddNumberTest extends TestCase{

    //Success Test
    public function testIsAddedToForm(){
        $receiver = new Receiver("!Number:Number,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Number component added successfully.", $output);
    }

    public function testIsAddedToFormWithRequired(){
        $receiver = new Receiver("!Number:Number,1,Required:No", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Number component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithRequiredSpell(){
        $receiver = new Receiver("!Number:Number,1,Required:YeS", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Number component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithMinValue(){
        $receiver = new Receiver("!Number:Number,1,Min:2", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Number component added successfully with Min.", $output);
    }

    public function testIsAddedToFormWithMaxValue(){
        $receiver = new Receiver("!Number:Number,1,Max:10", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Number component added successfully with Max.", $output);
    }

    public function testIsAddedToFormWithMinandMax(){
        $receiver = new Receiver("!Number:Number,1,Min:2,Max:6", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Number component added successfully with Min and Max.", $output);
    }

    public function testIsAddedToFormWithRequiredandMin(){
        $receiver = new Receiver("!Number:Number,1,Required:No,Min:2", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Number component added successfully with Required and Min.", $output);
    }

    public function testIsAddedToFormWithRequiredandMax(){
        $receiver = new Receiver("!Number:Number,1,Required:No,Max:2", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Number component added successfully with Required and Max.", $output);
    }

    public function testIsAddedToFormWithRequiredandMinandMax(){
        $receiver = new Receiver("!Number:Number,1,Required:No,Min:2,Max:10", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Number component added successfully with Required, Min and Max.", $output);
    }


    //Error Test
    public function testMissing(){

        $receiver = new Receiver("!Number:", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testMissing2(){

        $receiver = new Receiver("!Number:Number", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testExcess(){
        //5
        $receiver = new Receiver("!Number:Number,1,aas,ffh,sfsf,adsg", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testIsNumericOrder(){

        $receiver = new Receiver("!Number:Number,asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testIsNumericOrderWithOptional(){

        $receiver = new Receiver("!Number:Number,asd,Required:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testRequiredSpell(){

        $receiver = new Receiver("!Number:Number,1,Requred:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testRequiredYesNoSpell(){

        $receiver = new Receiver("!Number:Number,1,Required:Yeee", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for required component.", $output);
    }

    public function testWrongOptional(){

        $receiver = new Receiver("!Number:Number,1,asdf", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testWrongOptionalwithReq(){

        $receiver = new Receiver("!Number:Number,1,Required:Yes,asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testWrongOptionalwithReqandMin(){

        $receiver = new Receiver("!Number:Number,1,Required:Yes,Min:2,asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testMinIsNumeric(){
        $receiver = new Receiver("!Number:Number,1,Min:asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter the Min value as a number.", $output);
    }

    public function testMaxIsNumeric(){
        $receiver = new Receiver("!Number:Number,1,Max:asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter the Max value as a number.", $output);
    }
  
    public function testMinIsNumericWithReq(){

        $receiver = new Receiver("!Number:Number,1,Required:Yes,Min:asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter the Min value as a number.", $output);
    }

    public function testMaxIsNumericWithReq(){

        $receiver = new Receiver("!Number:Number,1,Required:Yes,Max:asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter the Max value as a number.", $output);
    }

    public function testMinIsNumericWithMax(){

        $receiver = new Receiver("!Number:Number,1,Min:asd,Max:5", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter the Min value as a number.", $output);
    }

    public function testMaxIsNumericWithMin(){

        $receiver = new Receiver("!Number:Number,1,Min:2,Max:asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter the Max value as a number.", $output);
    }

    public function testMaxIsNumericWithAll(){

        $receiver = new Receiver("!Number:Number,1,Required:Yes,Min:2,Max:asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter the Max value as a number.", $output);
    }

    public function testMinIsNumericWithAll(){

        $receiver = new Receiver("!Number:Number,1,Required:Yes,Min:asd,Max:5", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter the Min value as a number.", $output);
    }

  


}