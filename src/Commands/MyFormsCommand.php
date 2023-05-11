<?php
class MyFormsCommand implements Command
{
    private $command;

    public function __construct(Receiver $command)
    {
        $this->command = $command;
    }

    public function execute()
    {
        return $this->command->myForms();
    }
}