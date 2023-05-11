<?php
require_once './key.php';
require_once './Command.php';
require_once './Receiver.php';
require_once './Invoker.php';
require_once './Commands/RegisterInfoCommand.php';
require_once './Commands/FullNameCommand.php';
require_once './Commands/CreateFormCommand.php';
require_once './Commands/EmailCommand.php';
require_once './Commands/AddressCommand.php';
require_once './Commands/PhoneCommand.php';
require_once './Commands/DatePickerCommand.php';
require_once './Commands/ShortTextCommand.php';
require_once './Commands/LongTextCommand.php';
require_once './Commands/DropdownCommand.php';
require_once './Commands/SingleChoiceCommand.php';
require_once './Commands/MultipleChoiceCommand.php';
require_once './Commands/NumberCommand.php';
require_once './Commands/FileUploadCommand.php';
require_once './Commands/UpdateApiKeyCommand.php';
require_once './Commands/MyFormsCommand.php';
require_once './Commands/SelectFormCommand.php';
require_once './Commands/BugReportCommand.php';
require_once './Commands/SubmissionCommand.php';
require_once './Commands/ExitSubmissionCommand.php';
require_once './Commands/AnswerQuestionCommand.php';

include __DIR__ . '/../vendor/autoload.php';

use Discord\Discord;

$key = getDiscordKey();

function output($message, $invoker)
{
    $output = $invoker->run();
    $message->reply($output);
}

$discord = new Discord(['token' => $key]);

