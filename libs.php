<?php
$time = time ();



function isLoggedIn () {
    return isset ($_SESSION[USER]);
}



function getAction () {
    return getParameter (ACTION, TRUE);
}



function saveLogin ($username) {
    $user = array(
        'username' => $username,
        'time' => time ()
    );
    $_SESSION[USER] = (object) $user;
}



function getParameter ($name, $secure = false) {
    $value = isset ($_POST[$name]) ? $_POST[$name] : null;
    if (!$secure)
        return $value;

    $value = htmlspecialchars ($value);
    return $value;
}



function tryLogin ($username, $password) {
    // LDAP check
    saveLogin ($username);
}



function logout () {
    unset ($_SESSION[USER]);
    session_destroy ();
}



function getProblems () {
    $result = array();
    $xml = simplexml_load_file ('problems.xml');

    $items = $xml->item;

    foreach ($items as $value) {
        $code = @$value->problemcode;
        $name = @$value->problemname;
        if (empty ($code) && empty ($name))
            continue;
        if (empty ($code))
            $code = $name;
        if (empty ($name))
            $name = $code;
        $result["$code"] = $name;
    }
    return $result;
}



function getLanguages () {
    $lang_name = array();
    
    $xml = simplexml_load_file ('langs.xml');
    $items = $xml->item;
    foreach ($items as $item) {
        $attrs = $item->attributes ();
        $value = $attrs['value'];
        $lang_name["$value"] = "$item";
    }
    return $lang_name;
}



function getLangExtensions () {
    $lang_ext = array();
    
    $xml = simplexml_load_file ('langs.xml');
    $items = $xml->item;
    foreach ($items as $item) {
        $attrs = $item->attributes ();
        $ext = $attrs['ext'];
        $value = $attrs['value'];
        $lang_ext["$value"] = "$ext";
    }
    return $lang_ext;
}



function prepareStructure ($spojcode, $username) {
    $directory = DATA . "/$username/$spojcode/" . date ("y.m.d_H.i.s") . "_$spojcode";
    if (!file_exists ($directory)) {
		if (!mkdir ($directory, 0777, true)) {
			die ("cannot create directory '$directory'");
		}
	}

    return $directory;
}



/**
  Constructs the SSE data format and flushes that data to the client.
 */
function sendMessage ($id, $data) {

    echo "id: $id" . PHP_EOL;
    echo "data: " . json_encode ($data) . PHP_EOL;
    echo PHP_EOL;

    //PUSH THE data out by all FORCE POSSIBLE
    ob_flush ();
    flush ();
}



function saveSourceCode ($directory, $source, $langExt) {
    $file_name = "$directory/source.$langExt";
    file_put_contents ($file_name, html_entity_decode ($source));
    return $file_name;
}



function saveDetails ($directory, &$details) {
    $file = "$directory/result.txt";
    file_put_contents ($file, '');
/*
    $props = array(
        'status' => 'vysledek',
        'lang' => 'jazyk',
        'date' => 'cas',
        'id' => 'id',
        'runtime' => 'cas behu',
        'runmem' => 'pamet behu',
        'info' => 'test info',
        'stdio' => 'vystup',
    );
    
    foreach ($props as $key => $value) {
        file_put_contents (
                $file, sprintf ('%-12s: %s', $value, $details->$key), FILE_APPEND
        );
        file_put_contents (
                $file, PHP_EOL, FILE_APPEND);
    }
    */
    file_put_contents("$directory/stdout", $details->stdio);
    file_put_contents("$directory/judge.out", $details->test_info);
    unset($details->stdio);
    unset($details->test_info);
    foreach ($details as $key => $value) {
        file_put_contents (
                $file, sprintf ('%-12s: %s', $key, $value), FILE_APPEND
        );
        file_put_contents (
                $file, PHP_EOL, FILE_APPEND);
    }
}



function processOutput ($result) {
    $details = new stdClass();
	$details->debug = array();
    foreach ($result->output as $line) {
        $matches = array();
        preg_match ('/^([a-z_A-Z]+)=(.*)/', $line, $matches);
        if (count ($matches) == 3) {
            $details->$matches[1] = $matches[2];
			continue;
		} else {
			$details->debug[] = $line;
		}
    }
    return $details;
}



function view_logout () {
    ?>
    <section>
        <form name="logout" action="index.php" method="post" accept-charset="utf-8">
            <fieldset class="logoutform cf">
                <p><?php echo LOGGED_IN_AS; ?> <strong><?php echo $_SESSION[USER]->username; ?></strong></p>
                <input class="btn" type="submit" name="<?php echo ACTION; ?>" value="<?php echo ACTION_LOGOUT; ?>">
            </fieldset>
        </form>
    </section>
    <?php
}



function renameDirectory ($directory, $details) {
    $status = $details->result;
    $flag = 'N';
    switch ($status) {
        case 'limit':
            $flag = 'L';
            break;
        case 'accepted':
            $flag = 'A';
            break;
        case 'wrong answer':
            $flag = 'W';
            break;
        case 'compilation error':
            $flag = 'C';
            break;
        case 'runtime error':
        default:
            $flag = 'E';
            break;
    }
    $new_name=$directory . '_' . $flag;
    rename ($directory, $new_name);
    return $new_name;
}



function sendToServer ($problem, $language, $sourcePath) {
    $tmp_json_out= tempnam( "/tmp/" , "spoj-");
    $command = PYTHON_SPOJ . sprintf (' --json-output "%s" "%s" "%s" "%s"', $tmp_json_out, $problem, $language, $sourcePath);
    $output = array();
    $result = new stdClass();
    $return = 0;
    exec ($command, $output, $return);
     
    $result->command = $command;
    $result->output = $output;
    $result->code = $return;
    $result->json = file_get_contents($tmp_json_out);
    unlink($tmp_json_out);
 	
    return $result;
}



function fixPaths () {
    set_include_path (get_include_path () . ";" . getenv ('PATH'));
}