<!DOCTYPE html>
<html>

<head>
    <!-- links for CSS  -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <!-- links for jQuery library & local scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script> 
    <script src="scripts/polyfills.js"></script>
    <script src="scripts/script.js"></script>
    
	<title>Chore Tracker Login</title>
</head>
<body>
    
<?php
    require_once 'include.php';
    $_HOSTNAME = $_SESSION['db_host'];
    $_USERNAME = $_SESSION['db_login'];
    $_PASSWORD = $_SESSION['db_pw'];
    $_DBNAME = $_SESSION['db_table']; 

    $nowFormatted = date("g:i a O, m-d-Y");
    $DebugMode = FALSE;


    $authenticated = FALSE;
    



    $connection = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
    if ($connection -> connect_errno):
        $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection -> connect_error .");</script>";
        echo "alert({$errStr});";
        exit();
    else: // If connection to DB is successful
        // Ensure user has submitted an email address and password
        if(!isset($_REQUEST["login"])): // If button not pushed/submitted
            echo " <div class=\"alert alert-success\" role=\"alert\">
            Hello! Please log into your Chore Tracker account.</div>";

        elseif (empty($_REQUEST["user"]) OR empty($_REQUEST["pw"])): 
            echo " <div class=\"alert alert-danger\" role=\"alert\">
            Incomplete submission. Enter your email and password.</div>";        
        else:
            // see notes in include.php re: get_formdata function
            $email = get_formdata($connection, "user"); // selected user
            $pw = get_formdata($connection, "pw");; // submitted pw
            $login = $_REQUEST["login"]; // Submit button value
            
            // Validate login against database
            $sql = "SELECT * FROM user WHERE email = \"$email\"";
            //DEBUG
            if($DebugMode){
                echo "<script>alert('Submitting the following query: {$sql}');</script>";}

            $queryResult = $connection-> query($sql);


            $row = $queryResult-> fetch_assoc();
            $sql_hash = $row["password_hash"];
            //DEBUG
            if($DebugMode){
                echo "<script>alert(\"Retrieving hash from query result.\");</script>";}
            
            $firstName = $row["first_name"];
            $lastName =  $row["last_name"];
            $userId =  $row["user_id"];
            $role =  $row["role"];
            $timezone = $row["timezone"]; 

            // DEBUG
            if($DebugMode) {
                echo "<script>alert('Verifying password \"{$pw}\" against hash from DB: {$sql_hash}');</script>";}
            
            $pw = $_REQUEST["pw"];

            global $authenticated;
            $authenticated = password_verify($pw, $sql_hash); 
            
            if($authenticated):
//                $_SESSION["local_timestamp"] = $_REQUEST["local_timestamp"];  // This approach attempts to use JS to retrieve and use local tim and stores in $_SESSION. Problem: This means the time retrieved from $_SESSION is not the actual current time
                $_SESSION["auth_timezone"] = $row["timezone"];
                $_SESSION["auth_user_id"] = $row["user_id"];
                $_SESSION["auth_first_name"] = $row["first_name"];
                $_SESSION["auth_last_name"] = $row["last_name"];
                $_SESSION["auth_role"] = $row["role"];
                $_SESSION["authenticated"] = TRUE;
        
                header("Location: index.php");

                // DEBUG
                if($DebugMode):
                    echo "<div class=\"alert alert-success\" role=\"alert\">
                        Successful login. Current values in SESSION are:<br>";    
                        print_r($_SESSION);                
                        echo ");</div>";

                    echo "<div class=\"alert alert-success\" role=\"alert\">
                    Welcome, {$_SESSION['auth_first_name']} {$_SESSION['auth_last_name']}. Submission received at {$nowFormatted}. Your user ID is # {$_SESSION['auth_user_id']} and your role is: {$_SESSION['auth_role']}</div>";
                endif;    
            else: // If not authenticated
                echo "<div class=\"alert alert-danger\" role=\"alert\">
                Incorrect password. (You entered: '".$_REQUEST["pw"]."')</div>";
            endif;
        endif;
    endif;

    $connection -> close();

