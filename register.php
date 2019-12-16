<?php
    require_once "login.php";
    require_once 'helperFunctions.php';

    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die(mysql_fatal_error());

    echo <<<_END
        <!DOCTYPE html>
            <html>
                <head>
                    <title>Decryptoid</title>
                    <style>
                    .signup {
                        border:1px solid #999999; font: normal 14px helvetica; color: #444444;
                    }
                    </style>
                    <script>
                    function validate(form) {
                        fail = validateUsername(form.username.value)
                        fail += validatePassword(form.password.value)
                        fail += validateEmail(form.email.value)
                        
                        if (fail == "") return true
                        else { alert(fail); return false }
                    }
                    </script>
                    <script src="validate_functions.js"></script
                </head>
                <body>
                    <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
                        <th colspan="2" align="center">Sign Up Form</th>
                        <form method="post" action="register.php" onsubmit="return validate(this)">
                            <tr><td>Username</td>
                                <td><input type="text" maxlength="16" name="username"></td></tr>
                            <tr><td>Password</td>
                                <td><input type="text" maxlength="12" name="password"></td></tr>
                            <tr><td>Email</td>
                                <td><input type="text" maxlength="64" name="email"></td></tr>
                            <tr><td colspan="2" align="center"><input type="submit" name="submit"
                                value="Sign Up"></td></tr>
                        </form>
                    </table><br>
                    <a href=authenticate.php>Sign in instead</a><br><br>
                    <a href=Homepage.php>Go home</a><br><br>
                </body>
            </html>
_END;

    $table = "CREATE TABLE IF NOT EXISTS users (
            username VARCHAR(32) NOT NULL UNIQUE,
            password VARCHAR(32) NOT NULL,
            email VARCHAR(50) NOT NULL
        )";

    $result = $conn->query($table);

    if (isset($_POST['submit'])) {
        $username   = mysql_entities_fix_string($conn, $_POST['username']);
        $password   = mysql_entities_fix_string($conn, $_POST['password']);
        $email      = mysql_entities_fix_string($conn, $_POST['email']);

        if (validate($username, $password, $email)) {
            $token = password_hash($password, PASSWORD_DEFAULT);    // Securely generate hash with salt for given password
            add_user($conn, $username, $token, $email);
            echo "Welcome $username!<br>";
            die ("<a href=authenticate.php>Click here to log in.</a>");
            $result->close();
            $conn->close();
        }
    }

    function add_user($conn, $username, $token, $email) {
        $stmt = $conn->prepare('INSERT INTO users VALUES(?,?,?)');
        $stmt->bind_param('sss', $username, $token, $email);
        $stmt->execute();
        $stmt->close();
    }

    function validate($username, $password, $email) {
        if (!validate_username($username) == "" || !validate_password($password) == "" || !validate_email($email) == "")
            return FALSE;
        return TRUE;
    }

    function validate_username($field) {
        $min_characters = 5;
        if (preg_replace('/\s+/', '', $field) == '') return "No username was entered<br>";
        else if (strlen($field) < $min_characters)
            return "Usernames must be at least $min_characters characters<br>";
        else if (preg_match("/[^a-zA-Z0-9_-]/", $field))
            return "Usernames can only consist of letters, numbers, - and _<br>";
        return "";
    }
    function validate_password($field) {
        $min_characters = 6;
        if (preg_replace('/\s+/', '', $field) == '') return "No password was entered<br>";
        else if (strlen($field) < $min_characters)
            return "Passwords must be at least $min_characters characters<br>";
        return "";
    }
    function validate_email($field) {
        if (preg_replace('/\s+/', '', $field) == '') return "No email was entered<br>"; 
        else if (!preg_match("/\S+@\S+\.\S+/", $field))
            return "The email address is invalid<br>";
        return "";
    }
?>