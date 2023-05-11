<?php

use PHPUnit\Framework\TestCase;

require_once './src/Command.php';
require_once './src/Commands/LongTextCommand.php';
require_once './src/Receiver.php';
require_once './src/Invoker.php';

function output($receiver){
    $invoker = new Invoker(new LongTextCommand($receiver));
    $output = $invoker->run();
    return $output;
}
final class AddLongTextTest extends TestCase{

    //Success Test
    public function testIsAddedToForm(){
        $receiver = new Receiver("!LongText:LongText,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Long Text component added successfully.", $output);
    }

    public function testIsAddedToFormWithRequired(){
        $receiver = new Receiver("!LongText:LongText,1,Required:No", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Long Text component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithRequiredSpell(){
        $receiver = new Receiver("!LongText:LongText,1,Required:YeS", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Long Text component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithEntryLimit(){
        $receiver = new Receiver("!LongText:LongText,1,Words-100", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Long Text component added successfully with EntryLimit.", $output);
    }

    public function testIsAddedToFormWithRequiredandEntryLimit(){
        $receiver = new Receiver("!LongText:LongText,1,Required:No,Letters-100", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Long Text component added successfully with Required and EntryLimit.", $output);
    }


    //Error Test
    public function testMissing(){

        $receiver = new Receiver("!LongText:", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testMissing2(){

        $receiver = new Receiver("!LongText:LongText", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testExcess(){
        //5
        $receiver = new Receiver("!LongText:LongText,1,aas,ffh,sfsf", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testIsNumericOrder(){

        $receiver = new Receiver("!LongText:LongText,asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testIsNumericOrderWithOptional(){

        $receiver = new Receiver("!LongText:LongText,asd,Required:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testRequiredSpell(){

        $receiver = new Receiver("!LongText:LongText,1,Requred:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testRequiredYesNoSpell(){

        $receiver = new Receiver("!LongText:LongText,1,Required:Yeee", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for required component.", $output);
    }

    public function testWrongOptional(){

        $receiver = new Receiver("!LongText:LongText,1,asdf", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testEntryLimitIsNumeric(){
        $receiver = new Receiver("!LongText:LongText,1,Words-aa", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You should enter number for Entry Limit component.", $output);
    }
  
    public function testEntryLimitSpellWithReq(){

        $receiver = new Receiver("!LongText:LongText,1,Required:Yes,Wrds-100", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testEntryLimitIsNumericWithReq(){

        $receiver = new Receiver("!LongText:LongText,1,Required:Yes,Words-aa", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You should enter number for Entry Limit component.", $output);
    }

}