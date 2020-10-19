<!DOCTYPE html>
<html>

<head>
    <!-- links for CSS  -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
  rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <!-- links for jQuery library & local scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script> 
    <script src="scripts/polyfills.js"></script>
    <script src="scripts/script.js"></script>
<?php
    require_once 'include.php'; 
    if(empty($_SESSION["auth_user_id"])):
         header('Location: login.php');        // No active user. Re-direct to login page
    endif;  
?>
    
	<title>Assign Chores</title>
</head>
<body class = 'bg-light'>
   
<?php
    $_HOSTNAME = $_SESSION['db_host'];
    $_USERNAME = $_SESSION['db_login'];
    $_PASSWORD = $_SESSION['db_pw'];
    $_DBNAME = $_SESSION['db_table']; 
    $payperiod_id = $_SESSION['current_payperiod_id'];
    
    $today = date("g:i a O, m-d-Y");
    $DebugMode = false;
    $authenticated = FALSE;
    

    $assigner_id = !empty($_SESSION["auth_user_id"]) ? $_SESSION["auth_user_id"]:"";
    $name = !empty($_REQUEST["name"]) ? $_REQUEST["name"]:"";
    $job_name =  !empty($_REQUEST["job_name"]) ? $_REQUEST["job_name"]:"";             
    $isRecurring = !empty($_REQUEST["recurring"]) ? $_REQUEST["recurring"]:"";
    $job_category = !empty($_REQUEST["job_category"]) ? $_REQUEST["job_category"]:"";
    $job_pay = !empty($_REQUEST["job_pay"]) ? $_REQUEST["job_pay"]:"";
    $payprd_start = !empty($_REQUEST["payprd_start"]) ? $_REQUEST["payprd_start"]:"";
    $payprd_isValid = true;
    $isValid = TRUE;

    $day_of_week =   !empty($_REQUEST["start_duedate"]) ? date("l", strtotime($_REQUEST["start_duedate"])):"";
    
    $start_duedate = !empty($_REQUEST["start_duedate"]) ? $_REQUEST["start_duedate"]:"";
    $due_timestamp = strtotime("{$day_of_week} this week"); // Finds the requested day of the week prior to the next Sunday (i.e. start of 'this [upcoming] week')
    $due_date = date('m-d-Y', $due_timestamp); 
    
    $due_hours = !empty($_REQUEST["due_hours"]) ? $_REQUEST["due_hours"]:"";
    $due_minutes = !empty($_REQUEST["due_minutes"]) ? $_REQUEST["due_minutes"]:"";
    $due_am_pm =  !empty($_REQUEST["due_am_pm"]) ? $_REQUEST["due_am_pm"]:"";
    $due_timeDate = $day_of_week.", ".$due_date." at ".$due_hours.":".$due_minutes.$due_am_pm;
    

    if($DebugMode):
        echo "<div class=\"alert alert-success\" role=\"alert\">"
        ."The SESSION superglobal contains: <br>";
        print_r($_SESSION);
        echo "</div>";     
    endif;

//        if($pp_dayOfWeekInt != 'Sunday' ):
//           echo " <div class='alert alert-danger' role'alert'>"
//           ."Invalid 'Week Beginning' date (must be a Sunday)</div>";
//           global $isValid;
//           $isValid = FALSE;
//           global $payprd_isValid;
//           $payprd_isValid = FALSE;

//       endif;

    if(!isset($_REQUEST['submit-assignment'])):
        echo "<div class=\"alert alert-success\" role=\"alert\">"
        . "Welcome back, {$_SESSION['auth_first_name']}! Use this screen to assign chores and review chore assignments.</div>";
    
    else:
        $connection = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
        if ($connection -> connect_errno):
            $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection -> connect_error .");</script>";
            echo "alert({$errStr});";
            exit();
        else: // If connection to DB is successful
            if($isValid):
                if($DebugMode) {
                    echo "<script>alert('Passed validation.');</script>";
                }
                
                // Update DB with (validated) chore assignment
                global $isValid;
                global $assigner_id;
                global $payprd_start;
                global $job_id;
                global $due_timeDate;

                // Use prepared statements like the following to improve security
