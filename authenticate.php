<?php
    require_once 'login.php';       // Require login file to connect to MySQL database
    require_once 'helperFunctions.php';    // Require sanitizing functions
    
    $conn = new mysqli($hn, $un, $pw, $db);             // Create connection object
    if ($conn->connect_error) die(mysql_fatal_error()); // If it can't connect, run error function
    
    // Check if username and password have been set
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->bind_param('s', $un_temp);
        $un_temp = mysql_entities_fix_string($conn, $_SERVER['PHP_AUTH_USER']); // Get inputted username
        $pw_temp = mysql_entities_fix_string($conn, $_SERVER['PHP_AUTH_PW']);   // Get inputted password
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (!$result) die(mysql_fatal_error());
        
        // Check if there are matching usernames in database
		elseif ($result->num_rows) {
			$row = $result->fetch_array(MYSQLI_NUM);
			$result->close();

            if (password_verify($pw_temp, $row[1])) { // if password matches hash in database
                session_start();
                $_SESSION['username'] = $un_temp;
                $_SESSION['password'] = $token;
                $_SESSION['email'] = $row[2];
                echo "Welcome row[0]!";
                die ("<a href=Homepage.php>Click here to access main page</a>");
            }
			else die("Invalid username/password combination");  // Incorrect password
		}
		else die("Invalid username/password combination");  // Incorrect username
    }
    else {
        header('WWW-Authenticate: Basic realm="Restricted Section"');
        header('HTTP/1.0 401 Unauthorized');
        die ("Please enter your username and password to continue.");
    }

    $conn->close();
?>