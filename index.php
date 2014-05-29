<?php session_start (); ?><?php
ini_set("log_errors", 1);
ini_set("error_log", "/tmp/php-error.log");
error_log( "Hello, errors!" );

//ini_set('display_errors', 'On');

define ('ACCESS', true);
define ('ACTION', 'action');
define ('USER', 'user');
define ('ACTION_LOGIN', 'Log in');
define ('ACTION_LOGOUT', 'Log out');
define ('ACTION_SEND_CODE', 'Odevzdat');
define ('DATA', './files');
define ('PYTHON_SPOJ', 'python ./py/spoj_run.py');


require_once 'libs.php';
require_once 'consts.php';

fixPaths ();


$action = getAction ();
switch ($action) {
    // login case
    case ACTION_LOGIN:
        $username = getParameter ('username', true);
        $password = getParameter ('password', true);
        tryLogin ($username, $password);

        // try login
        if (isLoggedIn ()) {
            // show send code page on success
            require 'html/send-code.php';
        } else {
            // on failure show warning
            $error = LOGIN_ERROR;
            require 'html/login.php';
        }
        break;

    // logout case
    case ACTION_LOGOUT:
        logout ();
        require 'html/login.php';
        break;

    case ACTION_SEND_CODE:
        $languages = getLanguages ();
        $problems = getProblems ();
        
        $problem = getParameter ('problem', true);
        $language = getParameter ('language', true);
        $source = getParameter ('source', true);
        $isAjax = intval (getParameter ('ajax', true)) == 1;

        // wrong language id
        if (!array_key_exists ($language, $languages)) {
            if ($isAjax)
                echo json_encode (array('error' => 'Nepodporovany jazyk!'));
            else
                require 'html/error.php';
            die;
        }
        
        // wrong problem id
        if (!array_key_exists ($problem, $problems)) {
            if ($isAjax)
                echo json_encode (array('error' => 'Nepodporovany jazyk!'));
            else
                require 'html/error.php';
            die;
        }

        $directory = prepareStructure ($problem, $_SESSION[USER]->username);
        $ext = getLangExtensions();
        $sourcePath = saveSourceCode ($directory, $source, $ext[$language]);

        // file error
        if ($sourcePath === false) {
            if ($isAjax)
                echo json_encode (array('error' => 'Nelze ulozit soubor!'));
            else
                require 'html/error.php';
            die;
        }


        // process
        $result = sendToServer ($problem, $language, $sourcePath);

        $details = json_decode($result->json);
        error_log("details");
        $directory = renameDirectory ($directory, $details);
        $details->lang = $languages[$language];
        $details->directory = $directory; 
        $details->problem = $problem;
        //$details = processOutput ($result);
        saveDetails ($directory, $details);
        

        // return result
        if ($isAjax) {
            echo json_encode ($details);
        } else {
            require 'html/details.php';
        }
        break;

    // default case    
    default :
        // if is logged in, show send code page
        if (isLoggedIn ())
            require 'html/send-code.php';
        else
            require 'html/login.php';
        break;
}