//                    $stmt = $connection->prepare('INSERT INTO assignment VALUES (?,?,?,?,?,?,?,?,?');
//                    $stmt->bind_param('iiiisdsss', $assigner_id, $assignee_id, $payperiod_id, $job_id, $job_category, $job_pay, $start_date, $createdAt, $updatedAt);
//                    
//                    $assigner_id = '';
//                    $assignee_id = ;
//                    $payperiod_id = '';
//                    $job_id = '';
//                    $j_category = $job_category;
//                    $j_pay = $job_pay;
//                    $start_date = $due_timeDate;;
//                    $createdAt = '';
//                    $updatedAt = '';
//                    
//                    $stmt->execute();
//                    echo "alert('Success! Chore has been assigned.');";
//                    $stmt->close();

                
                
                
                
                $timestamp = date("Y-m-d H:i:s ");

                require_once 'payperiod.php'; 

                $addChoreQuery = 
                        "INSERT INTO assignment 
                        (createdAt, updatedAt, assigner_id, job_category, job_pay, day_of_week, start_duedate, assignee_id, payperiod_id, job_id)" 
                        ." VALUES ('{$timestamp}', '{$timestamp}', '{$assigner_id}', '{$job_category}', '{$job_pay}', '{$day_of_week}', '{$due_date}'," 
                        ."(SELECT user_id FROM user WHERE first_name = '{$name}'),"
                        ."'{$payperiod_id}',"
                        ."(SELECT job_id FROM job WHERE job_name = '{$job_name }'));";
                        

                        
                if($DebugMode):
                    echo "<script>alert(mySQL query: {$addChoreQuery});</script>";
                endif;
                        
                if (mysqli_query($connection, $addChoreQuery )):
                  $last_id = mysqli_insert_id($connection);
                  
                  echo "<div class=\"alert alert-success\" role=\"alert\">"
                  . "Chore assignment '{$job_name}' was created successfully with a completion deadline of {$due_timeDate}. (Updated {$timestamp})</div>";
                else:
                  echo "<div>Error updating database.<br>".mysqli_error($connection)."</div>";
                   echo "<div>Query:{$addChoreQuery}</div>";
                endif;
            endif; // end $isValid
        endif; // else connection is good...
        $connection -> close();
    endif; // else attempt connection...
        