?>




    <div class="container text-center bg-light">
        <form action="login.php" method="get" name = "form_login">
            
            <!-- Uses bootstrap 12-column grid system  -->
            <div class="row">
                <!-- 'CHORE TRACKER' (SCREEN TITLE) -->    
                <div class="col-12 text-center">
                            <h1 class = "m-4">Chore Tracker</h1>
                </div>
                <div class="col-3 text-center"></div>
                
                <div class="col-6 text-center bg-light border bg-white pt-3 pb-1 mb-4">
                    <div class = "row"> <!-- Begin 1st nested row-->
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="user">
                                Email:
                                </label>
                            </p>
                            
                        </div> <!-- End col-4 (row 1) -->  
                        <div class = "col-8">
                            <p  class = "text-left">
                                <select 
                                    class = "w-75" 
                                    id="email_list" 
                                    name="user">
                                    <option 
                                        value=""
                                        <?php
                                            if(empty($_REQUEST["user"])):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        Select your email
                                    </option>
                                
<?php
        $connection2 = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
        if ($connection2 -> connect_errno):
            $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection2 -> connect_error .");</script>";
            echo "alert({$errStr});";
            exit();
        

        else:
            $getUsersQuery = "SELECT email FROM user ORDER BY email ASC";
            $userResults = $connection2-> query($getUsersQuery);
            
            while ($row = $userResults -> fetch_assoc()):
                $eml = ($row[email]);
                if($eml === $_REQUEST['user']):
                  $selected = "selected";
                else: 
                    $selected = "";
                endif;
                echo "
                    <option value = {$eml} {$selected}>
                        {$eml}
                    </option>
                ".PHP_EOL;
            endwhile;

    
        endif;
        
        $connection2 -> close();

    ?>

                                </select>
                            </p>

                        </div>   <!-- End col-8 (row 1) -->  
                    </div>     <!-- End 1st nested row-->    

                    <div class = "row"> <!-- Begin 2nd nested row-->        
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="pw">
                                Password:
                                </label>
                            </p>
<!--                            <p class ="text-right font-weight-bold pt-2 d-none">
                                <label  for="sql_hash">
                                Local Time:
                                </label>
                            </p>                             -->
                            
                        </div> <!-- End col-4 (row 1) -->  
                        <div class = "col-8">
                            <p class = "text-left">
                                <input 
                                    type = "password" 
                                    class = "w-75"  
                                    id = "pw" 
                                    name = "pw" 
                                    value = "<?php
                                            $pwValue = $_REQUEST['pw'] ?? "";
                                            echo $pwValue;
                                        ?>">
                        
                            </p>

<!--                            <p>        
                                <input 
                                    class = "w-100 d-none"
                                    type = "hidden" 
                                    name = "local_timestamp" 
                                    id ="localTimestamp"
                                    placeholder = "JS-calculated timestamp..."
                                    value = 
                            </p>-->
 
                                   
                            
                             <p class = "text-left details"><a href = "register.php">New user? Register here.</a></p>           
                        </div>
                    </div>     <!-- End 2nd nested row-->        


                        
                    </div> <!-- End col-8  -->    
                    
                   <!-- RIGHT MAIN column (spacer)  -->
                    <div class="col-2"></div>
                </div>
                    <div class = "row pb-4"> <!-- Begin 3rd nested row-->        
                        <div class = "col-3 col-md-4"></div>
                        <div class = "col-6 col-md-4">
                            <p>
                                <input type = "submit" class = "btn btn-block btn-success"  id = "save" name = "login" value="Submit">
                            </p>
                        </div>
                        <div class = "col-3 col-md-4"></div>                        
                    </div>     <!-- End 3rd nested row-->

            </div> <!-- End of login box row -->

        </form>
    </div> <!-- End of container -->
</body>
</html>