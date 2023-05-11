<?php
class Invoker
{
    private Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }


    public function setCommand(Command $cmd)
    {
        $this->command = $cmd;
    }

    public function run()
    {
        return $this->command->execute();
    }
}