?>

    <form action="assign-jobs.php" method="get" name = "form_assignment" onsubmit="return get_date()">
        <div class="container text-center bg-light">
            <!-- Uses bootstrap 12-column grid system  -->
            
            <!-- 'ASSIGN A CHORE' (SCREEN TITLE) -->    
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class = "m-4">Assign a Chore</h1>
                </div>
            </div> 
            
            <div class="row"> <!-- Row for Login/Logout and Register links  -->
                <div class="col-11">
                    <div class="row">
                        <div class="col-12 text-right">
                            <a href='index.php'>
                            <i class='material-icons home-icon'>home</i>
                            </a>
                        </div>
                        
                        <div class="col-12 text-right">                        
                            <?php
                                if(empty($_SESSION["auth_user_id"])):
                                    echo "<a href = 'login.php'>Login</a>";
                                else:
                                    $_SESSION['log_out_user']= true;
                                    echo "<a href = 'logout.php'>Log Out | </a>";
                                endif;
                            ?>
                            <a href = "register.php">Register</a>    
                        </div> 

                    </div>    <!-- End row  -->
                </div> <!-- End .col-11 -->
                
            <!-- Right spacer after login/logout links  -->
            <div class="col-1"></div>  

            <!-- LEFT MAIN column (spacer)  -->
            <div class="col-1"></div>  

            <!-- CENTER COLUMN (MAIN CONTENT  -->
            <div class="col-10 text-center bg-light border bg-white pt-3 pb-1 mb-4">
                <!-- 
                ##########################
                BEGINNING 1st NESTED ROW
                ##########################
                -->
                <div class = "row"> <!-- Begin 1st nested row-->
                          <!-- FIELD LABEL  --> 
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="name">
                                Child / Worker:
                                </label>
                            </p>                            
                        </div> <!-- End col-4 (row 1) -->  

                        <!-- NAME DROPDOWN LIST -->                         
                        <div class = "col-4">
                            <p  class = "text-left">
                                <select 
                                    class = "w-75" 
                                    id="name" 
                                    name="name" 
                                    required> 
                                    <option 
                                        value="blank"
                                        <?php
                                            if(empty($_REQUEST)):
                                                echo " selected>";
                                            elseif($_REQUEST["name"] == "blank"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        (Select one)
                                    </option>

                                <?php
                                    $connection2 = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
                                    if ($connection2 -> connect_errno):
                                        $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection2 -> connect_error .");</script>";
                                        echo "alert({$errStr});";
                                        exit();


                                    else:
                                        $getUsersQuery = "SELECT first_name FROM user WHERE role = 'child' ORDER BY first_name ASC";
                                        $userResults = $connection2-> query($getUsersQuery);

                                        echo "<script>alert(Database connection successful.);</script>";

                                        while ($row = $userResults -> fetch_assoc()):
                                            $f_name = ($row[first_name]);
                                            if($f_name  === $_REQUEST['name']):
                                              $selected = "selected";
                                            else: 
                                                $selected = "";
                                            endif;
                                            echo "<option value = {$f_name} {$selected}>
                                                    {$f_name}
                                                </option>
                                            ".PHP_EOL;
                                        endwhile;


                                    endif;

                                    $connection2 -> close();

                                ?>
                                </select>
                            </p>
                        </div>   <!-- End col-4 (row 1) -->  
                        <div class = 'col-4'>
                            <a href = 'register.php'><p class='text-left'><i class = 'material-icons'>add_circle</i>Add a child</p></a>
                            
                        </div>
                    </div>     <!-- End 1st nested row--> 

            <!-- 
            #######################################
            BEGINNING 2nd NESTED ROW: JOBS LIST
            #######################################
            -->

                <!-- FIELD LABEL  --> 
                <div class = "row"> <!-- Begin 2nd nested row-->        
                    <div class = "col-4">
                        <p class ="text-right font-weight-bold">
                            <label  for="job_name">
                            Chore Assignment
                            </label>
                        </p>
                    </div> <!-- End col-4 (row 1) -->  

                    <!-- Job Selection Dropdown  -->                      
                    <div class = "col-4">
                        <p  class = "text-left">
                            <select 
                                class = "w-75" 
                                id="job_name" 
                                name="job_name" 
                                required>
                                <option 
                                    value=""
                                    <?php
                                        if(empty($_REQUEST)):
                                            echo " selected>";
//                                        elseif($_REQUEST["name"] == "blank"):
//                                            echo " selected>";
                                        else:
                                            echo ">";
                                        endif;
                                    ?>
                                    Select a chore
                                </option>

                                <?php
                                    $connection3 = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
                                    if ($connection3 -> connect_errno):
                                        $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection3 -> connect_error .");</script>";
                                        echo "alert({$errStr});";
                                        exit();


                                    else:
                                        $getJobsQuery = "SELECT job_name FROM job ORDER BY job_name ASC";
                                        $jobResults = ($connection3-> query($getJobsQuery));

                                        while ($row = $jobResults -> fetch_assoc()):
                                            $job = ($row['job_name']);
                                            $selected = "";

                                            if($job == $_REQUEST['job_name']):
                                                $selected = "selected";
                                            else: 
                                                $selected = "";
                                            endif;

                                            echo "<option value = '{$job}' {$selected}>
                                                    {$job}
                                                </option>".PHP_EOL;
                                        endwhile;
                                         $connection3 -> close();
                                    endif;
                                ?>
                            </select>
                        </p>
                    </div>   <!-- End col-4 (row 2) -->  
                    <div class = 'col-4'>
                        <a href = 'create-chore.php'><p class='text-left'><i class = 'material-icons'>add_circle</i>New chore</p></a>

                    </div>                    
                </div> <!-- End nested ROW #2  -->    


            <!-- 
            #######################################
            BEGINNING 3rd NESTED ROW:Job category               
            #######################################
            -->
                <div class = "row"> <!-- Begin 3rd nested row-->
                    <div class = "col-4">
                        <p class ="text-right font-weight-bold">
                            <label  for="job_category">
                            Chore Category:
                            </label>
                        </p>

                    </div> <!-- End col-4 (row 1) -->  
                    <div class = "col-8">
                        <p class ="text-left font-weight-bold">
                            <select                           
                                id="job_category" 

                                name="job_category" 
                                style = "width: 120px">

                                <?php
                                    for($i=0; $i < 3 ; $i++):
                                        $category_arr = ['allowance', 'extra','penalty'];
                                        $value = $category_arr[$i];
                                        $selection_status = "";
                                        if(empty($_REQUEST["job_category"])): // Set default pay to 'allowance'
                                            if($i == 0):
                                                $selection_status = " selected";
                                            endif;
                                        elseif($value == $_REQUEST['job_category']): // Remember user's selectionafter submission
                                            $selection_status = " selected";
                                        else:
                                            $selection_status = "";                                                
                                        endif;

                                        echo "<option value = {$value}{$selection_status}>{$value}</option>";
                                    endfor;
                                ?>

                         <p class ="text-right font-weight-bold">    
                            </select>    
                    </div>   <!-- End col-8 (row 1) -->  
                </div>     <!-- End 3rd nested row-->    

            <!-- 
            #######################################
            BEGINNING 4th NESTED ROW:Job Pay               
            #######################################
            -->

                <div class = "row"> <!-- Begin 4th nested row-->
                    <div class = "col-4">
                        <p class ="text-right font-weight-bold">
                            <label  for="job_category">
                            Job Value:
                            </label>
                        </p>

                    </div> <!-- End col-4 (row 1) -->  
                    <div class = "col-8">
                        <p class ="text-left font-weight-bold">
                            <select                           
                                id="job_pay" 

                                name="job_pay" 
                                style = "width: 80px">

                                <?php
                                    for($i=0; $i<=20.0 ; $i+=0.5):
                                        $selection_status = "";

                                        if(empty($_REQUEST["job_pay"])): // Set deafult pay to $1
                                            if($i == 1):
                                                $selection_status = " selected";
                                            endif;
                                        elseif($i == $_REQUEST['job_pay']):
                                            $selection_status = " selected";
                                        else:
                                            $selection_status = "";                                                
                                        endif;
                                        $dollar_value = number_format($i, 2, '.', '');
                                        echo "<option value = {$dollar_value}{$selection_status}>"."$"."{$dollar_value}</option>";
                                    endfor;
                                ?>

                         <p class ="text-right font-weight-bold">    
                            </select>    
                    </div>   <!-- End col-8 (row 1) -->  
                </div>     <!-- End 3rd nested row-->    


           <!-- 
           #########################################
            BEGINNING 5th NESTED ROW : DEADLINE
           #########################################
            -->
                <div class = "row"> <!-- Begin 5th nested row-->
                    <div class = "col-4">
                        <p class ="text-right font-weight-bold">
                            <label  for="start_duedate">
                            Start date (due date):
                            </label>
                        </p>                            
                    </div> <!-- End col-4 (row 1) -->  

                    <!-- Date Picker -->
                    <div class = "col-8">
                        <p  class = "text-left">
                            <input  type ='date' 
                                    required 
                                    id = 'start_duedate'
                                    name = 'start_duedate' 
                                    value = 
                                        <?php
                                            if(isset($_REQUEST['start_duedate'])):
                                                echo "{$_REQUEST['start_duedate']}";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                        </p>                         
                    </div>   <!-- End col-8 -->  
                </div> <!-- End ROW -->  

                <!-- New row -->                                 
                <div class = "row">
                    <div class = "col-4">
                        <p class ="text-right font-weight-bold">Due by:</p>
                    </div>

                    <div class = "col-8 ">
                         <!-- (Due: Hour) -->  
                        <p  class = "text-left float-left">
                            <!-- Due: Hours -->
                            <select                           
                                id="due_hours" 

                                name="due_hours" 
                                style = "width: 60px">
                                <?php
                                    for($i=1; $i<=12 ; $i++):
                                        $selection_status = "";
                                        echo "<option 
                                            value = {$i}"; 
                                            if(empty($_REQUEST['due_hours'])):
                                                if($i == "11"):
                                                    $selection_status = "selected";
                                                else:
                                                    $selection_status = "";
                                                endif;
                                            else:
                                                $selection_status = ($_REQUEST['due_hours'] == $i) ? "selected" :"";
                                            endif;
                                    echo " {$selection_status}> {$i} </option>";
                                    endfor;
                                ?>
                            </select>                                
                        </p>

                        <!-- Due: Minutes -->
                        <p  class = "text-left float-left ">
                            <select 
                                id="due_minutes" 
                                class = "ml-2 mr-2" 

                                name="due_minutes">

                                    <?php
                                        $minutes_arr = ['00', '15', '30', '45', '59'];
                                        $default_opt = 4;
                                        $selection_status = "";

                                        for($i = 0; $i<5; $i++):
                                            $current_val = $minutes_arr[$i];
                                            if(empty($_REQUEST['due_minutes'])):
                                                if($i == $default_opt):
                                                    $selection_status = 'selected';
                                                else: $selection_status = '';
                                                endif;
                                            elseif($minutes_arr[$i] == $_REQUEST['due_minutes']):
                                                $selection_status = 'selected';
                                            else: $selection_status = '';
                                            endif;

                                            echo "<option value = '{$current_val}' {$selection_status}>{$current_val}</option>";
                                        endfor;
                                    ?>
                            </select>

                        </p>    
                        <!-- AM or PM -->
                        <p  class = "text-left float-left">
                            <select 
                                id="due_am_pm" 

                                name="due_am_pm">
                                <?php
                                    $select_status_AM = "";
                                    $select_status_PM = "";
                                    $selection_status = '';
                                    if(empty($_REQUEST['due_am_pm']) OR ($_REQUEST['due_am_pm'] == 'PM')):
                                        $select_status_PM = " selected";
                                        $select_status_AM = ""; 

                                    elseif($_REQUEST['due_am_pm'] == 'AM'):
                                        $select_status_AM = " selected";
                                        $select_status_PM = "";
                                    else:
                                        $select_status_AM = "";
                                        $select_status_PM += "";
                                    endif;

                                    echo "<option value = 'AM'{$select_status_AM}>AM</option>";
                                    echo "<option value = 'PM'{$select_status_PM}>PM</option>";
                                ?>
                            </select>
                        </p>
                    </div>

                </div>     <!-- End 5th nested row-->                       
            <!-- 
            #########################################
            BEGINNING 6th NESTED ROW : Pay Period
            #########################################
            -->      
<!--                    <div class = "row">  Begin 6th nested row
                    <div class = "col-4">
                        <p class ="text-right font-weight-bold">
                            <label  for="payprd_start">
                            Week Beginning (Sunday):
                            </label>
                        </p>

                    </div>  End col-4 (row 1)   
                    <div class = "col-8">
                        <p  class = "text-left">


                                <input  
                                    type ='date' 
                                    required 
                                    id = 'payprd_start'
                                    name = 'payprd_start' 
                                <?php
//                                        global $payprd_isValid;
//                                        if(!$payprd_isValid) {
//                                            echo "class = 'bg-warning' ";
//                                        }
//
//                                        if(isset($_REQUEST['payprd_start'])) {
//                                            echo "value = {$_REQUEST['payprd_start']}";
//                                        }
//                                        echo ">";
                                ?>

                        </p>

                    </div>    End col-8 (row 1)   
                </div>      End 6th nested row    -->

            <!-- 
            #########################################
            BEGINNING 7th NESTED ROW : RECURRING?
           #########################################
            -->                   
                <div class = "row"> <!-- Begin 7th nested row-->
                    <div class = "col-4">
                        <p class ="text-right font-weight-bold">
                            <label  for="recurring">
                            Repeat Weekly?
                            </label>
                        </p>

                    </div> <!-- End col-4 (row 1) -->  
                    <div class = "col-4">
                        <p  class = "text-left">
                            <select 
                                class = "w-75" 
                                id="recurring"
                                name="recurring">

                                <option value ="Yes" 
                                    <?php
                                        if(!empty($_REQUEST["recurring"])):
                                            if($_REQUEST["recurring"]):
                                                echo " selected>";
                                            endif;
                                        else:
                                            echo ">";
                                        endif;
                                    ?>
                                    Yes
                                </option>

                                <option value = "No" 
                                    <?php
                                        if(!empty($_REQUEST["recurring"])):
                                            if(!$_REQUEST["recurring"]):
                                                echo " selected>";
                                            else: 
                                                echo ">";
                                            endif;
                                        else:
                                            echo ">";
                                        endif;
                                    ?>
                                    No
                                </option>
                            </select>
                        </p>
                    </div>   <!-- End col-8 -->  
                </div>     <!-- End 7th nested row-->    

                <!-- RIGHT MAIN column (spacer)  -->
                <div class="col-1"></div>  <!-- RIGHT SPACER  -->
                    

                </div> <!-- End of dropdown menus -->    
            </div>  <!-- End of white background -->
            <!-- 
                ##########################
                SUBMIT & REVIEW BUTTONS
                ##########################
                -->                                       
            <div class = "row pb-4 ">   
                <div class = "col-3 col-md-4"></div>

                <div class = "col-3 col-md-2">
                        <input type = "submit" class = "btn btn-block btn-success"  id = "submit-assignment" name = "submit-assignment" value = "Save">
                </div>

                <div class = "col-3 col-md-2">
                    <a class = 'btn btn btn-block btn-info text-white' 
                       <?php
                       echo "href='review-assignments.php?name={$name}'";
                        ?>
                    >Search Chores</a>
                </div>
                
                
                
                <div class = "col-3 col-md-4"></div>                        
            </div>     <!-- End row (Submit button) -->          
                    
            
        </div> <!-- End of container -->
    </form>   
    
    
    
    
    

        
<!-- RETRIEVE ASSIGNMENTS -->
<?php
    $connection2 = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
    echo "<script>alert(Attempting database connection...);</script>";

    if ($connection2 -> connect_errno):
        $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection -> connect_error .");</script>";
        echo "alert({$errStr});";
        exit();

    else: // if connection is OK
        echo "<script>alert(Database connection successful);</script>";
        $payperiod_id_current = $_SESSION['current_payperiod_id'];
        $payperiod_id_4wksAgo = $payperiod_id_current - 3;
        $weeks_value = !empty($_REQUEST['weeks']) ?? '0';
        $payperiod_query = '';
        $custom_pp_start_date = $_REQUEST['custom-weeks'] ?? '';

        switch($weeks_value):
            case('0'): 
                $payperiod_query = "a.payperiod_id = ".$payperiod_id_current;
                break;
            case('1'): 
                $payperiod_query = "a.payperiod_id >= ".$payperiod_id_4wksAgo;
                break;
            case('2'): 
                $payperiod_query = '(SELECT p.payperiod_id WHERE start_date >= '.$custom_pp_start_date;
                break;
        endswitch;


        $getAssignmentsQuery = 
            "SELECT "
                . "a.assigner_id, "
                . "uparent.first_name, "
                . "a.assignee_id, "
                . "a.assignment_id, "
                . "a.payperiod_id, "
                . "j.job_id, "
                . "j.job_name, "
                . "a.job_category, "
                . "a.job_pay, "
                . "a.day_of_week, "
                . "a.time_due, "
                . "a.start_duedate, "
                . "a.completion_date, "
                . "a.isCompleted, "
                . "a.isRecurring, "
                . "a.createdAt "
            . "FROM "
                . "assignment AS a "
                . "INNER JOIN user AS uparent ON a.assigner_id = uparent.user_id "
                . "INNER JOIN user AS uchild ON a.assignee_id = uchild.user_id "
                . "INNER JOIN job AS j ON a.job_id = j.job_id "
                . "INNER JOIN payperiod AS pp ON a.payperiod_id = pp.payperiod_id "
            . "WHERE "
//                        . "a.assigner_id = uparent.user_id AND "
                . "a.assignee_id = (SELECT uchild.user_id WHERE uchild.first_name = '{$name}') "
            . "AND "
                . "{$payperiod_query} "
//                        . "a.createdAt > 0 AND "
//                        . ""
            . "ORDER BY "
//                        . "a.assignment_id, "
                . "a.start_duedate, "
                . "a.time_due DESC";

        
        if($DebugMode) {
            echo $getAssignmentsQuery;
        }

        global $DebugMode;
        if($DebugMode):
            echo $getAssignmentsQuery;
        endif;

        $assignmentResults = $connection2->query($getAssignmentsQuery);

        while ($row_assignment = $assignmentResults->fetch_assoc()):
            $id = ($row_assignment['assignment_id']);
            $job_name = ($row_assignment['job_name']);
            $job_category = ($row_assignment['job_category']);
            $job_pay = ($row_assignment['job_pay']);
            $due_day = ($row_assignment['day_of_week']);
            $due_time = ($row_assignment['time_due']);
            $duedate =  ($row_assignment['start_duedate']);
            $isCompleted = $row_assignment['isCompleted'] != 0 ? "Yes":"No";
            $completion_date = ($row_assignment['completion_date']);
            $isRecurring = $row_assignment['isRecurring'] ? "Yes":"No";
            $duedate = ($row_assignment['start_duedate']);

            echo "
                <div class='row border-top border-bottom ml-5 mr-5'>
                    <!-- 'Results: Table Headers' -->                         
                    <div class='col-1 text-left'></div>

                    <div class= col-2 text-left'>{$job_name}</div>

                    <div class='col-1 text-left'>{$job_category}</div>

                    <div class='col-1 text-left'>"."$"."{$job_pay}</div>

                    <div class='col-1 text-left'>{$due_time}</div>

                    <div class='col-1 text-left'>{$due_day}</div>

                    <div class='col-1 text-left'>{$duedate}</div>

                    <div class='col-1 text-left'>{$isRecurring}</div>

                    <div class='col-1 text-left'>{$isCompleted}</div>            

                    <div class='col-1 text-left'>
                        <a  class='btn btn-block btn-danger' href = 'process-edits.php?source=review-assignments.php&name={$name}&delete={$id}';>Delete</a></div>

                    <div class='col-1 text-left'>
                        <a  class='btn btn-block btn-warning' href = 'process-edits.php?source=review-assignments.php&name={$name}&edit={$id}';>Edit</a></div>

                </div> <!-- End .row -->
            ";
        endwhile;
    endif;
    $connection2 -> close();
?>
</body>
</html>