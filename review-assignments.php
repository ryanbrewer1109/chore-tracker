<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Review Assignments</title>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
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
        
        <title>Review Chore Assignments</title>
    </head>
    <?php

        $_HOSTNAME = $_SESSION['db_host'];
        $_USERNAME = $_SESSION['db_login'];
        $_PASSWORD = $_SESSION['db_pw'];                                  
        $_DBNAME = $_SESSION['db_table']; 


        $authenticated = $_SESSION["authenticated"] ?? FALSE;
        $authUserId = $_SESSION["auth_user_id"] ?? "NONE FOUND";
        $authFirstName = $_SESSION["auth_first_name"] ?? "NONE FOUND";
        $authLastName = $_SESSION["auth_last_name"] ?? "NONE FOUND";
        $authRole = $_SESSION["auth_role"] ?? "NONE FOUND";
//        $default_Timezone = 'Greenwich';
        $name = $_REQUEST["name"] ?? "";
        $custom_payperiod = 'false';
        $DebugMode = false;
    ?>
    
    
    <body style = "background-color: aliceblue">
    

        <form action="review-assignments.php" method="get" name = "form_review-assignments">
            <!-- 'REVIEW CHORE ASSIGNMENTS' (SCREEN TITLE) -->    
            <div class="container text-center bg-light">
                
                <div class="row justify-content-center" >
                    <!-- Uses bootstrap 12-column grid system  -->                
                
                    <div class="col-12 text-center">
                        <h1 class = "m-4">Review Chore Assignments</h1>
                    </div>
                </div> 
                

                <div class='row'>            
                    <div class='col-11'>
                        <div class='row'>
                            <!-- Gear icon for mobile -->
                            <div class = 'col-12 col-sm-12 text-right'>  
                            <?php
                                echo "<a href='index.php'>
                                    <i class='material-icons home-icon'>home</i>
                                </a>";

                                if($authenticated):
                                    echo "<a href='assign-jobs.php'>"
                                    ."<i class='material-icons settings-icon'>settings</i>"
                                    ."</a>";
                                else:
                                    echo "<i class='material-icons settings-icon'>settings</i>";
                                endif;
                            ?>                          
                            </div>

                            <div class='col-12 text-right'>
                                <?php
                                    if(empty($_SESSION['auth_user_id'])):
                                        echo "<a href = 'login.php'>Login</a>";
                                    else:
                                        $_SESSION['log_out_user']= true;
                                        echo "<a href = 'logout.php'>Log Out | </a>";
                                    endif;
                                    echo "<a href = 'register.php'>Register</a>";
                                ?>
                            </div>
                        </div> <!-- .row -->  
                    </div>  <!-- End of nav.row -->
                        
                    <!-- Right spacer after login/logout links  -->
                    <div class="col-1"></div>  
                    
                    <!-- LEFT MAIN column (spacer)  -->
                    <div class="col-1"></div>  
                    <!-- CENTER COLUMN (Dropdown Menus)  -->

                    <div class="col-10 text-center bg-light border bg-white pt-3 pb-1 mb-4">

                    <!-- 
                    #################################################
                    Weeks to Display (1st nested row): 
                    #################################################
                    -->
                    <div class = "row"> <!-- Begin 1st nested row-->
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="weeks">
                                Select weeks:
                                </label>
                            </p>

                        </div> <!-- End col-4 (row 1) -->  
                        <div class = "col-8">
                            <p class ="text-left font-weight-bold">
                                <select                           
                                    id="weeks" 
                                    name="weeks" 
                                    style = "width: 120px" 
                                    required>

                                    <?php
                                        for($i=0; $i < 3 ; $i++):
                                            $records_arr = ['Current week', 'Last 4 weeks', 'Select Dates'];
                                            $display_option = $records_arr[$i];
                                            $selection_status = "";
                                            if(empty($_REQUEST["weeks"])): // Set default pay to 'current'
                                                if($i == 0):
                                                    $selection_status = " selected";
                                                endif;
                                                
                                            elseif($display_option == $_REQUEST['weeks']): // Remember user's selection after submission
                                                $selection_status = " selected";
                                            else:
                                                $selection_status = "";                                                
                                            endif;

                                            echo "<option value = {$i}{$selection_status}>{$display_option}</option>";
                                            global $custom_payperiod;
                                            $custom_payperiod = ($selection_status == ' selected') ? 'true':'false';
                                                
                                        endfor;
                                    ?>

                             <p class ="text-right font-weight-bold">    
                                </select>    
                        </div>   <!-- End col-8 (row 1) -->  
                    </div>     <!-- End 1st nested row-->    

                    <!-- 
                    #################################
                    Date picker (Custom "weeks" option)
                    #################################
                    -->
                    <?php
                    global $custom_payperiod;
                    $isHiddenClass = $custom_payperiod ? 'd-none':'';
                    echo "
                    <div class = 'row {$isHiddenClass}'>"; 
                    ?>
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="custom-weeks">
                                Pay period starting (Sunday):
                                </label>
                            </p>

                        </div> <!-- End col-4 (row 1) -->  
                        <div class = "col-8">
                            <input type ='date' id='custom-weeks' name = 'custom-weeks'>
                        </div>  
                    
                    
                    
                    </div>
                    <!-- 
                    #################################
                    Weeks to Display (2nd nested row)
                    #################################
                    -->
                    <div class = "row"> <!-- Begin 2nd nested row-->
                          <!-- FIELD LABEL  --> 
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="name">
                                Child / Worker:
                                </label>
                            </p>                            
                        </div> <!-- End col-4 (row 1) -->  

                        <!-- NAME DROPDOWN LIST -->                         
                        <div class = "col-8">
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
                                    $connection = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
                                    if ($connection -> connect_errno):
                                        $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection -> connect_error .");</script>";
                                        echo "alert({$errStr});";
                                        exit();


                                    else:
                                        $getUsersQuery = "SELECT first_name FROM user WHERE role = 'child' ORDER BY first_name ASC";
                                        $userResults = $connection-> query($getUsersQuery);

                                        echo "<script>alert(Database connection successful.);</script>";

                                        while ($row = $userResults -> fetch_assoc()):
                                            global $name;
                                            $name = ($row[first_name]);
                                            if($name === $_REQUEST['name']):
                                              $selected = "selected";
                                            else: 
                                                $selected = "";
                                            endif;
                                            echo "<option value = {$name} {$selected}>
                                                    {$name}
                                                </option>
                                            ".PHP_EOL;
                                        endwhile;


                                    endif;

                                    $connection -> close();

                                ?>
                                </select>
                            </p>
                        </div>   <!-- End col-8 (row 1) -->  
                    </div>     <!-- End 2nd nested row-->    
                    </div> <!-- End .col-10 -->

                    <!-- RIGHT MAIN column (spacer)  -->
                    <div class="col-1"></div>  
                </div> <!-- End .row -->
                    
                <!-- 
                    ##########################
                    SUBMIT BUTTON
                    ##########################
                    -->                        
                    <div class = "row pb-4 ">   
                        <div class = "col-3 col-md-4"></div>
                        <div class = "col-3 col-md-2">
                            <input type = "submit" class = "btn btn-block btn-success"  id = "chore-assign-btn" name = "submit" value = "Search">
                        </div>
                        <div class = "col-3 col-md-2">
                            <a class = "btn btn-block btn-info"  id = "chore-assign-link" name = "chore-assign-link" 
                                <?php
                                echo "href='assign-jobs.php?name={$name}'";
                                 ?>
                            >Assign a Chore</a>
                        </div>
                        <div class = "col-3 col-md-4"></div>                        
                    </div>    </div> <!-- End .row -->        
                </div> <!-- End .row-->               
            </div> <!-- End .row -->
        </form>                 
    
        
            <!-- 'Display Assignments -->    
        <div class="row font-weight-bold border-top border-bottom ml-5 mr-5">
            <div class="col-1"></div> <!-- Left spacer-->    
                        
            <!-- 'Results: Table Headers' -->                         
            <div class="col-2 text-left">
                <div>Job</div></div>

            <div class="col-1 text-left">
                <div>Category</div></div>

            <div class="col-1 text-left">
               <div>Value</div></div>

            <div class="col-1 text-left">
                <div>Due By</div></div>

            <div class="col-1 text-left">
                <div>Day</div></div>            

            <div class="col-1 text-left">
                <div>Deadline</div></div>            
            

            <div class="col-1 text-left">
                Weekly?</div>            

            <div class="col-1 text-left">
                <div>Completed?</div></div>            

            <div class="col-2 text-center">Action</div>          

        </div> <!-- End .row -->

        
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
                
                if($DebugMode){
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