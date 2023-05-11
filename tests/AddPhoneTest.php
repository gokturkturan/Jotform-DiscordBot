<?php

use PHPUnit\Framework\TestCase;

require_once './src/Command.php';
require_once './src/Commands/PhoneCommand.php';
require_once './src/Receiver.php';
require_once './src/Invoker.php';

function output($receiver){
    $invoker = new Invoker(new PhoneCommand($receiver));
    $output = $invoker->run();
    return $output;
}
final class AddPhoneTest extends TestCase{

    //Success Test
    public function testIsAddedToForm(){
        $receiver = new Receiver("!Phone:Phone,1", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Phone component added successfully.", $output);
    }

    public function testIsAddedToFormWithRequired(){
        $receiver = new Receiver("!Phone:Phone,1,Required:No", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Phone component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithRequiredSpell(){
        $receiver = new Receiver("!Phone:Phone,1,Required:YeS", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Phone component added successfully with Required.", $output);
    }

    public function testIsAddedToFormWithCountryCode(){
        $receiver = new Receiver("!Phone:Phone,1,CountryCode:No", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Phone component added successfully with CountryCode.", $output);
    }

    public function testIsAddedToFormWithCountryCodeSpell(){
        $receiver = new Receiver("!Phone:Phone,1,CountryCode:yEs", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Phone component added successfully with CountryCode.", $output);
    }

    public function testIsAddedToFormWithRequiredandCountryCode(){
        $receiver = new Receiver("!Phone:Phone,1,Required:No,CountryCode:YeS", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Phone component added successfully with Required and CountryCode.", $output);
    }


    //Error Test
    public function testMissing(){

        $receiver = new Receiver("!Phone:", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testMissing2(){

        $receiver = new Receiver("!Phone:Phone", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }

    public function testExcess(){
        //5
        $receiver = new Receiver("!Phone:Phone,1,aas,ffh,sfsf", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please do not enter missing or excess components.", $output);
    }


    public function testIsNumericOrder(){

        $receiver = new Receiver("!Phone:Phone,asd", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testIsNumericOrderWithOptional(){

        $receiver = new Receiver("!Phone:Phone,asd,Required:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter number for order.", $output);
    }

    public function testRequiredSpell(){

        $receiver = new Receiver("!Phone:Phone,1,Requred:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testRequiredYesNoSpell(){

        $receiver = new Receiver("!Phone:Phone,1,Required:Yeee", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for required component.", $output);
    }

    public function testCountryCodeSpell(){

        $receiver = new Receiver("!Phone:Phone,1,ContryCode:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testCountryCodeYesNoSpell(){

        $receiver = new Receiver("!Phone:Phone,1,CountryCode:Yee", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for countryCode component.", $output);
    }

    public function testCountryCodeSpellWithReq(){

        $receiver = new Receiver("!Phone:Phone,1,Required:Yes,ContryCode:Yes", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("You entered wrong component!", $output);
    }

    public function testCountryCodeYesNoSpellWithReq(){

        $receiver = new Receiver("!Phone:Phone,1,Required:Yes,CountryCode:Yee", "926112958449328179");
        $output = output($receiver);

        $this->assertEquals("Please enter YES or No for countryCode component.", $output);
    }


}