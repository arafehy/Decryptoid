<?php
    require_once 'login.php';
    require_once 'helperFunctions.php';

    $conn = new mysqli($hn, $un, $pw, $db);
    if($conn->connect_error) die(mysql_fatal_error());

    session_start();
    
    if (isset($_SESSION['check']) && $_SESSION['check'] != hash('ripemd128', $_SERVER['REMOTE_ADDR'] .$_SERVER['HTTP_USER_AGENT']))
        different_user();
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id();
        $_SESSION['initiated'] = 1;
    }
    
    if (isset($_SESSION['username'])) {

        ini_set('session.gc_maxlifetime', 60 * 60 * 24);
        $username = $_SESSION['username'];

        echo "Welcome back $username!<br><br>";

        $query = "CREATE TABLE IF NOT EXISTS dataEntry(
            entryTimeStamp TIMESTAMP NOT NULL,
            username VARCHAR(32) NOT NULL,
            textInput TEXT,
            fileInput BLOB,
            convertedOutput BLOB
        )";

        $result = $conn->query($query);
        if(!$result) die (mysql_fatal_error());
    }

    echo <<<_END

        <h1>Welcome to Decryptoid!</h1>
        
        <p><a href=register.php>Sign up</a>   <a href=authenticate.php>Login</a></p>
        
        <form action="MainPage.php" method="POST" enctype="multipart/form-data">
            
            Select Cipher: <select id ="ciphers" name ="ciphers">
                <option value = "Simple Substitution">Simple Substitution</option>
                <option value = "Double Transposition">Double Transposition</option>
                <option value = "RC4">RC4</option>
            </select>
            Select Type: <select id ="type" name="type">
                <option name ="Encrypt">Encrypt</option>
                <option name = "Decrypt">Decrypt</option>
            </select>
            
            <br><br>
            
            <p>Enter text you would like to encrypt or decrypt in the box below:</p>
            <textarea rows="10 cols="100" name="textArea"></textarea>
            
            <br><br>
            <p>Or submit a text file here:</p>
        <input type='file' name='selectedFile' id='selectedFile'>
            <br><br>
            
            <input type = "Submit" name ="submitTextbox" value="Encrypt/Decrypt Textbox!"/>
            <input type = "Submit" name ="submitFile" value="Encrypt/Decrypt File!"/>

            <h3>Output:</h3>

        </form>      
    _END;

    if (isset($_POST['submitTextbox'])) {   // Get input from text box
        if (strlen($_POST['textArea']) != 0) {
            
            $text = mysql_entities_fix_string($conn, $_POST['textArea']);
            $text = preg_replace("/[^A-Za-z]/", '', $text);

            encryptOrDecrypt($text, $conn, TRUE);
        }
    }

    else if (isset($_POST['submitFile'])) { // Get input from a text file
        if ($_FILES['selectedFile']['type'] == 'text/plain') {

            $filename = $_FILES['selectedFile']['name'];
            $text = mysql_entities_fix_string($conn, file_get_contents($filename));
            $text = preg_replace("/[^A-Za-z]/", '', $text);

            encryptOrDecrypt($text, $conn, FALSE);
        }
        else {
            echo "The file you upload must be a text file.";
        }
    }

    function encryptOrDecrypt($string, $conn, $bool) {
        $output = "";
        if ($bool) {
            $textInput = $string;
            $fileInput = "";
        }
        else {
            $textInput ="";
            $fileInput = $string;
        }

        $timestamp = date('Y-m-d G:i:s');

        if (isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
        }

        if($_POST['ciphers'] == 'Simple Substitution') {
            require_once 'SimpleSubs.php';

            $cipher = new SimpleSubstitution();

            $pair = array("a"=>"c", "b"=>"h", "c"=>"q", "d"=>"g", "e"=>"u","f"=>"r",
                        "g"=>"m", "h"=>"a","i"=>"l", "j"=>"y", "k"=>"e", "l"=>"n", "m"=>"p",
                        "n"=>"v", "o"=>"d", "p"=>"z", "q"=>"x", "r"=>"k", "s"=>"i", "t"=>"o",
                        "u"=>"f", "v"=>"s", "w"=>"t", "x"=>"j", "y"=>"w", "z"=>"b"," "=> " ");

            if($_POST['type'] == 'Encrypt') {
                $output =  $cipher->encrypt($string, $pair);
                echo $output;
            }
            else if($_POST['type'] == 'Decrypt') {

                $output = $cipher->Decrypt($string, $pair);
                echo $output;
            }
            if (isset($_SESSION['username'])) {
                makeQuery($conn, $timestamp, $username, $textInput, $fileInput, $output);
            }

        }
        else if($_POST['ciphers']=='Double Transposition'){
            require_once 'DoubleTransposition.php';

            $cipher = new DoubleTransposition();

            if($_POST['type'] == 'Encrypt') {
                $output = $cipher->encrypt("doggo","rocket", $string);
                echo $output;
            }else if($_POST['type'] == 'Decrypt') {

                $output = $cipher->decrypt("rocket", "doggo", $string);
                echo $output;
            }

            if (isset($_SESSION['username'])) {
                makeQuery($conn, $timestamp, $username, $textInput, $fileInput, $output);
            }

        }else if($_POST['ciphers'] =='RC4') {
            require_once 'RC4.php';

            $cipher = new RC4();

            if($_POST['type'] == 'Encrypt'){
                $output = $cipher->rc4Cipher("secret", $string);
                echo $output;
            }
            else if($_POST['type'] == 'Decrypt'){
                $output = $cipher->rc4Cipher("secret", $string);
                echo $output;
            }
            if (isset($_SESSION['username'])) {
                makeQuery($conn, $timestamp, $username, $textInput, $fileInput, $output);
            }
        }
    }

    function makeQuery($conn, $timestamp, $username, $text, $file, $output){
        $stmt = $conn->prepare('INSERT INTO dataentry VALUES(?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $timestamp, $username, $text, $file, $output);
        $username = mysql_entities_fix_string($conn, $username);
        $text = mysql_entities_fix_string($conn, $text);
        $file = mysql_entities_fix_string($conn, $file);
        $output = mysql_entities_fix_string($conn, $output);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (!$result) die(mysql_fatal_error());
    }

    // Close the result and connection to remove from memory so that attackers can't access them
    $result->close();
	$conn->close();
?>