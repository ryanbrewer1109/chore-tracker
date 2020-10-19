<!DOCTYPE html>
<html>
<head>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <script src="scripts/polyfills.js"></script>
    <script src="scripts/script.js"></script>
    
	<title>Chore Tracker Login</title>
</head>
<body>

<?php  
    // force user logout
    if($_SESSION['log_out_user']= true):
        session_start(); // Prevents error "Trying to destroy uninitialized session"
        session_unset(); // unsets session_id
        session_destroy();   
    endif;

    require_once 'include.php';

    $_HOSTNAME = $_SESSION['db_host'];
    $_USERNAME = $_SESSION['db_login'];
    $_PASSWORD = $_SESSION['db_pw'];
    $_DBNAME = $_SESSION['db_table']; 

    $_homepage = "/index.php";
    $user_email_list = [];
    $isDuplicate = FALSE;
    $validSubmission;    
    $noSubmissionData; // Boolean affecting whether blank fields should be highlighted or not


    // Connect to DB; if unsuccessful, stop script
    $connection = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
    if ($connection -> connect_errno):
        $errStr= " <div class=\"alert alert-danger\" role=\"alert\">
           Failed to connect to MySQL. Reason: " .  $connection -> connect_error {$errStr} . "</div>";
        echo $errStr;
        exit();

    // if connection is successful...
    else:
        // return value from submission, if exists; else, return FALSE
        // see notes in include.php re: get_formdata function
        $email = !empty($_REQUEST["email"]) ?  get_formdata($connection, "email") : FALSE;
        $role = !empty($_REQUEST["role"]) ? get_formdata($connection, "role") : FALSE;        
        $f_name = !empty($_REQUEST["f_name"]) ? get_formdata($connection, "f_name") : FALSE;
        $l_name = !empty($_REQUEST["l_name"]) ? get_formdata($connection, "l_name") : FALSE;
        $pwd_1 = !empty($_REQUEST["new_pw"]) ? get_formdata($connection, "new_pw") : FALSE;
        $pwd_2 = !empty($_REQUEST["pw_confirm"]) ? get_formdata($connection, "pw_confirm") : FALSE;
        $pw_hash =  encryptPassword($pwd_1); 
        
        $timezone = (!empty($_REQUEST["timezone"]) AND ($_REQUEST["timezone"] !== "blank")) ? get_formdata($connection, "timezone") : "blank";  // Stores user-selected PHP name for timezone(e.g., 'America/North_Dakota/New_Salem')
        $timestamp = !empty($_REQUEST["timestamp"]) ? get_formdata($connection, "timestamp") : time();

        global $validSubmission;
        // Display this if user has not yet submitted the form on this page
        if(empty($_REQUEST)):
            $noSubmissionData = TRUE;
            echo " <div class=\"alert alert-success\" role=\"alert\">
            Ready to create a new account? Let's get started!</div>";

        // If submit button was clicked... 
        elseif(isset($_REQUEST["register"])):
            $noSubmissionData = FALSE;
            // retrieve all email addresses from DB
            $queryUserEmail = "SELECT email FROM user";
            $result = $connection-> query($queryUserEmail);
            echo "</div>";
        
            // determine whether submitted email already exists in DB 
            while ($row = $result -> fetch_assoc()):
                $userEmailAddress = $row["email"];
                if($userEmailAddress == ($_REQUEST["email"])):
                    global $isDuplicate;
                    $isDuplicate = TRUE;

                endif;
                
            array_push($user_email_list, $userEmailAddress);
            endwhile;
            
            // Validate submitted data 
            // DEBUG
//            echo " <script>alert(\"Validating submission...\")</script>";
            validate_form_data($user_email_list);
            //DEBUG
//            echo " <script>alert(\"Validation result: {$validSubmission}\")</script>";
             // If submitted registration is valid, update DB

            if($validSubmission):
                $_SESSION["auth_first_name"] = $_REQUEST["f_name"];
                $_SESSION["auth_last_name"] = $_REQUEST["l_name"];
                $_SESSION["auth_role"] = $_REQUEST["role"];
//                $_SESSION["local_timestamp"] = $_REQUEST["local_timestamp"];   // This approach attempts to use JS to retrieve and use local tim and stores in $_SESSION. Problem: This means the time retrieved from $_SESSION is not the actual current time
                $_SESSION["auth_timezone"] = $_REQUEST["timezone"]; // Stores user-selected PHP name for timezone(e.g., 'America/North_Dakota/New_Salem')
                $sqlCreateAccount = "INSERT INTO user (email, role, first_name, last_name, password_hash, timezone, createdAt, updatedAt, is_active) VALUES ('{$email}', '{$role}', '{$f_name}', '{$l_name}', '{$pw_hash}','{$timezone}','{$timestamp}', '{$timestamp}', 'TRUE')";
                // DEBUG
                               
