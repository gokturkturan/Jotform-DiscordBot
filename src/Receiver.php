<?php
class Receiver
{
    public function __construct($parameter, $discordID)
    {
        $this->parameter = $parameter;
        $this->discordID = $discordID;
    }

    public function insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer)
    {
        $connect = $this->database();
        $this->Update2($question['order'], $submission, $discordID, $answer);
        $order++;
        $updateOrder = mysqli_query($connect, "UPDATE users SET QOrder='$order' WHERE discordID='$discordID'");
        $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
        $row = mysqli_fetch_assoc($getTable);
        $newOrder = $row['QOrder'];
        $showQuestion = $this->form($apiKey, $submission, $newOrder, $discordID);
        return $showQuestion;
    }

    public function Update2($order, $submission, $discordID, $answer)
    {
        $connect = $this->database();
        $i = 1;
        while ($i <= 10) {
            if ($i == $order) {
                $string = "UPDATE formanswers SET Order" . $order .  "='$answer' WHERE formID='$submission' AND discordID='$discordID'";
                $insertAnswer = mysqli_query($connect, $string);
            }
            $i++;
        }
    }

    public function order($order, $submission, $discordID)
    {
        $connect = $this->database();
        $getTable = mysqli_query($connect, "SELECT * FROM formanswers WHERE discordID='$discordID' AND formID = '$submission'");
        $row = mysqli_fetch_assoc($getTable);
        $i = 1;
        while ($i <= 10) {
            if ($order == $i) {
                $string = "Order" . $i;
                $answer = $row[$string];
                return $answer;
            }
            $i++;
        }
    }

    public function form($apiKey, $formID, $order, $discordID)
    {
        $connect = $this->database();
        $jotformAPI = new JotForm("$apiKey");
        $questions = $jotformAPI->getFormQuestions("$formID");
        if ($order > count($questions)) {
            $newOrder = 1;
            $qidAnswers = array();
            while ($newOrder <= count($questions)) {
                foreach ($questions as $question) {
                    if ($question['order'] == $newOrder) {
                        $qidNumber = $question['qid'];
                        $answer = $this->order($newOrder, $formID, $discordID);
                        switch ($question['type']) {
                            case "control_fullname":
                                $str_arr = explode(",", $answer);
                                $string1 = $qidNumber . "_first";
                                $string2 = $qidNumber . "_last";
                                $qidAnswers[$string1] = $str_arr[0];
                                $qidAnswers[$string2] = $str_arr[1];
                                break;
                            case "control_address":
                                $str_arr = explode(",", $answer);
                                $string1 = $qidNumber . "_addr_line1";
                                $string2 = $qidNumber . "_addr_line2";
                                $string3 = $qidNumber . "_city";
                                $string4 = $qidNumber . "_state";
                                $string5 = $qidNumber . "_postal";
                                $qidAnswers[$string1] = $str_arr[0];
                                $qidAnswers[$string2] = $str_arr[1];
                                $qidAnswers[$string3] = $str_arr[2];
                                $qidAnswers[$string4] = $str_arr[3];
                                $qidAnswers[$string5] = $str_arr[4];
                                break;
                            case "control_phone":
                                $str_arr = explode(",", $answer);
                                if (count($str_arr) == 3) {
                                    $string1 = $qidNumber . "_country";
                                    $string2 = $qidNumber . "_area";
                                    $string3 = $qidNumber . "_phone";
                                    $qidAnswers[$string1] = $str_arr[0];
                                    $qidAnswers[$string2] = $str_arr[1];
                                    $qidAnswers[$string3] = $str_arr[2];
                                } else if (count($str_arr) == 2) {
                                    $string1 = $qidNumber . "_area";
                                    $string2 = $qidNumber . "_phone";
                                    $qidAnswers[$string1] = $str_arr[0];
                                    $qidAnswers[$string2] = $str_arr[1];
                                }
                                break;
                            case "control_textbox":
                            case "control_textarea":
                            case "control_dropdown":
                            case "control_radio":
                            case "control_number":
                            case "control_email":
                                $qidAnswers[$qidNumber] = $answer;
                                break;
                            case "control_checkbox":
                                $newAnswer = explode(',', $answer);
                                $qidAnswers[$qidNumber] = [];
                                for ($i = 0; $i < count($newAnswer); $i++) {
                                    $string = $qidNumber . sprintf("_%d", $i);
                                    $qidAnswers[$string] = $newAnswer[$i];
                                }
                                break;
                        }
                        break;
                    }
                }
                $newOrder++;
            }
            try {
                $result = $jotformAPI->createFormSubmission($formID, $qidAnswers);
                $updateTable = mysqli_query($connect, "UPDATE users SET QOrder='',submission='' WHERE discordID='$discordID'");
                return "You have answered all the questions of the form. Your answers have been submitted.";
            } catch (Exception $e) {
                return "Your API Key is wrong.";
            }
        } else {
            foreach ($questions as $question) {
                if ($question['order'] == $order) {
                    if ($question['type'] == "control_fullname") {
                        if ($question['required'] == 'Yes') {
                            return "Question:" . $question['text'] . " | Required: " . $question['required'] . " | Answer using the !Answer:FirstName,LastName command.";
                        } else if ($question['required'] == 'No') {
                            return "Question: " . $question['text'] . " | Required: No | Answer using the !Answer:FirstName,LastName command.";
                        }
                    } else if ($question['type'] == "control_email") {
                        if ($question['required'] == 'Yes') {
                            return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | Answer using the !Answer:Email command.";
                        } else if ($question['required'] == 'No') {
                            return "Question: " . $question['text'] . " | Required: No | Answer using the !Answer:Email command.";
                        }
                    } else if ($question['type'] == "control_address") {
                        if ($question['required'] == 'Yes') {
                            return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | Answer using the !Answer:Street Address,Street Address Line 2,City,State/Province,Posta / Zip Code command.";
                        } else if ($question['required'] == 'No') {
                            return "Question: " . $question['text'] . " | Required: No | Answer using the !Answer:Street Address,Street Address Line 2,City,State/Province,Posta / Zip Code command.";
                        }
                    } else if ($question['type'] == "control_phone") {
                        if ($question['required'] == 'Yes') {
                            if ($question['countryCode'] == 'Yes') {
                                return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | CountryCode: " . $question['countryCode'] . " | Answer using the !Answer:CountryCode(1-3 digits),AreaCode(1-3 digits),Phone(7 digits) command.";
                            } else if ($question['countryCode'] == 'No') {
                                return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | CountryCode: No | Answer using the !Answer:AreaCode(1-3 digits),Phone(7 digits) command.";
                            }
                        } else if ($question['required'] == 'No') {
                            if ($question['countryCode'] == 'Yes') {
                                return "Question: " . $question['text'] . " | Required: No | CountryCode: " . $question['countryCode'] . " | Answer using the !Answer:CountryCode(1-3 digits),AreaCode(1-3 digits),Phone(7 digits) command.";
                            } else if ($question['countryCode'] == 'No') {
                                return "Question: " . $question['text'] . " | Required: No | CountryCode: No | Answer using the !Answer:AreaCode(1-3 digits),Phone(7 digits) command.";
                            }
                        }
                    } else if ($question['type'] == "control_textbox") {
                        if ($question['required'] == 'Yes') {
                            if (array_key_exists("maxsize", $question)) {
                                return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | MaxSize: " . $question['maxsize'] . " | Answer using the !Answer:ShortText command.";
                            } else {
                                return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | MaxSize: Limitless | Answer using the !Answer:ShortText command.";
                            }
                        } else if ($question['required'] == 'No') {
                            if (array_key_exists("maxsize", $question)) {
                                return "Question: " . $question['text'] . " | Required: No | MaxSize: " . $question['maxsize'] . " | Answer using the !Answer:ShortText command.";
                            } else {
                                return "Question: " . $question['text'] . " | Required: No | MaxSize: No | Answer using the !Answer:ShortText command.";
                            }
                        }
                    } else if ($question['type'] == "control_textarea") {
                        if ($question['required'] == 'Yes') {
                            if (array_key_exists("entryLimit", $question)) {
                                return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | EntryLimit: " . $question['entryLimit'] . " | Answer using the !Answer:LongText command.";
                            } else {
                                return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | EntryLimit: Limitless | Answer using the !Answer:LongText command.";
                            }
                        } else if ($question['required'] == 'No') {
                            if (array_key_exists("entryLimit", $question)) {
                                return "Question: " . $question['text'] . " | Required: No | EntryLimit: " . $question['entryLimit'] . " | Answer using the !Answer:LongText command.";
                            } else {
                                return "Question: " . $question['text'] . " | Required: No | EntryLimit: Limitless | Answer using the !Answer:LongText command.";
                            }
                        }
                    } else if ($question['type'] == "control_dropdown" || $question['type'] == "control_radio") {
                        if ($question['required'] == 'Yes') {
                            if (array_key_exists("special", $question)) {
                                return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | Special: " . $question['special'] . " | Answer using the !Answer:Answer command.";
                            } else if (array_key_exists("options", $question)) {
                                return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | Options: " . $question['options'] . " | Answer using the !Answer:Option command.";
                            }
                        } else if ($question['required'] == 'No') {
                            if (array_key_exists("special", $question)) {
                                return "Question: " . $question['text'] . " | Required: No | Special: " . $question['special'] . " | Answer using the !Answer:Answer command.";
                            } else if (array_key_exists("options", $question)) {
                                return "Question: " . $question['text'] . " | Required: No | Options: " . $question['options'] . " | Answer using the !Answer:Option command.";
                            }
                        }
                    } else if ($question['type'] == "control_checkbox") {
                        if ($question['required'] == 'Yes') {
                            if (array_key_exists("special", $question)) {
                                return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | Special: " . $question['special'] . " | Answer using the !Answer:Answers command (Seperate answers with comma).";
                            } else if (array_key_exists("options", $question)) {
                                return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | Options: " . $question['options'] . " | Answer using the !Answer:Options command (Seperate answers with comma).";
                            }
                        } else if ($question['required'] == 'No') {
                            if (array_key_exists("special", $question)) {
                                return "Question: " . $question['text'] . " | Required: No | Special: " . $question['special'] . " | Answer using the !Answer:Answers command (Seperate answers with comma).";
                            } else if (array_key_exists("options", $question)) {
                                return "Question: " . $question['text'] . " | Required: No | Options: " . $question['options'] . " | Answer using the !Answer:Options command (Seperate answers with comma).";
                            }
                        }
                    } else if ($question['type'] == "control_number") {
                        if ($question['required'] == 'Yes') {
                            if (array_key_exists("minValue", $question)) {
                                if (array_key_exists("maxValue", $question)) {
                                    return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | Min: " . $question['minValue'] . " | Max: " . $question['maxValue'] . " | Answer using the !Answer:Number command.";
                                } else {
                                    return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | Min: " . $question['minValue'] . " | Max: Limitless | Answer using the !Answer:Number command.";
                                }
                            } else {
                                if (array_key_exists("maxValue", $question)) {
                                    return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | Min: Limitless | Max: " . $question['maxValue'] . " | Answer using the !Answer:Number command.";
                                } else {
                                    return "Question: " . $question['text'] . " | Required: " . $question['required'] . " | Min: Limitless | Max: Limitless | Answer using the !Answer:Number command.";
                                }
                            }
                        } else if ($question['required'] == 'No') {
                            if (array_key_exists("minValue", $question)) {
                                if (array_key_exists("maxValue", $question)) {
                                    return "Question: " . $question['text'] . " | Required: No | Min: " . $question['minValue'] . " | Max: " . $question['maxValue'] . " | Answer using the !Answer:Number command.";
                                } else {
                                    return "Question: " . $question['text'] . " | Required: No | Min: " . $question['minValue'] . " | Max: Limitless | Answer using the !Answer:Number command.";
                                }
                            } else {
                                if (array_key_exists("maxValue", $question)) {
                                    return "Question: " . $question['text'] . " | Required: No | Min: Limitless | Max: " . $question['maxValue'] . " | Answer using the !Answer:Number command.";
                                } else {
                                    return "Question: " . $question['text'] . " | Required: No | Min: Limitless | Max: Limitless | Answer using the !Answer:Number command.";
                                }
                            }
                        }
                    }
                    break;
                }
            }
        }
    }

    public function database()
    {
        $host = "localhost";
        $user = "root";
        $pass = "";
        $db   = "discordbot";
        $connect = mysqli_connect($host, $user, $pass);
        mysqli_select_db($connect, $db);
        return $connect;
    }

    public function registerInfo()
    {
        $content = $this->parameter;
        $string = str_replace("!RegisterInfo:", "", $content);
        $apikey = $string;
        $discordID = $this->discordID;

        if (empty($apikey)) {
            return "Please enter the command with your api key.";
        } else {

            $connect = $this->database();

            if ($connect) {
                $apikey = mysqli_real_escape_string($connect, $string);
                $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
                $row = mysqli_fetch_assoc($getTable);
                if (mysqli_num_rows($getTable) == 0) {
                    $addTable = mysqli_query($connect, "INSERT INTO users (discordID,apiKey) VALUES('$discordID','$apikey')");

                    if ($addTable) {
                        return "You have successfully registered in the system. You can create a form (!CreateForm:formName (optional) if you don't write formName, your form will be named 'New Form') or you can select an existing form and add an element to it (!SelectForm:formID).";
                    } else {
                        return "You have not successfully registered in the system.";
                    }
                } else if (mysqli_num_rows($getTable) == 1 && $apikey != $row['apiKey']) {
                    return "You already have an apikey registered in the system. If you do not have access to the old api key, please enter your new api key with the !NewApiKey command. Ex: !NewApiKey:df9df94df515";
                } else {
                    return "You are already registered in the system. You can create a form (!CreateForm:formName (optional) if you don't write formName, your form will be named 'New Form') or you can select an existing form and add an element to it (!SelectForm:formID).";
                }
            } else {
                return "Database connection failed.";
            }
        }
    }

    public function updateApiKey()
    {
        $content = $this->parameter;
        $string = str_replace("!NewApiKey:", "", $content);
        $apikey = $string;
        $discordID = $this->discordID;

        if (empty($apikey)) {
            return "Please enter the command with your api key.";
        } else {

            $connect = $this->database();

            if ($connect) {
                $apikey = mysqli_real_escape_string($connect, $string);
                $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
                $row = mysqli_fetch_assoc($getTable);
                if (mysqli_num_rows($getTable) == 0) {
                    return "You are not registered to the system, so you cannot update apikey. Please register to the system with !Register.";
                } else if (mysqli_num_rows($getTable) == 1 && $apikey == $row['apiKey']) {
                    return "You have entered the api key registered in the system.";
                } else {
                    $updateApi = mysqli_query($connect, "UPDATE users SET apiKey='$apikey' WHERE discordID='$discordID'");

                    if ($updateApi) {
                        return "Your Api Key has been updated with a new one.";
                    } else {
                        return "Your Api Key could not be updated.";
                    }
                }
            } else {
                return "Database connection failed.";
            }
        }
    }

    public function createForm()
    {
        $content = $this->parameter;
        $formName = str_replace("!CreateForm:", "", $content);

        if (empty($formName)) {
            $discordID = $this->discordID;

            $connect = $this->database();

            if ($connect) {

                $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
                $row = mysqli_fetch_assoc($getTable);
                $apiKey = $row['apiKey'];

                if (mysqli_num_rows($getTable) == 0) {
                    return "In order to create a form, you must be registered in the system. Switch to the registration section using the !Register command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);
                    $form = array(
                        'questions' => array(),
                        'properties' => array(
                            'title' => 'New Form',
                            'height' => '539',
                        ),
                        'emails' => array(
                            array(
                                'type' => 'notification',
                                'name' => 'notification',
                                'from' => 'default',
                                'to' => 'noreply@jotform.com',
                                'subject' => 'New Submission',
                                'html' => 'false'
                            ),
                        ),
                    );

                    try {
                        $response = $jotformAPI->createForm($form);
                        $formId = $response['id'];
                        $addTable = mysqli_query($connect, "INSERT INTO forms (formID,apiKey) VALUES ('$formId','$apiKey')");

                        if ($addTable) {
                            return "Your form created successfully. This is your form id: $formId. If you want to add new form elements enter !InfoFormElements";
                        }
                    } catch (Exception $e) {
                        return "Your form did not created, your API key is wrong. Please update your API key with !NewApiKey:apiKey.";
                    }
                }
            } else {
                return "Database connection failed.";
            }
        } else {
            $discordID = $this->discordID;
            $connect = $this->database();

            if ($connect) {
                $formNameNew = mysqli_real_escape_string($connect, $formName);
                $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
                $row = mysqli_fetch_assoc($getTable);
                $apiKey = $row['apiKey'];

                if (mysqli_num_rows($getTable) == 0) {
                    return "In order to create a form, you must be registered in the system. Switch to the registration section using the !Register command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);
                    $form = array(
                        'questions' => array(),
                        'properties' => array(
                            'title' => $formNameNew,
                            'height' => '539',
                        ),
                        'emails' => array(
                            array(
                                'type' => 'notification',
                                'name' => 'notification',
                                'from' => 'default',
                                'to' => 'noreply@jotform.com',
                                'subject' => 'New Submission',
                                'html' => 'false'
                            ),
                        ),
                    );

                    try {
                        $response = $jotformAPI->createForm($form);
                        $formId = $response['id'];
                        $addTable = mysqli_query($connect, "INSERT INTO forms (formID,apiKey) VALUES ('$formId','$apiKey')");

                        if ($addTable) {
                            return "Your form created successfully. This is your form id: $formId. If you want to add new form elements enter !InfoFormElements";
                        }
                    } catch (Exception $e) {
                        return "Your form did not created, your API key is wrong. Please update your API key with !NewApiKey:apiKey.";
                    }
                }
            } else {
                return "Database connection failed.";
            }
        }
    }

    public function myForms()
    {
        $discordID = $this->discordID;
        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to show all your forms, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                $jotformAPI = new JotForm($apiKey);
                try {
                    $forms = $jotformAPI->getForms();
                    $size = count($forms);
                    $i = 0;
                    $myForms = [];
                    $count = 0;
                    while ($i < $size) {
                        if ($forms[$i]['status'] == "ENABLED") {
                            $count++;
                            $myform = [$forms[$i]['id'], $forms[$i]['title']];
                            array_push($myForms, $myform);
                        }
                        $i++;
                    }
                    return $myForms;
                } catch (Exception $e) {
                    return "Your API Key is wrong.";
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function selectForm()
    {
        $content = $this->parameter;
        $discordID = $this->discordID;
        $formId = str_replace("!SelectForm:", "", $content);

        if (empty($formId)) {
            return "Please write the form ID along with the command.";
        } else {
            $connect = $this->database();

            if ($connect) {
                $formIdNew = mysqli_real_escape_string($connect, $formId);
                $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
                $row = mysqli_fetch_assoc($getTable);
                $apiKey = $row['apiKey'];

                if (mysqli_num_rows($getTable) == 0) {
                    return "In order to select form, you must be registered in the system. Switch to the registration section using the !Register command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    try {
                        $form = $jotformAPI->getForm($formIdNew);
                        if ($form['status'] == "ENABLED") {
                            $updateFormID = mysqli_query($connect, "UPDATE users SET formID='$formIdNew' WHERE discordID='$discordID'");
                            return "The form has been selected successfully. You can add elements to the form you have selected. !InfoFormElements";
                        } else if ($form['status'] == "DELETED") {
                            return "This form has already been deleted.";
                        } else {
                            return "No such form was found.";
                        }
                    } catch (Exception $e) {
                        return "Your API Key is wrong.";
                    }
                }
            } else {
                return "Database connection failed.";
            }
        }
    }

    public function addFullName()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $fullNameInfo = str_replace("!FullName:", "", $content);
        $str_arr = explode(",", $fullNameInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Full Name component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {

                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);
                    if (count($str_arr) == 2) {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        $question = array(
                            'type' => 'control_fullname',
                            'text' => $text,
                            'order' => $order,
                            'required' => 'No'
                        );

                        try {
                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                            return "Full Name component added successfully.";
                        } catch (Exception $e) {
                            return "Your API key is wrong!";
                        }
                    } else if (count($str_arr) == 3) {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));

                                $question = array(
                                    'type' => 'control_fullname',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => $requiredNew
                                );

                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "Full Name component added successfully with Required.";
                                } catch (Exception $e) {
                                    return "Your API key is wrong!";
                                }
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                        } else {
                            return "You entered wrong component!";
                        }
                    } else {
                        return "Please do not enter missing or excess components.";
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addEmail()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $emailInfo = str_replace("!Email:", "", $content);
        $str_arr = explode(",", $emailInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Email component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    if (count($str_arr) == 2) {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        $question = array(
                            'type' => 'control_email',
                            'text' => $text,
                            'order' => $order,
                            'required' => 'No'
                        );

                        try {
                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                            return "Email component added successfully.";
                        } catch (Exception $e) {
                            return "Your API Key is wrong!";
                        }
                    } else if (count($str_arr) == 3) {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));

                                $question = array(
                                    'type' => 'control_email',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => $requiredNew
                                );

                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "Email component added successfully with Required.";
                                } catch (Exception $e) {
                                    return "Your API Key is wrong!";
                                }
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                        } else {
                            return "You entered wrong component!";
                        }
                    } else {
                        return "Please do not enter missing or excess components.";
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addAddress()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $addressInfo = str_replace("!Address:", "", $content);
        $str_arr = explode(",", $addressInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Email component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    if (count($str_arr) == 2) {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        $question = array(
                            'type' => 'control_address',
                            'text' => $text,
                            'order' => $order,
                            'required' => 'No'
                        );

                        try {
                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                            return "Address component added successfully.";
                        } catch (Exception $e) {
                            return "Your API Key is wrong!";
                        }
                    } else if (count($str_arr) == 3) {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));

                                $question = array(
                                    'type' => 'control_address',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => $requiredNew
                                );

                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "Address component added successfully with Required.";
                                } catch (Exception $e) {
                                    return "Your API Key is wrong!";
                                }
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                        } else {
                            return "You entered wrong component!";
                        }
                    } else {
                        return "Please do not enter missing or excess components.";
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addPhone()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $phoneInfo = str_replace("!Phone:", "", $content);
        $str_arr = explode(",", $phoneInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Email component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    if (count($str_arr) < 2 || count($str_arr) > 4) {
                        return "Please do not enter missing or excess components.";
                    } else if (count($str_arr) > 2) {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                            if (!empty($str_arr[3])) {
                                if (str_starts_with($str_arr[3], "CountryCode:")) {
                                    $countryCode = str_replace("CountryCode:", "", $str_arr[3]);
                                    if (ucfirst(strtolower($countryCode)) == 'Yes' || ucfirst(strtolower($countryCode)) == 'No') {
                                        $countryCodeNew = ucfirst(strtolower($countryCode));

                                        $question = array(
                                            'type' => 'control_phone',
                                            'text' => $text,
                                            'order' => $order,
                                            'required' => $requiredNew,
                                            'countryCode' => $countryCodeNew
                                        );

                                        try {
                                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                            return "Phone component added successfully with Required and CountryCode.";
                                        } catch (Exception $e) {
                                            return "Your API Key is wrong.";
                                        }
                                    } else {
                                        return "Please enter YES or No for countryCode component.";
                                    }
                                } else {
                                    return "You entered wrong component!";
                                }
                            } else {
                                $question = array(
                                    'type' => 'control_phone',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => $requiredNew,
                                    'countryCode' => 'No'
                                );

                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "Phone component added successfully with Required.";
                                } catch (Exception $e) {
                                    return "Your API Key is wrong.";
                                }
                            }
                        } else if (str_starts_with($str_arr[2], "CountryCode:")) {
                            $countryCode = str_replace("CountryCode:", "", $str_arr[2]);
                            if (ucfirst(strtolower($countryCode)) == 'Yes' || ucfirst(strtolower($countryCode)) == 'No') {
                                $countryCodeNew = ucfirst(strtolower($countryCode));

                                $question = array(
                                    'type' => 'control_phone',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => 'No',
                                    'countryCode' => $countryCodeNew
                                );

                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "Phone component added successfully with CountryCode.";
                                } catch (Exception $e) {
                                    return "Your API Key is wrong.";
                                }
                            } else {
                                return "Please enter YES or No for countryCode component.";
                            }
                        } else {
                            return "You entered wrong component!";
                        }
                    } else {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        $question = array(
                            'type' => 'control_phone',
                            'text' => $text,
                            'order' => $order,
                            'required' => 'No',
                            'countryCode' => 'No'
                        );

                        try {
                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                            return "Phone component added successfully.";
                        } catch (Exception $e) {
                            return "Your API Key is wrong.";
                        }
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addShortText()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $shortTextInfo = str_replace("!ShortText:", "", $content);
        $str_arr = explode(",", $shortTextInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Email component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    if (count($str_arr) < 2 || count($str_arr) > 4) {
                        return "Please do not enter missing or excess components.";
                    } else if (count($str_arr) > 2) {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                            if (!empty($str_arr[3])) {
                                if (is_numeric($str_arr[3])) {
                                    $maxsize = $str_arr[3];

                                    $question = array(
                                        'type' => 'control_textbox',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => $requiredNew,
                                        'maxsize' => $maxsize
                                    );

                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Short Text component added successfully with Required and Maxsize.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else {
                                    return "You should enter number for Maxsize component!";
                                }
                            } else {
                                $question = array(
                                    'type' => 'control_textbox',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => $requiredNew
                                );

                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "Short Text component added successfully with Required.";
                                } catch (Exception $e) {
                                    return "Your API Key is wrong.";
                                }
                            }
                        } else if (is_numeric($str_arr[2])) {
                            $maxsize = $str_arr[2];

                            $question = array(
                                'type' => 'control_textbox',
                                'text' => $text,
                                'order' => $order,
                                'required' => 'No',
                                'maxsize' => $maxsize
                            );

                            try {
                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                return "Short Text component added successfully with Maxsize.";
                            } catch (Exception $e) {
                                return "Your API Key is wrong.";
                            }
                        } else {
                            return "You entered wrong component!";
                        }
                    } else {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        $question = array(
                            'type' => 'control_textbox',
                            'text' => $text,
                            'required' => 'No',
                            'order' => $order
                        );

                        try {
                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                            return "Short Text component added successfully.";
                        } catch (Exception $e) {
                            return "Your API Key is wrong.";
                        }
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addLongText()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $longTextInfo = str_replace("!LongText:", "", $content);
        $str_arr = explode(",", $longTextInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Email component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    if (count($str_arr) < 2 || count($str_arr) > 4) {
                        return "Please do not enter missing or excess components.";
                    } else if (count($str_arr) > 2) {

                        $text = $str_arr[0];
                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                            if (!empty($str_arr[3])) {
                                $entryLimit = ucfirst(strtolower($str_arr[3]));
                                if (str_starts_with($entryLimit, "Words-") || str_starts_with($entryLimit, "Letters-")) {
                                    $number = explode("-", $entryLimit);
                                    if (is_numeric($number[1])) {
                                        $question = array(
                                            'type' => 'control_textarea',
                                            'text' => $text,
                                            'order' => $order,
                                            'required' => $requiredNew,
                                            'entryLimit' => $entryLimit
                                        );

                                        try {
                                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                            return "Long Text component added successfully with Required and EntryLimit.";
                                        } catch (Exception $e) {
                                            return "Your API Key is wrong.";
                                        }
                                    } else {
                                        return "You should enter number for Entry Limit component.";
                                    }
                                } else {
                                    return "You entered wrong component!";
                                }
                            } else {
                                $question = array(
                                    'type' => 'control_textarea',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => $requiredNew
                                );

                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "Long Text component added successfully with Required.";
                                } catch (Exception $e) {
                                    return "Your API Key is wrong.";
                                }
                            }
                        } else if (str_starts_with(ucfirst(strtolower($str_arr[2])), "Words-") || str_starts_with(ucfirst(strtolower($str_arr[2])), "Letters-")) {
                            $entryLimit = ucfirst(strtolower($str_arr[2]));

                            $number = explode("-", $entryLimit);
                            if (is_numeric($number[1])) {
                                $question = array(
                                    'type' => 'control_textarea',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => 'No',
                                    'entryLimit' => $entryLimit
                                );

                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "Long Text component added successfully with EntryLimit.";
                                } catch (Exception $e) {
                                    return "Your API Key is wrong.";
                                }
                            } else {
                                return "You should enter number for Entry Limit component.";
                            }
                        } else {
                            return "You entered wrong component!";
                        }
                    } else {

                        $text = $str_arr[0];
                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        $question = array(
                            'type' => 'control_textbox',
                            'text' => $text,
                            'required' => 'No',
                            'order' => $order
                        );

                        try {
                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                            return "Long Text component added successfully.";
                        } catch (Exception $e) {
                            return "Your API Key is wrong.";
                        }
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addDropdown()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $dropdownInfo = str_replace("!Dropdown:", "", $content);
        $str_arr = explode(",", $dropdownInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Email component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    if (count($str_arr) < 3 || count($str_arr) > 4) {
                        return "Please do not enter missing or excess components.";
                    } else if (count($str_arr) >= 3) {

                        $text = $str_arr[0];
                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }

                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                            if (!empty($str_arr[3])) {
                                if (strpos($str_arr[3], "-")) {
                                    $options = str_replace("-", "|", $str_arr[3]);

                                    $question = array(
                                        'type' => 'control_dropdown',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => $requiredNew,
                                        'options' => $options
                                    );

                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Dropdown component added successfully with options with Required and Options.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else if (is_numeric($str_arr[3])) {
                                    $special = $str_arr[3];
                                    switch ($special) {
                                        case '1':
                                            $special = 'Countries';
                                            break;
                                        case '2':
                                            $special = 'US States';
                                            break;
                                        case '3':
                                            $special = 'US States Abbr';
                                            break;
                                        case '4':
                                            $special = 'Months';
                                            break;
                                        case '5':
                                            $special = 'Gender';
                                            break;
                                        case '6':
                                            $special = 'Last 100 Years';
                                            break;
                                        case '7':
                                            $special = 'Days';
                                            break;
                                        default:
                                            return "You have to choose between 1-7 for pre-defined values. Look at predefined values from !8";
                                    }

                                    $question = array(
                                        'type' => 'control_dropdown',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => $requiredNew,
                                        'special' => $special
                                    );
                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Dropdown component added successfully with Required and Special.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else {
                                    return "You entered wrong component for Special/Optional component.";
                                }
                            } else {
                                return "Please enter option or special components.";
                            }
                        } else if (strpos($str_arr[2], "-")) {

                            if (count($str_arr) == 4) {
                                return "Please enter just one of them (special or options)";
                            }
                            $options = str_replace("-", "|", $str_arr[2]);

                            $question = array(
                                'type' => 'control_dropdown',
                                'text' => $text,
                                'order' => $order,
                                'required' => 'No',
                                'options' => $options
                            );

                            try {
                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                return "Dropdown component added successfully with Options.";
                            } catch (Exception $e) {
                                return "Your API Key is wrong.";
                            }
                        } else if (is_numeric($str_arr[2])) {
                            if (count($str_arr) == 4) {
                                return "Please enter just one of them (special or options)";
                            }
                            $special = $str_arr[2];
                            switch ($special) {
                                case '1':
                                    $special = 'Countries';
                                    break;
                                case '2':
                                    $special = 'US States';
                                    break;
                                case '3':
                                    $special = 'US States Abbr';
                                    break;
                                case '4':
                                    $special = 'Months';
                                    break;
                                case '5':
                                    $special = 'Gender';
                                    break;
                                case '6':
                                    $special = 'Last 100 Years';
                                    break;
                                case '7':
                                    $special = 'Days';
                                    break;
                                default:
                                    return "You have to choose between 1-7 for pre-defined values. Look at predefined values from !8";
                            }

                            $question = array(
                                'type' => 'control_dropdown',
                                'text' => $text,
                                'order' => $order,
                                'required' => 'No',
                                'special' => $special
                            );
                            try {
                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                return "Dropdown component added successfully with Special.";
                            } catch (Exception $e) {
                                return "Your API Key is wrong.";
                            }
                        } else if (count($str_arr) == 4) {
                            return "You entered wrong component!";
                        } else {
                            return "You entered wrong component!";
                        }
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addSingleChoice()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $singleChoiceInfo = str_replace("!SingleChoice:", "", $content);
        $str_arr = explode(",", $singleChoiceInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Email component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    if (count($str_arr) < 3 || count($str_arr) > 5) {
                        return "Please do not enter missing or excess components.";
                    } else if (count($str_arr) >= 3) {

                        $text = $str_arr[0];
                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }
                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                            if (!empty($str_arr[3])) {
                                if (str_starts_with($str_arr[3], "Allow:")) {
                                    $allow = str_replace("Allow:", "", $str_arr[3]);
                                    if (ucfirst(strtolower($allow)) == 'Yes' || ucfirst(strtolower($allow)) == 'No') {
                                        $allowNew = ucfirst(strtolower($allow));
                                    } else {
                                        return "Please enter YES or No for allow component.";
                                    }
                                    if (!empty($str_arr[4])) {
                                        if (is_numeric($str_arr[4])) {
                                            $special = $str_arr[4];
                                            switch ($special) {
                                                case '1':
                                                    $special = 'Gender';
                                                    break;
                                                case '2':
                                                    $special = 'Days';
                                                    break;
                                                case '3':
                                                    $special = 'Months';
                                                    break;
                                                default:
                                                    return "You have to choose between 1-3 for pre-defined values. Look at predefined values from !9";
                                            }

                                            $question = array(
                                                'type' => 'control_radio',
                                                'text' => $text,
                                                'order' => $order,
                                                'required' => $requiredNew,
                                                'allowOther' => $allowNew,
                                                'special' => $special
                                            );
                                            try {
                                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                                return "Single Choice component added successfully with Required, Allow and Special.";
                                            } catch (Exception $e) {
                                                return "Your API Key is wrong.";
                                            }
                                        } else if (strpos($str_arr[4], "-")) {
                                            $options = str_replace("-", "|", $str_arr[4]);

                                            $question = array(
                                                'type' => 'control_radio',
                                                'text' => $text,
                                                'order' => $order,
                                                'required' => $requiredNew,
                                                'allowOther' => $allowNew,
                                                'options' => $options
                                            );

                                            try {
                                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                                return "Single Choice component added successfully with Required, Allow and Options.";
                                            } catch (Exception $e) {
                                                return "Your API Key is wrong.";
                                            }
                                        } else {
                                            return "You entered wrong component for Special/Optional component.";
                                        }
                                    } else {
                                        return "Please enter option or special components.";
                                    }
                                } else if (is_numeric($str_arr[3])) {
                                    if (count($str_arr) == 5) {
                                        return "Please enter just one of them (special or options)";
                                    }
                                    $special = $str_arr[3];
                                    switch ($special) {
                                        case '1':
                                            $special = 'Gender';
                                            break;
                                        case '2':
                                            $special = 'Days';
                                            break;
                                        case '3':
                                            $special = 'Months';
                                            break;
                                        default:
                                            return "You have to choose between 1-3 for pre-defined values. Look at predefined values from !9";
                                    }

                                    $question = array(
                                        'type' => 'control_radio',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => $requiredNew,
                                        'special' => $special
                                    );
                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Single Choice component added successfully with Required and Special.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else if (strpos($str_arr[3], "-")) {
                                    if (count($str_arr) == 5) {
                                        return "Please enter just one of them (special or options)";
                                    }
                                    $options = str_replace("-", "|", $str_arr[3]);

                                    $question = array(
                                        'type' => 'control_radio',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => $requiredNew,
                                        'options' => $options
                                    );

                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Single Choice component added successfully with Required and Options.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else if (count($str_arr) == 6) {
                                    return "You entered wrong component!";
                                } else {
                                    return "You entered wrong component!";
                                }
                            } else {
                                return "Please enter option or special components.";
                            }
                        } else if (str_starts_with($str_arr[2], "Allow:")) {
                            $allow = str_replace("Allow:", "", $str_arr[2]);
                            if (ucfirst(strtolower($allow)) == 'Yes' || ucfirst(strtolower($allow)) == 'No') {
                                $allowNew = ucfirst(strtolower($allow));
                            } else {
                                return "Please enter YES or No for allow component.";
                            }
                            if (!empty($str_arr[3])) {
                                if (count($str_arr) == 5) {
                                    return "Please enter just one of them (special or options)";
                                } else if (is_numeric($str_arr[3])) {
                                    $special = $str_arr[3];
                                    switch ($special) {
                                        case '1':
                                            $special = 'Gender';
                                            break;
                                        case '2':
                                            $special = 'Days';
                                            break;
                                        case '3':
                                            $special = 'Months';
                                            break;
                                        default:
                                            return "You have to choose between 1-3 for pre-defined values. Look at predefined values from !9";
                                    }

                                    $question = array(
                                        'type' => 'control_radio',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => 'No',
                                        'allowOther' => $allowNew,
                                        'special' => $special
                                    );
                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Single Choice component added successfully with Allow and Special.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else if (strpos($str_arr[3], "-")) {
                                    $options = str_replace("-", "|", $str_arr[3]);

                                    $question = array(
                                        'type' => 'control_radio',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => 'No',
                                        'allowOther' => $allowNew,
                                        'options' => $options
                                    );

                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Single Choice component added successfully with Allow and Options.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else {
                                    return "You entered wrong component for Special/Optional component.";
                                }
                            } else {
                                return "Please enter option or special components.";
                            }
                        } else if (is_numeric($str_arr[2])) {
                            if (count($str_arr) == 4) {
                                return "Please enter just one of them (special or options)";
                            }
                            $special = $str_arr[2];
                            switch ($special) {
                                case '1':
                                    $special = 'Gender';
                                    break;
                                case '2':
                                    $special = 'Days';
                                    break;
                                case '3':
                                    $special = 'Months';
                                    break;
                                default:
                                    return "You have to choose between 1-3 for pre-defined values. Look at predefined values from !9";
                            }

                            $question = array(
                                'type' => 'control_radio',
                                'text' => $text,
                                'order' => $order,
                                'required' => 'No',
                                'special' => $special
                            );
                            try {
                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                return "Single Choice component added successfully with Special.";
                            } catch (Exception $e) {
                                return "Your API Key is wrong.";
                            }
                        } else if (strpos($str_arr[2], "-")) {
                            if (count($str_arr) == 4) {
                                return "Please enter just one of them (special or options)";
                            }
                            $options = str_replace("-", "|", $str_arr[2]);

                            $question = array(
                                'type' => 'control_radio',
                                'text' => $text,
                                'order' => $order,
                                'required' => 'No',
                                'options' => $options
                            );

                            try {
                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                return "Single Choice component added successfully with Options.";
                            } catch (Exception $e) {
                                return "Your API Key is wrong.";
                            }
                        } else if (count($str_arr) == 4) {
                            return "You entered wrong component!";
                        } else {
                            return "Please enter option or special components.";
                        }
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addMultipleChoice()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $multipleChoiceInfo = str_replace("!MultipleChoice:", "", $content);
        $str_arr = explode(",", $multipleChoiceInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Email component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    if (count($str_arr) < 3 || count($str_arr) > 5) {
                        return "Please do not enter missing or excess components.";
                    } else if (count($str_arr) >= 3) {

                        $text = $str_arr[0];
                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }
                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                            if (!empty($str_arr[3])) {
                                if (str_starts_with($str_arr[3], "Allow:")) {
                                    $allow = str_replace("Allow:", "", $str_arr[3]);
                                    if (ucfirst(strtolower($allow)) == 'Yes' || ucfirst(strtolower($allow)) == 'No') {
                                        $allowNew = ucfirst(strtolower($allow));
                                    } else {
                                        return "Please enter YES or No for allow component.";
                                    }
                                    if (!empty($str_arr[4])) {
                                        if (is_numeric($str_arr[4])) {
                                            $special = $str_arr[4];
                                            switch ($special) {
                                                case '1':
                                                    $special = 'Gender';
                                                    break;
                                                case '2':
                                                    $special = 'Days';
                                                    break;
                                                case '3':
                                                    $special = 'Months';
                                                    break;
                                                default:
                                                    return "You have to choose between 1-3 for pre-defined values. Look at predefined values from !10";
                                            }

                                            $question = array(
                                                'type' => 'control_checkbox',
                                                'text' => $text,
                                                'order' => $order,
                                                'required' => $requiredNew,
                                                'allowOther' => $allowNew,
                                                'special' => $special
                                            );
                                            try {
                                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                                return "Multiple Choice component added successfully with Required, Allow and Special.";
                                            } catch (Exception $e) {
                                                return "Your API Key is wrong.";
                                            }
                                        } else if (strpos($str_arr[4], "-")) {
                                            $options = str_replace("-", "|", $str_arr[4]);

                                            $question = array(
                                                'type' => 'control_checkbox',
                                                'text' => $text,
                                                'order' => $order,
                                                'required' => $requiredNew,
                                                'allowOther' => $allowNew,
                                                'options' => $options
                                            );

                                            try {
                                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                                return "Multiple Choice component added successfully with Required, Allow and Options.";
                                            } catch (Exception $e) {
                                                return "Your API Key is wrong.";
                                            }
                                        } else {
                                            return "You entered wrong component for Special/Optional component.";
                                        }
                                    } else {
                                        return "Please enter option or special components.";
                                    }
                                } else if (is_numeric($str_arr[3])) {
                                    if (count($str_arr) == 5) {
                                        return "Please enter just one of them (special or options)";
                                    }
                                    $special = $str_arr[3];
                                    switch ($special) {
                                        case '1':
                                            $special = 'Gender';
                                            break;
                                        case '2':
                                            $special = 'Days';
                                            break;
                                        case '3':
                                            $special = 'Months';
                                            break;
                                        default:
                                            return "You have to choose between 1-3 for pre-defined values. Look at predefined values from !10";
                                    }

                                    $question = array(
                                        'type' => 'control_checkbox',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => $requiredNew,
                                        'special' => $special
                                    );
                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Multiple Choice component added successfully with Required and Special.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else if (strpos($str_arr[3], "-")) {
                                    if (count($str_arr) == 5) {
                                        return "Please enter just one of them (special or options)";
                                    }
                                    $options = str_replace("-", "|", $str_arr[3]);

                                    $question = array(
                                        'type' => 'control_checkbox',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => $requiredNew,
                                        'options' => $options
                                    );

                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Multiple Choice component added successfully with Required and Options.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else if (count($str_arr) == 5) {
                                    return "You entered wrong component!";
                                } else {
                                    return "You entered wrong component!";
                                }
                            } else {
                                return "Please enter option or special components.";
                            }
                        } else if (str_starts_with($str_arr[2], "Allow:")) {
                            $allow = str_replace("Allow:", "", $str_arr[2]);
                            if (ucfirst(strtolower($allow)) == 'Yes' || ucfirst(strtolower($allow)) == 'No') {
                                $allowNew = ucfirst(strtolower($allow));
                            } else {
                                return "Please enter YES or No for allow component.";
                            }
                            if (!empty($str_arr[3])) {
                                if (count($str_arr) == 5) {
                                    return "Please enter just one of them (special or options)";
                                } else if (is_numeric($str_arr[3])) {
                                    $special = $str_arr[3];
                                    switch ($special) {
                                        case '1':
                                            $special = 'Gender';
                                            break;
                                        case '2':
                                            $special = 'Days';
                                            break;
                                        case '3':
                                            $special = 'Months';
                                            break;
                                        default:
                                            return "You have to choose between 1-3 for pre-defined values. Look at predefined values from !10";
                                    }

                                    $question = array(
                                        'type' => 'control_checkbox',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => 'No',
                                        'allowOther' => $allowNew,
                                        'special' => $special
                                    );
                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Multiple Choice component added successfully with Allow and Special.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else if (strpos($str_arr[3], "-")) {
                                    $options = str_replace("-", "|", $str_arr[3]);

                                    $question = array(
                                        'type' => 'control_checkbox',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => 'No',
                                        'allowOther' => $allowNew,
                                        'options' => $options
                                    );

                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Multiple Choice component added successfully with Allow and Options.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else {
                                    return "You entered wrong component for Special/Optional component.";
                                }
                            } else {
                                return "Please enter option or special components.";
                            }
                        } else if (is_numeric($str_arr[2])) {
                            if (count($str_arr) == 4) {
                                return "Please enter just one of them (special or options)";
                            }
                            $special = $str_arr[2];
                            switch ($special) {
                                case '1':
                                    $special = 'Gender';
                                    break;
                                case '2':
                                    $special = 'Days';
                                    break;
                                case '3':
                                    $special = 'Months';
                                    break;
                                default:
                                    return "You have to choose between 1-3 for pre-defined values. Look at predefined values from !10";
                            }

                            $question = array(
                                'type' => 'control_checkbox',
                                'text' => $text,
                                'order' => $order,
                                'required' => 'No',
                                'special' => $special
                            );
                            try {
                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                return "Multiple Choice component added successfully with Special.";
                            } catch (Exception $e) {
                                return "Your API Key is wrong.";
                            }
                        } else if (strpos($str_arr[2], "-")) {
                            if (count($str_arr) == 4) {
                                return "Please enter just one of them (special or options)";
                            }
                            $options = str_replace("-", "|", $str_arr[2]);

                            $question = array(
                                'type' => 'control_checkbox',
                                'text' => $text,
                                'order' => $order,
                                'required' => 'No',
                                'options' => $options
                            );

                            try {
                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                return "Multiple Choice component added successfully with Options.";
                            } catch (Exception $e) {
                                return "Your API Key is wrong.";
                            }
                        } else if (count($str_arr) == 4) {
                            return "You entered wrong component!";
                        } else {
                            return "Please enter option or special components.";
                        }
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addNumber()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $numberInfo = str_replace("!Number:", "", $content);
        $str_arr = explode(",", $numberInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Email component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    if (count($str_arr) < 2 || count($str_arr) > 5) {
                        return "Please do not enter missing or excess components.";
                    } else if (count($str_arr) > 2) {

                        $text = $str_arr[0];
                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }
                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                            if (!empty($str_arr[3])) {
                                if (str_starts_with($str_arr[3], "Min:")) {
                                    $minValue = str_replace("Min:", "", $str_arr[3]);

                                    if (!is_numeric($minValue)) {
                                        return "Please enter the Min value as a number.";
                                    }

                                    if (!empty($str_arr[4])) {
                                        if (str_starts_with($str_arr[4], "Max:")) {
                                            $maxValue = str_replace("Max:", "", $str_arr[4]);

                                            if (!is_numeric($maxValue)) {
                                                return "Please enter the Max value as a number.";
                                            }
                                            $question = array(
                                                'type' => 'control_number',
                                                'text' => $text,
                                                'order' => $order,
                                                'required' => $requiredNew,
                                                'minValue' => $minValue,
                                                'maxValue' => $maxValue
                                            );
                                            try {
                                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                                return "Number component added successfully with Required, Min and Max.";
                                            } catch (Exception $e) {
                                                return "Your API Key is wrong.";
                                            }
                                        } else {
                                            return "You entered wrong component!";
                                        }
                                    } else {
                                        $question = array(
                                            'type' => 'control_number',
                                            'text' => $text,
                                            'order' => $order,
                                            'required' => $requiredNew,
                                            'minValue' => $minValue
                                        );
                                        try {
                                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                            return "Number component added successfully with Required and Min.";
                                        } catch (Exception $e) {
                                            return "Your API Key is wrong.";
                                        }
                                    }
                                } else if (str_starts_with($str_arr[3], "Max:")) {
                                    $maxValue = str_replace("Max:", "", $str_arr[3]);
                                    if (!is_numeric($maxValue)) {
                                        return "Please enter the Max value as a number.";
                                    }

                                    $question = array(
                                        'type' => 'control_number',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => $requiredNew,
                                        'maxValue' => $maxValue
                                    );
                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Number component added successfully with Required and Max.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else {
                                    return "You entered wrong component!";
                                }
                            } else {
                                $question = array(
                                    'type' => 'control_number',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => $requiredNew,
                                );
                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "Number component added successfully with Required.";
                                } catch (Exception $e) {
                                    return "Your API Key is wrong.";
                                }
                            }
                        } else if (str_starts_with($str_arr[2], "Min:")) {
                            $minValue = str_replace("Min:", "", $str_arr[2]);
                            if (!is_numeric($minValue)) {
                                return "Please enter the Min value as a number.";
                            }

                            if (!empty($str_arr[3])) {
                                if (str_starts_with($str_arr[3], "Max:")) {
                                    $maxValue = str_replace("Max:", "", $str_arr[3]);
                                    if (!is_numeric($maxValue)) {
                                        return "Please enter the Max value as a number.";
                                    }
                                    $question = array(
                                        'type' => 'control_number',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => 'No',
                                        'minValue' => $minValue,
                                        'maxValue' => $maxValue
                                    );
                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "Number component added successfully with Min and Max.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else {
                                    return "You entered wrong component!";
                                }
                            } else {
                                $question = array(
                                    'type' => 'control_number',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => 'No',
                                    'minValue' => $minValue
                                );
                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "Number component added successfully with Min.";
                                } catch (Exception $e) {
                                    return "Your API Key is wrong.";
                                }
                            }
                        } else if (str_starts_with($str_arr[2], "Max:")) {
                            $maxValue = str_replace("Max:", "", $str_arr[2]);

                            if (!is_numeric($maxValue)) {
                                return "Please enter the Max value as a number.";
                            }
                            $question = array(
                                'type' => 'control_number',
                                'text' => $text,
                                'order' => $order,
                                'required' => 'No',
                                'maxValue' => $maxValue
                            );
                            try {
                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                return "Number component added successfully with Max.";
                            } catch (Exception $e) {
                                return "Your API Key is wrong.";
                            }
                        } else {
                            return "You entered wrong component!";
                        }
                    } else {

                        $text = $str_arr[0];

                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }
                        $question = array(
                            'type' => 'control_number',
                            'text' => $text,
                            'order' => $order,
                            'required' => 'No'
                        );
                        try {
                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                            return "Number component added successfully.";
                        } catch (Exception $e) {
                            return "Your API Key is wrong.";
                        }
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addUploadFile()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $uploadFileInfo = str_replace("!FileUpload:", "", $content);
        $str_arr = explode(",", $uploadFileInfo);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $row = mysqli_fetch_assoc($getTable);
            $apiKey = $row['apiKey'];
            $selectedForm = $row['formID'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to add Email component, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                if (empty($selectedForm)) {
                    return "Please select the form you want to add an element to with the !SelectForm:formID command.";
                } else {
                    $jotformAPI = new JotForm($apiKey);

                    if (count($str_arr) < 2 || count($str_arr) > 5) {
                        return "Please do not enter missing or excess components.";
                    } else if (count($str_arr) > 2) {

                        $text = $str_arr[0];
                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }
                        if (str_starts_with($str_arr[2], "Required:")) {
                            $required = str_replace("Required:", "", $str_arr[2]);
                            if (ucfirst(strtolower($required)) == 'Yes' || ucfirst(strtolower($required)) == 'No') {
                                $requiredNew = ucfirst(strtolower($required));
                            } else {
                                return "Please enter YES or No for required component.";
                            }
                            if (!empty($str_arr[3])) {
                                if (str_starts_with($str_arr[3], "Multiple:")) {
                                    $multiple = str_replace("Multiple:", "", $str_arr[3]);
                                    if (ucfirst(strtolower($multiple)) == 'Yes' || ucfirst(strtolower($multiple)) == 'No') {
                                        $allowMultiple = ucfirst(strtolower($multiple));

                                        if (!empty($str_arr[4])) {
                                            if (str_starts_with($str_arr[4], "Type:")) {
                                                $type = strtolower(str_replace("Type:", "", $str_arr[4]));
                                                $extensions = str_replace("-", ", ", $type);;

                                                $question = array(
                                                    'type' => 'control_fileupload',
                                                    'text' => $text,
                                                    'order' => $order,
                                                    'required' => $requiredNew,
                                                    'allowMultiple' => $allowMultiple,
                                                    'maxFileSize' => 10854,
                                                    'extensions' => $extensions
                                                );

                                                try {
                                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                                    return "File Upload component added successfully with Required, Multiple and Type.";
                                                } catch (Exception $e) {
                                                    return "Your API Key is wrong.";
                                                }
                                            } else {
                                                return "You entered wrong component!";
                                            }
                                        } else {
                                            $question = array(
                                                'type' => 'control_fileupload',
                                                'text' => $text,
                                                'order' => $order,
                                                'required' => $requiredNew,
                                                'maxFileSize' => 10854,
                                                'allowMultiple' => $allowMultiple
                                            );

                                            try {
                                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                                return "File Upload component added successfully with Required and Multiple.";
                                            } catch (Exception $e) {
                                                return "Your API Key is wrong.";
                                            }
                                        }
                                    } else {
                                        return "Please enter YES or No for multiple component.";
                                    }
                                } else if (str_starts_with($str_arr[3], "Type:")) {
                                    $type = strtolower(str_replace("Type:", "", $str_arr[3]));
                                    $extensions = str_replace("-", ", ", $type);;

                                    $question = array(
                                        'type' => 'control_fileupload',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => $requiredNew,
                                        'maxFileSize' => 10854,
                                        'extensions' => $extensions
                                    );

                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "File Upload component added successfully with Required, Type.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                } else {
                                    return "You entered wrong component!";
                                }
                            } else {
                                $question = array(
                                    'type' => 'control_fileupload',
                                    'text' => $text,
                                    'order' => $order,
                                    'required' => $requiredNew,
                                    'maxFileSize' => 10854
                                );
                                try {
                                    $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                    return "File Upload component added successfully with Required.";
                                } catch (Exception $e) {
                                    return "Your API Key is wrong.";
                                }
                            }
                        } else if (str_starts_with($str_arr[2], "Multiple:")) {
                            $multiple = str_replace("Multiple:", "", $str_arr[2]);
                            if (ucfirst(strtolower($multiple)) == 'Yes' || ucfirst(strtolower($multiple)) == 'No') {
                                $allowMultiple = ucfirst(strtolower($multiple));

                                if (!empty($str_arr[3])) {
                                    if (str_starts_with($str_arr[3], "Type:")) {
                                        $type = strtolower(str_replace("Type:", "", $str_arr[3]));
                                        $extensions = str_replace("-", ", ", $type);;

                                        $question = array(
                                            'type' => 'control_fileupload',
                                            'text' => $text,
                                            'order' => $order,
                                            'required' => 'No',
                                            'allowMultiple' => $allowMultiple,
                                            'maxFileSize' => 10854,
                                            'extensions' => $extensions
                                        );
                                        try {
                                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                            return "File Upload component added successfully with Multiple and Type.";
                                        } catch (Exception $e) {
                                            return "Your API Key is wrong.";
                                        }
                                    } else {
                                        return "You entered wrong component!";
                                    }
                                } else {
                                    $question = array(
                                        'type' => 'control_fileupload',
                                        'text' => $text,
                                        'order' => $order,
                                        'required' => 'No',
                                        'maxFileSize' => 10854,
                                        'allowMultiple' => $allowMultiple
                                    );
                                    try {
                                        $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                        return "File Upload component added successfully with Multiple.";
                                    } catch (Exception $e) {
                                        return "Your API Key is wrong.";
                                    }
                                }
                            } else {
                                return "Please enter YES or No for multiple component.";
                            }
                        } else if (str_starts_with($str_arr[2], "Type:")) {
                            $type = strtolower(str_replace("Type:", "", $str_arr[2]));
                            $extensions = str_replace("-", ", ", $type);;

                            $question = array(
                                'type' => 'control_fileupload',
                                'text' => $text,
                                'order' => $order,
                                'required' => 'No',
                                'maxFileSize' => 10854,
                                'extensions' => $extensions
                            );
                            try {
                                $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                                return "File Upload component added successfully with Type.";
                            } catch (Exception $e) {
                                return "Your API Key is wrong.";
                            }
                        } else {
                            return "You entered wrong component!";
                        }
                    } else {

                        $text = $str_arr[0];
                        if (is_numeric($str_arr[1])) {
                            $order = $str_arr[1];
                        } else {
                            return "Please enter number for order.";
                        }
                        $question = array(
                            'type' => 'control_fileupload',
                            'text' => $text,
                            'required' => 'No',
                            'maxFileSize' => 10240,
                            'order' => $order
                        );
                        try {
                            $response = $jotformAPI->createFormQuestion($selectedForm, $question);
                            return "File Upload component added successfully.";
                        } catch (Exception $e) {
                            return "Your API Key is wrong.";
                        }
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function addReport()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $bugReport = str_replace("!BugReport:", "", $content);

        $delimiters = ['Desc:', 'Link:', 'Name:', 'Surname:', 'Category:'];
        $newStr = str_replace($delimiters, $delimiters[0], $bugReport);
        $str_arr = explode($delimiters[0], $newStr);

        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");

            if (mysqli_num_rows($getTable) == 0) {
                return ["In order to make a bug report, you must be registered in the system. Switch to the registration section using the !Register command."];
            } else {
                $jotformAPI = new JotForm("cc2217e416e8fc8667e948f1713c7fa8");
                if (count($str_arr) != 6) {
                    return ["Please do not enter missing or excess components."];
                } else {

                    $name = str_replace(",", "", $str_arr[1]);
                    $surname = str_replace(",", "", $str_arr[2]);
                    $channel = ucfirst(strtolower(str_replace(",", "", $str_arr[3])));
                    $desc = rtrim($str_arr[4], ",");
                    $url = str_replace(",", "", $str_arr[5]);

                    $newName = $name;
                    $newSurname = $surname;

                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        $newUrl = $url;
                    } else {
                        return ["Your URl is not valid!"];
                    }
                    if (strlen($desc) <= 400) {
                        $newDesc = $desc;
                    } else {
                        return ["Your description exceeded the description max limit."];
                    }
                    if ($channel == 'Tables' || $channel == 'Forms' || $channel == 'Reports' || $channel == 'Approval' || $channel == 'Inbox' || $channel == 'Apps') {
                        $newChannel = $channel;
                    } else {
                        return ["You have to choose one of them (Forms,Tables,Reports,Approvals,Apps,Inbox) for category."];
                    }

                    $submission = array(
                        "3_first" => $newName,
                        "3_last" => $newSurname,
                        "4" => $newChannel,
                        "5" => $newUrl,
                        "6" => $newDesc,
                    );

                    try {
                        $result = $jotformAPI->createFormSubmission("222142081070036", $submission);
                        return ["Bug submission done successfully.", $newChannel, $newDesc, $newUrl];
                    } catch (Exception $e) {
                        return ["Your API Key is wrong."];
                    }
                }
            }
        } else {
            return ["Database connection failed."];
        }
    }

    public function postSubmission()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;

        $submissionInfo = str_replace("!Submission:", "", $content);
        $formID = $submissionInfo;
        $connect = $this->database();


        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            $rowCheck = mysqli_fetch_assoc($getTable);
            $submissionForm = $rowCheck['submission'];
            $QuestionOrder = $rowCheck['QOrder'];

            if (mysqli_num_rows($getTable) == 0) {
                return "In order to submit a form, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                $getFormsTable = mysqli_query($connect, "SELECT * FROM forms WHERE formID='$formID'");
                if (mysqli_num_rows($getFormsTable) == 0) {
                    return "There is no such form created via bot in the system.";
                } else {
                    if (!empty($QuestionOrder) && !empty($submissionForm)) {
                        return "The form to be submitted has been selected. You cannot use this command again. Please complete the submission first or log out with the !ExitSubmission command. If you log out, the data you have entered so far will be submitted and the system will be reset.";
                    } else {
                        $getForm = mysqli_query($connect, "SELECT * FROM formanswers WHERE formID='$formID' AND discordID='$discordID'");
                        if (mysqli_num_rows($getForm) >= 1) {
                            return "You have already submitted this form.";
                        }
                        $row = mysqli_fetch_assoc($getFormsTable);
                        $apiKey = $row['apiKey'];
                        $jotformAPI = new JotForm($apiKey);
                        $questions = $jotformAPI->getFormQuestions($formID);
                        $insertIDs = mysqli_query($connect, "INSERT INTO formanswers (formID,discordID) VALUES ('$formID','$discordID')");
                        $updateTable = mysqli_query($connect, "UPDATE users SET submission='$formID' WHERE discordID='$discordID'");
                        $updateTable2 = mysqli_query($connect, "UPDATE users SET QOrder='1' WHERE discordID='$discordID'");
                        $getFormID = mysqli_query($connect, "SELECT * FROM users WHERE submission='$formID' AND discordID='$discordID'");
                        $rowOrder = mysqli_fetch_assoc($getFormID);
                        $order = $rowOrder['QOrder'];
                        $showQuestion = $this->form($apiKey, $formID, $order, $discordID);
                        return $showQuestion;
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function exitSubmission()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;
        $connect = $this->database();

        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            if (mysqli_num_rows($getTable) == 0) {
                return "In order to exit from the form, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                $row = mysqli_fetch_assoc($getTable);
                $formID = $row['submission'];
                $qOrder = $row['QOrder'];

                if (empty($formID) && empty($qOrder)) {
                    return "You have already signed out of form submission.";
                } else if (!empty($formID) && !empty($qOrder)) {
                    $getFormsTable = mysqli_query($connect, "SELECT * FROM forms WHERE formID='$formID'");
                    $row2 = mysqli_fetch_assoc($getFormsTable);
                    $apiKey = $row2['apiKey'];
                    $jotformAPI = new JotForm($apiKey);
                    $questions = $jotformAPI->getFormQuestions($formID);
                    if ($qOrder <= count($questions)) {
                        $updateTable = mysqli_query($connect, "UPDATE users SET QOrder='',submission='' WHERE discordID='$discordID'");
                        $updateTable2 = mysqli_query($connect, "DELETE FROM formanswers WHERE formID='$formID' AND discordID='$discordID'");
                        if ($updateTable && $updateTable2) {
                            return "The submission process has been exited.";
                        } else {
                            return "Failed to exit submission process.";
                        }
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }

    public function answerQuestion()
    {
        $discordID = $this->discordID;
        $content = $this->parameter;
        $connect = $this->database();
        if ($connect) {
            $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
            if (mysqli_num_rows($getTable) == 0) {
                return "In order to answer the question, you must be registered in the system. Switch to the registration section using the !Register command.";
            } else {
                $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
                $row = mysqli_fetch_assoc($getTable);
                $submission = $row['submission'];
                $order = $row['QOrder'];
                if (empty($submission)) {
                    return "Please enter which form you will submit to with the !Submission:formID command.";
                } else {
                    $getTable = mysqli_query($connect, "SELECT * FROM forms WHERE formID='$submission'");
                    $row = mysqli_fetch_assoc($getTable);
                    $apiKey = $row['apiKey'];
                    $jotformAPI = new JotForm("$apiKey");
                    $questions = $jotformAPI->getFormQuestions("$submission");
                    foreach ($questions as $question) {
                        $answer = str_replace("!Answer:", "", $content);
                        if (empty($answer)) {
                            return "Please answer the question. The question cannot be left blank.";
                        } else if ($order >= 1) {
                            if ($question['order'] == $order) {
                                if ($question['required'] == 'Yes' || ($question['required'] == 'No' && $answer != 'Skip')) {
                                    if ($answer == "Skip") {
                                        return "Answering this question is mandatory.";
                                    } else {
                                        switch ($question['type']) {
                                            case "control_fullname":
                                                $str_arr = explode(",", $answer);
                                                if (count($str_arr) == 2) {
                                                    $answer = implode(",", $str_arr);
                                                    $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                    return $output;
                                                } else {
                                                    return "Please enter your name and surname separated by commas.";
                                                }
                                                break;
                                            case "control_email":
                                                if (filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                                                    $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                    return $output;
                                                } else {
                                                    return "$answer is not a valid email address";
                                                }
                                                break;
                                            case "control_address":
                                                $str_arr = explode(",", $answer);
                                                if (count($str_arr) == 5) {
                                                    $answer = implode(",", $str_arr);
                                                    $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                    return $output;
                                                } else {
                                                    return "Please enter Street Address, Street Address Line 2, City, State/Province and Postal/Zip Code separated by commas.";
                                                }
                                                break;
                                            case "control_phone":
                                                if ($question['countryCode'] == "Yes") {
                                                    $str_arr = explode(",", $answer);
                                                    if (count($str_arr) == 3) {
                                                        if (is_numeric($str_arr[2]) && strlen($str_arr[2]) == 7 && is_numeric($str_arr[0]) && (strlen($str_arr[0]) <= 3) && (strlen($str_arr[0]) >= 1) && is_numeric($str_arr[1]) && (strlen($str_arr[1]) <= 3) && (strlen($str_arr[1]) >= 1)) {
                                                            $answer = implode(",", $str_arr);
                                                            $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                            return $output;
                                                        } else {
                                                            return "$str_arr[0] is not a valid countryCode or $str_arr[1] is not a valid areaCode or $str_arr[2] is not a valid phone.";
                                                        }
                                                    } else {
                                                        return "Please enter countryCode,areaCode and phone.";
                                                    }
                                                } else if ($question['countryCode'] == "No") {
                                                    $str_arr = explode(",", $answer);
                                                    if (count($str_arr) == 2) {
                                                        if (is_numeric($str_arr[1]) && strlen($str_arr[1]) == 7 && is_numeric($str_arr[0]) && strlen($str_arr[0]) <= 3 && strlen($str_arr[0]) >= 1) {
                                                            $answer = implode(",", $str_arr);
                                                            $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                            return $output;
                                                        } else {
                                                            return "$str_arr[0] is not a valid areaCode or $str_arr[1] is not a valid phone.";
                                                        }
                                                    } else {
                                                        return "Please enter areaCode and phone.";
                                                    }
                                                }
                                                break;
                                            case "control_textbox":
                                                if (array_key_exists('maxsize', $question)) {
                                                    if (strlen($answer) <= $question['maxsize']) {
                                                        $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                        return $output;
                                                    } else {
                                                        return "Your answer is too long.";
                                                    }
                                                } else {
                                                    $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                    return $output;
                                                }
                                                break;
                                            case "control_textarea":
                                                if (array_key_exists('entryLimit', $question)) {
                                                    $str_arr = explode("-", $question['entryLimit']);
                                                    if ($str_arr[0] == "Words") {
                                                        if ((str_word_count($answer)) <= $str_arr[1]) {
                                                            $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                            return $output;
                                                        } else {
                                                            return "Your answer is too long.";
                                                        }
                                                    } else if ($str_arr[0] == "Letters") {
                                                        if (strlen($answer) <= $str_arr[1]) {
                                                            $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                            return $output;
                                                        } else {
                                                            return "Your answer is too long.";
                                                        }
                                                    }
                                                } else {
                                                    $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                    return $output;
                                                }
                                                break;
                                            case "control_dropdown":
                                            case "control_radio":
                                                if (array_key_exists('options', $question)) {
                                                    $str_arr = explode("|", $question['options']);
                                                    if (in_array($answer, $str_arr)) {
                                                        $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                        return $output;
                                                    } else {
                                                        return "Your answer is not include in options.";
                                                    }
                                                } else if (array_key_exists('special', $question)) {
                                                    $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                    return $output;
                                                }
                                                break;
                                            case "control_checkbox":
                                                if (array_key_exists('options', $question)) {
                                                    $str_arr = explode("|", $question['options']);
                                                    $answerNew = explode(",", $answer);
                                                    $equal = count(array_intersect($answerNew, $str_arr));
                                                    if ($equal == count($answerNew)) {
                                                        $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                        return $output;
                                                    } else {
                                                        return "Your answer or answers is not include multiple choices's options.";
                                                    }
                                                } else if (array_key_exists('special', $question)) {
                                                    $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                    return $output;
                                                }
                                                break;
                                            case "control_number":
                                                if (!is_numeric($answer)) {
                                                    return "You should write a numeric value.";
                                                }
                                                if (array_key_exists('minValue', $question)) {
                                                    if (array_key_exists('maxValue', $question)) {
                                                        if ($answer >= $question['minValue'] && $answer <= $question['maxValue']) {
                                                            $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                            return $output;
                                                        } else {
                                                            return "Your number is not include in range";
                                                        }
                                                    } else {
                                                        if ($answer >= $question['minValue']) {
                                                            $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                            return $output;
                                                        } else {
                                                            return "Your number is less than minValue!";
                                                        }
                                                    }
                                                } else if (array_key_exists('maxValue', $question)) {
                                                    if ($answer <= $question['maxValue']) {
                                                        $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                        return $output;
                                                    } else {
                                                        return "Your number is greater than maxValue!";
                                                    }
                                                } else {
                                                    $output = $this->insertAnswer($question, $apiKey, $submission, $discordID, $order, $answer);
                                                    return $output;
                                                }
                                                break;
                                        }
                                    }
                                } else if ($question['required'] == 'No' && $answer == "Skip") {
                                    $order++;
                                    $updateOrder = mysqli_query($connect, "UPDATE users SET QOrder='$order' WHERE discordID='$discordID'");
                                    $getTable = mysqli_query($connect, "SELECT * FROM users WHERE discordID='$discordID'");
                                    $row = mysqli_fetch_assoc($getTable);
                                    $newOrder = $row['QOrder'];
                                    $showQuestion = $this->form($apiKey, $submission, $newOrder, $discordID);
                                    return $showQuestion;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            return "Database connection failed.";
        }
    }
}
