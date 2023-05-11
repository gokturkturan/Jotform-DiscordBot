<?php

use PHPUnit\Framework\TestCase;

require_once './src/Command.php';
require_once './src/Commands/FileUploadCommand.php';
require_once './src/Receiver.php';
require_once './src/Invoker.php';

function output($receiver){
    $invoker = new Invoker(new FileUploadCommand($receiver));
    $output = $invoker->run();
    return $output;
}
final class AddFileUploadTest extends TestCase{

    //Success Test
    public function testIsAddedToForm(){
        $receiver = new Receiver("!FileUpload:FileUpload,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("File Upload component added successfully.", $output);
    }

    public function testIsAddedToFormWithRequired(){
        $receiver = new Receiver("!FileUpload:FileUpload,1,Required:No", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("File Upload component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithRequiredSpell(){
        $receiver = new Receiver("!FileUpload:FileUpload,1,Required:YeS", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("File Upload component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithMultiple(){
        $receiver = new Receiver("!FileUpload:FileUpload,1,Multiple:No", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("File Upload component added successfully with Multiple.", $output);
    }

    public function testIsAddedToFormWithMultipleSpell(){
        $receiver = new Receiver("!FileUpload:FileUpload,1,Multiple:yeS", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("File Upload component added successfully with Multiple.", $output);
    }

    public function testIsAddedToFormWithType(){
        $receiver = new Receiver("!FileUpload:FileUpload,1,Type:jpeg-png", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("File Upload component added successfully with Type.", $output);
    }

    public function testIsAddedToFormWithMultipleandType(){
        $receiver = new Receiver("!FileUpload:FileUpload,1,Multiple:yeS,Type:jpeg-png", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("File Upload component added successfully with Multiple and Type.", $output);
    }

    public function testIsAddedToFormWithRequiredandMultiple(){
        $receiver = new Receiver("!FileUpload:FileUpload,1,Required:No,Multiple:yeS", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("File Upload component added successfully with Required and Multiple.", $output);
    }

    public function testIsAddedToFormWithRequiredandType(){
        $receiver = new Receiver("!FileUpload:FileUpload,1,Required:No,Type:jpeg", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("File Upload component added successfully with Required, Type.", $output);
    }

    public function testIsAddedToFormWithAll(){
        $receiver = new Receiver("!FileUpload:FileUpload,1,Required:No,Multiple:yeS,Type:jpeg", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("File Upload component added successfully with Required, Multiple and Type.", $output);
    }


    //Error Test
    public function testMissing(){

        $receiver = new Receiver("!FileUpload:", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testMissing2(){

        $receiver = new Receiver("!FileUpload:FileUpload", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testExcess(){
        //5
        $receiver = new Receiver("!FileUpload:FileUpload,1,aas,ffh,sfsf,adsg", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testIsNumericOrder(){

        $receiver = new Receiver("!FileUpload:FileUpload,asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testIsNumericOrderWithOptional(){

        $receiver = new Receiver("!FileUpload:FileUpload,asd,Required:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testRequiredSpell(){

        $receiver = new Receiver("!FileUpload:FileUpload,1,Requred:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testRequiredYesNoSpell(){

        $receiver = new Receiver("!FileUpload:FileUpload,1,Required:Yeee", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for required component.", $output);
    }

    public function testMultipleYesNoSpell(){

        $receiver = new Receiver("!FileUpload:FileUpload,1,Multiple:Yeee", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for multiple component.", $output);
    }

    public function testWrongOptional(){

        $receiver = new Receiver("!FileUpload:FileUpload,1,asdf", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testWrongOptionalwithReq(){

        $receiver = new Receiver("!FileUpload:FileUpload,1,Required:Yes,asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testWrongOptionalwithReqandMult(){

        $receiver = new Receiver("!FileUpload:FileUpload,1,Required:Yes,Multiple:Yes,asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

  
    public function testMultipleYesNoSpellwithReq(){

        $receiver = new Receiver("!FileUpload:FileUpload,1,Required:Yes,Multiple:Yeee", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for multiple component.", $output);
    }



}