<?php
    require_once 'login.php';
    require_once 'helperFunctions.php';

    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die(mysql_fatal_error());

    session_start();
    
    if (isset($_SESSION['check']) && $_SESSION['check'] != hash('ripemd128', $_SERVER['REMOTE_ADDR'] .$_SERVER['HTTP_USER_AGENT']))
        different_user();
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id();
        $_SESSION['initiated'] = 1;
    }
    
    if (isset($_POST['logout'])) {
        different_user();
    }
    
    if (isset($_SESSION['username'])) {
        ini_set('session.gc_maxlifetime', 60 * 60 * 24);
        $username = $_SESSION['username'];

        echo "Welcome back $username!<br>";

        $query = "CREATE TABLE IF NOT EXISTS inputs(
            entryTimeStamp TIMESTAMP NOT NULL,
            username VARCHAR(32) NOT NULL,
            input TEXT,
            cipherUsed VARCHAR(32)
        )";

        $result = $conn->query($query);
        if (!$result) die (mysql_fatal_error());
    }

    echo <<<_END

        <h1>Decryptoid</h1>
        
        <p><a href=register.php>Sign up</a>   <a href=authenticate.php>Login</a></p>
        <form action='Homepage.php' method='POST' enctype='multipart/form-data'>
            <input type='submit' name='logout' value='Logout'>
        </form> 
        <form action="Homepage.php" method="POST" enctype="multipart/form-data">
            
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
            <br>
            <input type = "Submit" name ="submitTextbox" value="Encrypt/Decrypt Text"/>
            
            <br><br>
            <p>Or submit a text file here:</p>
        <input type='file' name='selectedFile' id='selectedFile'>
            <br><br>
            <input type = "Submit" name ="submitFile" value="Encrypt/Decrypt File"/>

            <h3>Output:</h3>

        </form>      
_END;

    if (isset($_POST['submitTextbox'])) {   // Get input from text box
        if (strlen($_POST['textArea']) != 0) {
            $text = mysql_entities_fix_string($conn, $_POST['textArea']);
            encryptOrDecrypt($text, $conn);
        }
    }

    else if (isset($_POST['submitFile'])) { // Get input from a text file
        if ($_FILES['selectedFile']['type'] == 'text/plain') {
            $text = mysql_entities_fix_string($conn, file_get_contents($_FILES['selectedFile']['tmp_name']));
            encryptOrDecrypt($text, $conn);
        }
        else {
            echo "The file you upload must be a text file.";
        }
    }

    function encryptOrDecrypt($input, $conn) {
        if ($_POST['ciphers'] == 'Simple Substitution') {
            require_once 'SimpleSubstitution.php';

            $cipher = new SimpleSubstitution();

            $pair = array("a"=>"c", "b"=>"h", "c"=>"q", "d"=>"g", "e"=>"u","f"=>"r",
                        "g"=>"m", "h"=>"a","i"=>"l", "j"=>"y", "k"=>"e", "l"=>"n", "m"=>"p",
                        "n"=>"v", "o"=>"d", "p"=>"z", "q"=>"x", "r"=>"k", "s"=>"i", "t"=>"o",
                        "u"=>"f", "v"=>"s", "w"=>"t", "x"=>"j", "y"=>"w", "z"=>"b"," "=> " ");

            if ($_POST['type'] == 'Encrypt') {
                echo $cipher->encrypt($input, $pair);
            }
            else if ($_POST['type'] == 'Decrypt') {
                echo $cipher->decrypt($input, $pair);
            }
            if (isset($_SESSION['username'])) {
                $cipherUsed = 'Simple Substitution';
                storeInput($conn, $input, $cipherUsed);
            }
        }
        else if ($_POST['ciphers'] == 'Double Transposition'){
            require_once 'DoubleTransposition.php';

            $cipher = new DoubleTransposition();

            if($_POST['type'] == 'Encrypt') {
                echo $cipher->encrypt("doggo","rocket", $input);
            }else if($_POST['type'] == 'Decrypt') {

                echo $cipher->decrypt("rocket", "doggo", $input);
            }
            if (isset($_SESSION['username'])) {
                $cipherUsed = 'Double Transposition';
                storeInput($conn, $input, $cipherUsed);
            }
        }
        else if($_POST['ciphers'] =='RC4') {
            require_once 'RC4.php';

            $cipher = new RC4();

            echo $cipher->rc4Cipher("vanilla", $input);

            if (isset($_SESSION['username'])) {
                $cipherUsed = 'RC4';
                storeInput($conn, $input, $cipherUsed);
            }
        }
    }

    function storeInput($conn, $input, $cipherUsed){
        $username = mysql_entities_fix_string($conn, $_SESSION['username']);
        $stmt = $conn->prepare('INSERT INTO inputs VALUES(?,?,?,?)');
        $stmt->bind_param('ssss', $timestamp, $username, $input, $cipherUsed);
        $input = mysql_entities_fix_string($conn, $input);
        $cipherUsed = mysql_entities_fix_string($conn, $cipherUsed);
        $stmt->execute();
        $stmt->close();
    }

    // Close the result and connection to remove from memory so that attackers can't access them
    $result->close();
	$conn->close();
?>