$discord->on('ready', function (Discord $discord) {
    $discord->on('message', function ($message, $discord) {
        $content = $message->content;

        if (strpos($content, '!') === false) return;

        $discordID = $message->author->id;
        $receiver = new Receiver($content, $discordID);

        switch ($content) {
            case '!Help':
                $response = "Hello, I'm Jotform Bot, and I'll be with you through the form creation process. If you are not registered to the bot, please continue with the '!Register' command. If you are already registered, you can continue using the !CreateForm,!Submission,!MyForms,!Bug commands.";
                $message->reply($response);
                break;

            case '!Register':
                $response = "Please enter your Jotform apikey. If you don't have api key please create from here https://www.jotform.com/myaccount/api then write the following command (!RegisterInfo:apikey) e.g: !RegisterInfo:df5sk6j5t";
                $message->reply($response);
                break;

            case str_starts_with($content, "!RegisterInfo:"):
                $invoker = new Invoker(new RegisterInfoCommand($receiver));
                output($message, $invoker);
                break;

            case str_starts_with($content, "!NewApiKey:"):
                $invoker = new Invoker(new UpdateApiKeyCommand($receiver));
                output($message, $invoker);
                break;

            case str_starts_with($content, "!CreateForm:"):
                $invoker = new Invoker(new CreateFormCommand($receiver));
                output($message, $invoker);
                break;

            case str_starts_with($content, "!MyForms"):
                $invoker = new Invoker(new MyFormsCommand($receiver));
                $output = $invoker->run();
                $i = 0;
                while ($i < count($output)) {
                    $allString = "Form ID =" . " " . $output[$i][0] . " | Form Name = " . $output[$i][1];
                    $message->reply($allString);
                    $i++;
                }
                break;

            case str_starts_with($content, "!SelectForm:"):
                $invoker = new Invoker(new SelectFormCommand($receiver));
                output($message, $invoker);
                break;

            case '!InfoFormElements':
                $response = "Please choose to add form element
                1-Full Name !1
                2-Email !2
                3-Address !3
                4-Phone !4
                5-Short Text !5
                6-Long Text !6
                7-Dropdown !7
                8-Single Choice !8
                9-Multiple Choice !9
                10-Number !10
                11-File Upload !11";
                $message->reply($response);
                break;

            case '!1':
                $message->reply("You will add Full Name component. Please set order with '!FullName:'. Ex: !FullName:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory). You can write the answer to the optional features, but if you do not, they will be considered negative.");
                break;

            case str_starts_with($content, "!FullName:"):
                $invoker = new Invoker(new FullNameCommand($receiver));
                output($message, $invoker);
                break;

            case '!2':
                $message->reply("You will add Email component. Please set order with '!Email:'. Ex: !Email:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory). You can write the answer to the optional features, but if you do not, they will be considered negative.");
                break;

            case str_starts_with($content, "!Email:"):
                $invoker = new Invoker(new EmailCommand($receiver));
                output($message, $invoker);
                break;

            case '!3':
                $message->reply("You will add Address component. Please set order with '!Address:'. Ex: !Address:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory). You can write the answer to the optional features, but if you do not, they will be considered negative.");
                break;

            case str_starts_with($content, "!Address:"):
                $invoker = new Invoker(new AddressCommand($receiver));
                output($message, $invoker);
                break;

            case '!4':
                $message->reply("You will add Phone component. Please set order with '!Phone:'. Ex: !Phone:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory),CountryCode:Yes/No (Optional)(Ask for country code). You can write the answer to the optional features, but if you do not, they will be considered negative.");
                break;

            case str_starts_with($content, "!Phone:"):
                $invoker = new Invoker(new PhoneCommand($receiver));
                output($message, $invoker);
                break;

            case '!5':
                $message->reply("You will add Short Text component. Please set order with '!ShortText:'. Ex: !ShortText:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory),maxsize (optional)(number). You can write the optional properties, but if you don't, maxsize will be 400 and Required will be accepted as No.");
                break;

            case str_starts_with($content, "!ShortText:"):
                $invoker = new Invoker(new ShortTextCommand($receiver));
                output($message, $invoker);
                break;

            case '!6':
                $message->reply("You will add Long Text component. Please set order with '!LongText:'. Ex: !LongText:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory),entryLimit (optional)(Words-number / Letters-number). You can write the optional properties, but if you don't, entryLimit will be limitless and Required will be accepted as No.");
                break;

            case str_starts_with($content, "!LongText:"):
                $invoker = new Invoker(new LongTextCommand($receiver));
                output($message, $invoker);
                break;

            case '!7':
                $message->reply("You will add Dropdown component. Please set order with '!Dropdown:'. Ex: !Dropdown:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory),special (Countries-> 1, US States-> 2, US States Abbr-> 3, Months-> 4, Gender-> 5, Last 100 Years-> 6, Days-> 7),options (A-B-C). Special and options properties cannot be written at the same time. You can write the optional properties, but if you don't, Required will be accepted as No.");
                break;

            case str_starts_with($content, "!Dropdown:"):
                $invoker = new Invoker(new DropdownCommand($receiver));
                output($message, $invoker);
                break;

            case '!8':
                $message->reply("You will add Single Choice component. Please set order with '!SingleChoice:'. Ex: !SingleChoice:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory),Allow:Yes/No (optional)(Let users type a text if none of the options are applicable),special(Gender-> 1, Days-> 2, Months-> 3),options (A-B-C). Special and options properties cannot be written at the same time. You can write the optional properties, but if you don't, Allow will be No and Required will be accepted as No.");
                break;

            case str_starts_with($content, "!SingleChoice:"):
                $invoker = new Invoker(new SingleChoiceCommand($receiver));
                output($message, $invoker);
                break;

            case '!9':
                $message->reply("You will add Multiple Choice component. Please set order with '!MultipleChoice:'. Ex: !MultipleChoice:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory),Allow:Yes/No (Optional)(Let users type a text if none of the options are applicable),special(Gender-> 1, Days-> 2, Months-> 3),options (A-B-C). Special and options properties cannot be written at the same time. You can write the optional properties, but if you don't, Allow will be No and Required will be accepted as No.");
                break;

            case str_starts_with($content, "!MultipleChoice:"):
                $invoker = new Invoker(new MultipleChoiceCommand($receiver));
                output($message, $invoker);
                break;

            case '!10':
                $message->reply("You will add Number component. Please set order with '!Number:'. Ex: !Number:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory),Min:number (Optional)(It won't let users to select less than this number),Max:number (Optional)(It won't let users to select more than this number). You can write the optional features, but if you don't, Min and Max will be set to unlimited and Required will be accepted as No.");
                break;

            case str_starts_with($content, "!Number:"):
                $invoker = new Invoker(new NumberCommand($receiver));
                output($message, $invoker);
                break;

            case '!11':
                $message->reply("You will add File Upload component. Please set order with '!FileUpload:'. Ex: !FileUpload:text (The text of the form element),order (Position of this question in the form),Required:Yes/No (Optional)(Indicates whether answering this question is mandatory),Multiple:Yes/No (Optional)(Upload more than one file),Type:jpg-pdf-jpeg (Optional)(Allowed file types). You can write the optional features, but if you don't, Multiple will be No, Type will be taken as pdf, doc, docx, xls, csv, txt, rtf, html, zip, mp3, wma, mpg, flv, avi, jpg, jpeg, png, gif and Required will be accepted as No.");
                break;

            case str_starts_with($content, "!FileUpload:"):
                $invoker = new Invoker(new FileUploadCommand($receiver));
                output($message, $invoker);
                break;

            case '!Bug':
                $message->reply("You are about to submit a bug report. If you find a bug, you can send it to the system with the command !BugReport:Name:name,Surname:surname,Category:category (Forms,Tables,Reports,Approvals,Apps,Inbox),Desc:description,Link:link.");
                break;

            case str_starts_with($content, "!BugReport:"):
                $invoker = new Invoker(new BugReportCommand($receiver));
                $output = $invoker->run();
                $message->reply($output[0]);

                if ($output[0] == "Bug submission done successfully.") {
                    $guild = $discord->guilds->get('id', '992314472226099240');
                    if ($output[1] == "Tables") {
                        $channel = $guild->channels->get('id', '1001122090407170048');
                    } else if ($output[1] == "Forms") {
                        $channel = $guild->channels->get('id', '1001122055367950426');
                    } else if ($output[1] == "Approvals") {
                        $channel = $guild->channels->get('id', '1001122136494194829');
                    } else if ($output[1] == "Reports") {
                        $channel = $guild->channels->get('id', '1001122108153278534');
                    } else if ($output[1] == "Apps") {
                        $channel = $guild->channels->get('id', '1001122149978882188');
                    } else if ($output[1] == "Inbox") {
                        $channel = $guild->channels->get('id', '1001129122367545474');
                    }
                    $channel->sendMessage('Bug Report submitted. | Description: ' . $output[2] . " | Link: " . $output[3]);
                }
                break;

            case str_starts_with($content, "!Submission:"):
                $invoker = new Invoker(new SubmissionCommand($receiver));
                output($message, $invoker);
                break;

            case '!ExitSubmission':
                $invoker = new Invoker(new ExitSubmissionCommand($receiver));
                output($message, $invoker);
                break;

            case str_starts_with($content, "!Answer:"):
                $invoker = new Invoker(new AnswerQuestionCommand($receiver));
                output($message, $invoker);
                break;

            default:
                if ($message->author->username != 'Jotform Bot') {
                    $message->reply("No such command was found.");
                }
        }
    });
});

$discord->run();