//                echo " <script>alert(\"Executing this SQL query: {$sqlCreateAccount}\")</script>";
//                $connection->query($sqlCreateAccount); // Can alternatively use this syntax (if not using the if statement below)
                if (mysqli_query($connection, $sqlCreateAccount)) {
                  $last_id = mysqli_insert_id($connection);
//                  echo "New record created successfully. Last inserted ID is: " . $last_id;
                } else {
                  echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
                $_SESSION["auth_user_id"] = $last_id;
                
                header("Location: index.php");
                
                echo " <div class=\"alert alert-success\" role=\"alert\">
                Thank you! Your registration was processed at {$timestamp}. 
                <a href = \"index.php\"></br>Click here to continue to your home screen.</a></div>";  
            endif;
        endif;
    endif;
    

function validate_form_data (array $emailArr) {
    global $isDuplicate;
    global $validSubmission;
    global $email;
    global $role; 
    global $f_name;
    global $l_name;
    global $pwd_1;
    global $pwd_2;
    global $timezone;
    // Initialize local boolean flag
    $isValid = TRUE;
    
    if(!empty($email) AND $isDuplicate):
        $isValid = FALSE;
        echo " <div class=\"alert alert-danger\" role=\"alert\">
        Submitted email address already has an account. Enter a diferent email or login to continue.</div>";
    endif;
    
    //$validEmail = boolval(!empty($_REQUEST["email"]));
    // if(!$validEmail):

    if(!$email): // Validate email field is not blank
        echo " <div class=\"alert alert-danger\" role=\"alert\">
        A valid email address is required.</div>";
        $isValid = FALSE;
    endif;
    
    if($role == "blank"): // Validate role is selected
        echo " <div class=\"alert alert-danger\" role=\"alert\">
        Select a role.</div>";
        $isValid = FALSE;
    endif;    
    

    // $validFirstName = !empty($_REQUEST["f_name"]);
    // if(!$validFirstName)

    if(!$f_name): // Validate first name field is not blank
        echo " <div class=\"alert alert-danger\" role=\"alert\">
        First name is required.</div>";
        $isValid = FALSE;
    endif;
    
    
    
    

    // $validPassword = !empty($_REQUEST["new_pw"]); 
    // if(!$validPassword):
    if(empty($pwd_1)): // Validate first pw field is not blank
        echo " <div class=\"alert alert-danger\" role=\"alert\">
          A password is required.</div>";
        $isValid = FALSE;
    endif;        
    
        
    // if($_REQUEST["new_pw"] !== $_REQUEST["pw_confirm"]):
    if($pwd_1 !== $pwd_2): // Validate password matches the password confirmation
        echo " <div class=\"alert alert-danger\" role=\"alert\">
        Your password and confirmed password do not match. Try again.</div>";
        $isValid = FALSE;
    endif;
    
    // Validate timezone selected
    if($timezone === "blank"): // Validate Time Zone has been selected
        echo " <div class=\"alert alert-danger\" role=\"alert\">
          Select your local time zone.</div>";
        $isValid = FALSE;
    endif;        
    
    $validSubmission = $isValid;
    
} // end of function


?>

    <div class="container text-center bg-light">
        <form action="register.php" method="get" name = "form_register" onsubmit="return get_date()">
            
            <!-- Uses bootstrap 12-column grid system  -->
            <div class="row">
                <!-- Title-->    
                <div class="col-12 text-center">
                            <h1 class = "m-4">Chore Tracker Registration</h1>
                </div>
                <div class="col-3 text-center"></div>
                
                <div class="col-6 text-center bg-light border bg-white pt-3 pb-1 mb-4">
                    
                    
                    <!-- EMAIL INPUT SECTION --> 
                    <div class = "row"> <!-- Begin 2nd nested row-->        
                        <div class = "col-4">  
                            <p class ="text-right font-weight-bold">
                                <label  for="email">
                                    <span class="text-danger">*</span>
                                Email:
                                </label>
                            </p>
                        </div>    
                         <!-- End col-4 (row 1) -->  
                        <div class = "col-8">
                            <p class = "text-left">
                                <input type = "email"
                                class = "w-100
                                     <?php 
                                        global $email; 
                                        if($noSubmissionData OR !empty($email)): // only highlight blank fields after form is submitted 
                                            echo " bg-light\" value = \"{$email}\" "; 
                                        else: echo " bg-warning\" placeholder = \"This field is required.\" "; 
                                        endif; 
                                    ?>                                 
                                id = "email" name = "email" required>
                            </p>
                 
                        </div>
                    </div>     <!-- End 2nd nested row-->    
                    

                        <!--ROLE INPUT SECTION -->                     
                    <div class = "row"> <!-- Begin 3rd nested row-->        
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                
                                <label  for="role" class = "text-nowrap">
                                    <span class="text-danger">*</span>
                                   Role
                                </label>
                            </p>
                            
                        </div> <!-- End .col-4 (row 1) -->  
                        <div class = "col-8">
                            <p class = "text-left">
                                <select 
                                    class = "w-100
                                        <?php 
                                            global $role; 
                                            if(($role == "blank")): // highlight dropdown field if submitted without a selection
                                                echo " bg-warning\" "; 
                                            else: echo " bg-light\" "; 
                                            endif; 
                                        ?>  
   
                                    id = "role" 
                                    name = "role" 
                                >                                        
                                        <option value = "blank">(Choose one)</option>
                                        <option value = "child"                                         
                                            <?php 
                                                global $role; 
                                                if($role === "child"): // Retain selection if form submission is rejected
                                                    echo " selected"; 
                                                endif; 
                                            ?>  
                                        >Child / Worker</option>
                                        <option value = "admin" 
                                            <?php 
                                                global $role; 
                                                if($role === "admin"): // Retain selection if form submission is rejected
                                                    echo " selected"; 
                                                endif; 
                                            ?>  
                                        >Parent / Chore Manager</option>
                                </select>
                            </p>
                 
                        </div>
                    </div>     <!-- End 3rd nested row-->    
                    
                    
                    <!--FIRST NAME INPUT SECTION -->                     
                    <div class = "row"> <!-- Begin 4th nested row-->        
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="f_name" class = "text-nowrap">
                                    <span class="text-danger">*</span>
                                    First Name
                                </label>
                            </p>
                            
                        </div> <!-- End .col-4 (row 1) -->  
                        <div class = "col-8">
                            <p class = "text-left">
                                <input type = "text"
                                id = "f_name" name = "f_name"
                                class = "w-100
                                    <?php 
                                        global $f_name; 
                                        if($noSubmissionData OR ($f_name !== FALSE)): // only highlight blank fields after form is submitted 
                                            echo " bg-light\" value = \"{$f_name}\" "; 
                                        else: echo " bg-warning\" 
                                        placeholder = \"This field is required.\" 
                                         "; 
                                        endif;
                                        echo "required >"
                                    ?>  
                                
                            </p>
                 
                        </div>
                    </div>     <!-- End 4th nested row--> 
                    
                    
                        <!--LAST NAME INPUT SECTION -->                     
                    <div class = "row"> <!-- Begin 5th nested row-->        
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="l_name">
                                    Last Name
                                </label>
                            </p>
                            
                        </div> <!-- End .col-4 (row 1) -->  
                        <div class = "col-8">
                            <p class = "text-left">
                                <input type = "text"
                                class = "bg-light"
                                value = 
                                    <?php global $l_name; echo " \"{$l_name}\" "; ?>  
                                id = "l_name" name = "l_name">
                            </p>
                 
                        </div>
                    </div>     <!-- End 5th nested row-->                        



                    <!-- PASSWORD INPUT SECTION --> 
                    <div class = "row"> <!-- Begin 6th nested row-->        
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="new_pw" class = "text-nowrap">
                                <span class="text-danger">*</span>
                                Password:
                                </label>
                            </p>
                            
                        </div> <!-- End .col-4 (row 1) -->  
                        <div class = "col-8">
                            <p class = "text-left">
                                <input type = "password" 
                                    class = "w-100 
                                      <?php 
                                            global $pwd_1; 
                                            if($noSubmissionData OR !empty($pwd_1)): // only highlight blank fields after form is submitted 
                                                echo " bg-light\" value = \"{$pwd_1}\" "; 
                                            else: echo " bg-warning\" 
                                            placeholder = \"This field is required.\" 
                                            required "; 
                                            endif; 
                                        ?>  
                                    id = "new_pw" name = "new_pw">
                            </p>
                 
                        </div>
                    </div>     <!-- End 6th nested row-->        



                    <!-- CONFIRM PASSWORD INPUT SECTION --> 
                    <div class = "row"> <!-- Begin 7th nested row-->        
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="pw_confirm">
                                <span class="text-danger">*</span>
                                Confirm Password:
                                </label>
                            </p>
                            
                        </div> <!-- End .col-4 (row 1) -->  
                        <div class = "col-8">
                            <p class = "text-left">
                                <input type = "password" 
                                    class = "w-100 
                                      <?php 
                                            global $pwd_2; 
                                            if($noSubmissionData OR !empty($pwd_2)): // only highlight blank fields after form is submitted 
                                                echo " bg-light\" value = \"{$pwd_2}\" "; 
                                            else: echo " bg-warning\" 
                                            placeholder = \"This field is required.\" 
                                            required "; 
                                            endif; 
                                        ?>  
                                    id = "pw_confirm" name = "pw_confirm">
                            </p>
                 
                        </div>
                    </div>     <!-- End 7th nested row-->   
                    
                    <!-- TIMEZONE INPUT SECTION --> 
                    <div class = "row"> <!-- Begin 8th nested row-->        
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="timezone">
                                <span class="text-danger">*</span>
                                Time Zone:
                                </label>
                            </p>
                            
                        </div> <!-- End .col-4 (row 1) -->  
                        <div>
                            <?php 
                                $timezone_identifiers = 
                                        DateTimeZone::listIdentifiers(DateTimeZone::ALL); 

                                $Africa = range(0, 51); 
                                $America = range(52, 198); 
                                $Asia = range(211, 292); 
                                $tz_stamp = time(); 

                                
                                
                                // highlight selection field if submitted with no selection
                                global $timezone;
                                $highlightClass;
                                if(empty($timezone) OR $timezone == "blank"):
                                    $highlightClass = "bg-light";
                                else:
                                    $highlightClass = "bg-warning";
                                endif;
                                
                                echo "<center>
                                        <select class = 'col-8 {$highlightClass}' 
                                                id = 'timezone'
                                                name = 'timezone'  required 
                                                style= 
                                                   'padding:5px 0; 
                                                    margin-bottom: 40px;
                                                    width: 300px;border:2px solid #111; 
                                                    outline: none;'>"; 
                                                    
                                echo "<option value='blank' class = 'bg-primary text-light'>(Select your Time Zone)
                                        </option>";     
                                echo "<option value='blank' class = 'bg-secondary text-light'><h3>AMERICA</h3> 
                                        </option>"; 

                                $selected_status;
                                foreach($America as $usa) {                                     
                                        $tzone[$usa] = date_default_timezone_set($timezone_identifiers[$usa]); 
                                        global $timezone; 
                                        $this_option = $timezone_identifiers[$usa] . 
                                                        ' @ ' . date('P', $tz_stamp);
                                        
                                        // Retain user's timezone selection if form submission is rejected
                                        global $timezone;
                                        if($_REQUEST["timezone"] === $this_option): 
                                            $selected_status = ' selected'; 
                                        else: 
                                             $selected_status = ''; 
                                        endif; 
                                echo "<option {$selected_status}>{$this_option}</option>"; 
                                } 

                                echo "<option value='blank' class = 'bg-secondary text-light'><h3>AFRICA</h3> 
                                        </option>"; 

                                foreach($Africa as $x) { 
                                        $tzone[$x] = date_default_timezone_set( 
                                                                                $timezone_identifiers[$x]); 

                                        echo "<option>" . $timezone_identifiers[$x] . 
                                                        ' @ ' . date('P', $tz_stamp);"</option>"; 
                                } 

                                echo "<option value='blank' class = 'bg-secondary text-light'><h3>ASIA</h3> 
                                        </option>"; 

                                foreach($Asia as $x) { 
                                        $tzone[$x] = date_default_timezone_set( 
                                                                                $timezone_identifiers[$x]); 

                                        echo "<option>" . $timezone_identifiers[$x] . 
                                                        ' @ ' . date('P', $tz_stamp);"</option>"; 
                                } 

                                echo "</select></center>"; 
                                ?> 
                                
                        </div>
                    </div>     <!-- End 8th nested row-->                       
                    
                    
                    
                    <!-- HIDDEN INPUTS -->                     
                    <input 
                        class = "d-none" 
                        type = "hidden"
                        name = "pw_hash" 
                        value = "<?php time(); ?>">
                  <input 
                        id="localTimestamp"
                        type = "hidden"
                        name = "local_timestamp" 
                        >
                      
                        
 
                    <!-- SUBMIT BUTTON -->    
                    <div class = "row pb-4"> <!-- Begin 9th nested row-->        
                        <div class = "col-3 col-md-4"></div>
                        <div class = "col-6 col-md-4">
                            <p>
                                <input type = "submit" class = "btn btn-block btn-success"  id = "save" name = "register"  onclick="get_date()">
                            </p>
                        </div>
                        <div class = "col-3 col-md-4"></div> 
                    </div> <!-- End 9th nested row--> 


                    <div class = "row pb-4"> <!-- Begin 10th nested row-->
                         <div class = "col-12">                           
                            <p class = "text-center details"><a href = "login.php">Finished registering? Return to Login screen</a>
                            </p>                                         
                            
                        </div>
                    </div> <!-- End 10th nested row--> 
                    
                   <!-- RIGHT MAIN column (spacer)  -->
                    <div class="col-2"></div>





                    </div>
                </div> <!-- End col-8  -->
            </div> <!-- End of login box row -->

        </form>
    </div> <!-- End of container -->
</body>
</html>