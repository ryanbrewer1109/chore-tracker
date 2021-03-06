<!DOCTYPE html>
<!-- THIS CODE WAS LAST UPDATED ON 10-04-2020 -->
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- icons from material.io  -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
    <!-- links for CSS  -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <!-- links for jQuery library & local scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script> 
    <script src="scripts/polyfills.js"></script>
    <script src="scripts/script.js"></script>
<?php
    require_once 'include.php'; 
    
    if(empty($_SESSION["auth_user_id"])):
         header('Location: login.php'); // No active user. Re-direct to login page
    endif;  
?>
    <title>Chore Tracker</title>

    
        
</head>

    <body class="bg-secondary">       
        
        <form action="index.php" method="GET" name = "form_index" onsubmit="return get_date()">

            <main class="main">
                <?php   
                    $authenticated = $_SESSION["authenticated"] ?? FALSE;
                    $authUserId = $_SESSION["auth_user_id"] ?? "ERROR: User ID not found";
                    $authFirstName = $_SESSION["auth_first_name"] ?? "ERROR: First name not found";
                    $authLastName = $_SESSION["auth_last_name"] ?? "ERROR: No Last name not found";
                    $authRole = $_SESSION["auth_role"] ?? "ERROR: Role not found";
                    $default_Timezone = 'Greenwich';

                    $job_category = $_SESSION["job_category"] ?? "allowance";

                //    $authTimeZone = (isset($_SESSION["auth_timezone"]) && timezoneID($authTimeZone)) ? $_SESSION["auth_timezone"]: "$UTC_timezone"; // Sets user's timezone if selected AND valid; else, defaults to UTC
                    $authTimeZone = $default_Timezone;
                //    $localTime = $_SESSION["local_timestamp"] ?? "NONE FOUND";   // This approach attempts to use JS to retrieve and use local tim and stores in $_SESSION. Problem: This means the time retrieved from $_SESSION is not the actual current time
                    $DebugMode = false;
                    $daysLeft;
                    $chorelistExists;
                    // DEBUG

                    $_HOSTNAME = $_SESSION['db_host'];
                    $_USERNAME = $_SESSION['db_login'];
                    $_PASSWORD = $_SESSION['db_pw'];
                    $_DBNAME = $_SESSION['db_table'];
                    $payperiod_id = $_SESSION['current_payperiod_id'] ?? "CURRENT PAY PERIOD UNKNOWN";

                    $total_complete = 0;
                    $total_incomplete = 0;
                    $counter = 0;
                    $penalty_exists;
                    $earnings = 0;
                    $extraEarnings = 0;
                    $bonusMultiplier = 1;
                    $totalEarnings = $earnings + $extraEarnings;
                    $local_timestamp;
                    $display_users_arr = [];


                    if($DebugMode) {
                        global $local_timestamp;
                        echo "<script>alert('Welcome, {$authFirstName} {$authLastName}. Your user ID is # {$authUserId} and your role is: {$authRole}. The local time is: {$local_timestamp}.')</script>";
                    }

                    // Connect to SQL database
                    $connection = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
                     echo "<script>console.log('In PHP script.');</script>";
                    if ($connection -> connect_errno):
                        $errStr= "Failed to connect to MySQL. Reason: " .  $connection -> connect_error;
                        echo "alert({$errStr});";
                        exit();
                    else:
                        // If displaying this page after save button has just been pressed
                        $getUsersQuery = "SELECT first_name,user_id FROM user WHERE role = 'child' ORDER BY first_name ASC";
                        $result_children  = $connection-> query($getUsersQuery);

                        $index_counter = 0;
                        while ($row_child = $result_children -> fetch_assoc()):
                            $name = $row_child['first_name'];
                            $id = $row_child['user_id'];
                            $this_chkbx = "user_chkbx_".($index_counter+1);
                            global $display_users_arr;

                            // Update array with values posted to $_REQUEST, if available
                            $display_users_arr[$index_counter][0] = $name;
                            $display_users_arr[$index_counter][1] = $id;
                            
                            //  Logic here determines the verbiage used in SQL query to get users' chorelists
                            $display_users_arr[$index_counter][2] = 'on';
                            if(empty($_REQUEST[$this_chkbx])):
                                if(!empty($_REQUEST['submit-tasklist'])):
                                    $display_users_arr[$index_counter][2] = null;
                                endif;
                            endif;                          
                            $index_counter++;
                        endwhile;

                        // Get penalty chores for current payperiod
                        $sql_results_filter_by_role = ($authRole === 'admin') ?  "AND u.role = 'child' ": "''";
                        $query_penalties = "SELECT * 
                                            FROM 
                                                assignment AS a 
                                                INNER JOIN 
                                                    user as u 
                                                    ON a.assignee_id = u.user_id 
                                                INNER JOIN 
                                                    payperiod as p 
                                                    ON a.payperiod_id = p.payperiod_id 

                                            WHERE 
                                                a.payperiod_id = '{$payperiod_id}' 

                                            {$sql_results_filter_by_role} 

                                            AND
                                                a.job_category = 'penalty'"; 

                        $result_penalties = mysqli_query($connection,$query_penalties);

                        $penalties_numrows = $result_penalties->num_rows;

                        if ($penalties_numrows === 0):
                            global $penalty_exists;
                            $penalty_exists = false;
                            if($DebugMode):
                                echo "<p>No penalties were found for this payperiod.</p>";
                            endif;
                        else:
                            global $penalty_exists;
                            $penalty_exists = true;
                        endif;

                        if(isset($_REQUEST["submit-tasklist"] )):

                            // echo "<script>alert('Submit button was pressed. Attempting to update DB.');</script>";
                            // save submission data to local variables
                            $form_counter = get_formdata($connection, "form_counter") ?? 0;
                            // echo "<script>alert('Value of form_counter is: {$form_counter}');</script>";


                            // // Declare variables
                            $choreID = '';
                            $thisCheckbox = '';



                            // Update completion status in database for all submitted chores
                            for($i = 1; $i <= $form_counter; $i++):
                                // echo "<script>alert('In update for loop...');</script>";

                                $tempChoreName = "choreID_".$i;
                                $tempCheckbox = "checkbox_".$i;
                //                $tempCounter = "counter_".$i;

                                $thisChoreNum = $_REQUEST[$tempChoreName]; // passed from hidden input field
                                // echo "<script>alert('Value of {$tempChoreName} is {$thisChoreNum}');</script>";

                                $thisCheckbox =  $_REQUEST[$tempCheckbox] ?? 0; // If checkbox is unchecked, set value to 0 instead of null

                                // echo "<script>alert('Value of {$tempCheckbox} is {$thisCheckbox}');</script>";                   

                                $epoch_time = time(); 

                                // Update database
                                $updateQuery = 
                                    "UPDATE assignment SET isCompleted = {$thisCheckbox}, completion_date = {$epoch_time} WHERE assignment_id = {$thisChoreNum}";

                                $errMsg = "Update failed. Reason: ";
                                $connection->query($updateQuery) or exit($errMsg.$connection->error);
                            endfor;

                        endif;
                    endif;
                    $connection -> close();
                ?>    

                <!-- TOP SECTION: NAVIGATION -->

                <section class="nav-wrapper container">                

                <!-- Mobile: Top row icons and user name -->
                    <!-- <div class="vert-spacer" ></div> -->
                    <div class="row mobile-navbar-top bg-light py-2">

                        <!-- Hamburger menu columns for mobile -->
                        <div class="col-2 col-sm-2 text-right">
                            <!-- Hamburger menu for mobile -->
                            <i class="material-icons menu-icon">menu</i>
                        </div>

                        <!--  Determine/Display active user at top of screen -->
                        <div class="col-6 col-sm-6 text-center text-nowrap">

                            <?php
                                // Connect to SQL database
                                $connection2 = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);

                                if ($connection2 -> connect_errno):
                                    exit( "Failed to connect to MySQL. Reason: " .  $connection2 -> connect_error);
                                else:                            
                                    // May need to type-cast current_userID  to an int
                                    // Display active user's name (NOTE: last name may be null)
                                    $current_username = $_SESSION['auth_first_name'];
                                    if(!empty($_SESSION['auth_last_name'])):
                                        $current_username = $current_username." ".$_SESSION['auth_last_name'];  
                                    endif;

                                    echo $current_username;
                                $connection2 -> close();
                                endif;
                            ?>

                        </div>

                        <!--  Show applicable links for Login/Logout and Register on navbar -->
                        <div class="col-2 col-sm-2 text-right">
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



                        <!-- Gear icon for mobile -->

                        <div class='col-2 col-sm-2 text-left'>
                        <?php
                            if($authenticated):
                                echo "<a href='assign-jobs.php'>"
                                ."<i class='material-icons settings-icon'>settings</i>"
                                ."</a>";
                               else:
                                echo "<i class='material-icons settings-icon'>settings</i>";
                            endif;
                        ?>
                        </div>

                    </div>  <!-- End of nav.row -->

                    <!-- Main menu, three Job Categories -->
                    <div class="row  pb-3 ">


                        <!-- ALLOWANCE menu item -->
                        <?php
                            // Highlight 'Allowance' category if selected (this is the default category)
                            global $job_category;
                            $active_status_1 = ($job_category === 'allowance') ? 'active' : '';
                            echo "<div  id = 'allowance' class = 'col-4 col-sm4 text-center border-right mobile-navbar-job-type {$active_status_1}'>
                                <p class = 'menu-job-type inline-flex text-wrap'>Allowance</p>
                            </div>";
                        ?>

                        <!-- EXTRA JOBS menu item -->
                        <?php
                            // Highlight 'Extra' category if selected (this is the default category)
                            global $job_category; 
                            $active_status_2 = ($job_category === 'extra') ? 'active' : '';
                            echo "<div  id = 'extra' class = 'col-4 col-sm4 text-center border-right mobile-navbar-job-type {$active_status_2}'>
                                    <p class = 'menu-job-type inline-flex text-wrap'>Extra</p></div>";
                        ?>

                        <!-- PENALTY BOX menu item -->
                        <?php
                            // Highlight 'Allowance' category if selected (this is the default category)
                            global $job_category;
                            global $penalty_exists;
                            $active_status_3 = ($job_category === 'penalty') ? 'active' : '';
                            echo "<div id = 'penalty' class = 'col-4 col-sm4 text-center border-right mobile-navbar-job-type {$active_status_3}'>";
                        ?>
                        <p class = 'menu-job-type inline-flex text-wrap'>Penalty
                        <?php    
                            if($penalty_exists):
                                echo "<i class='material-icons text-danger'> error</i>";
                            endif;
                        ?>
                        </p>

                    </div> <!-- End of div.row -->
                    <?php    
                        echo "<input type='hidden' name = 'job_category' value='{$job_category}'>";
                    ?>       
                </section> <!-- End of section.nav-wrapper -->

                <!-- Admin option: Select accounts to display -->

    <?php
        if($authRole === 'admin'):
            echo " 
                <section class = 'container'>
                    <div class='row admin-show-accts bg-light pt-1 border-top border-bottom '>
                        <div class='col-12 col-sm-12'>                        
                            <span class='font-weight-bold'>Select chore accounts to display:</span>";
                            global $display_users_arr;
                            for($i = 0;  $i < count($display_users_arr); $i++):
                                $child_name = $display_users_arr[$i][0];
                                $child_id = $display_users_arr[$i][1];
                                // Hidden field to post user ID
                                $this_user = 'user_id_'.($i+1);
                                echo "<input type='hidden' name = '{$this_user}' value= {$child_id}>";

                                // Determine the the status of checkboxes indicating which children's chores to display
                                $this_chkbx = 'user_chkbx_'.($i+1);
                                echo "<label class = 'px-3'>{$child_name}
                                <input type='checkbox' name = '{$this_chkbx}'}'
                                "; 
                                if($display_users_arr[$i][2]==='on'): // Per earlier logic to construct $display_users_arr, checkboxes default to checked state
                                    echo " checked></label>";
                                else:
                                    echo "></label>";
                                endif;
                            
                                

                            endfor;
            echo " 
                        </div>
                    </div>
                </section class = 'container'>
            ";
        endif;
    ?>


                <!-- CHORE LIST and EARNINGS CONTAINER-->
                <section  class='chore-list-earnings-container container bg-light'>

                    <!-- MIDDLE SECTION: CHORE LIST -->
                    <section class='chore-list-wrapper'>
                        <?php
                        require_once 'payperiod.php';

                            // Connect to SQL database
                            $connection3 = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
                        if ($connection3 -> connect_errno):
                              $errStr= "Failed to connect to MySQL. Reason: " .  $connection3 -> connect_error;
                              echo "alert({$errStr});";
                              exit();
                        else:
                            // Only display chores in the active job category (Allowance, Extra, or Penalty)
                            $sql_results_filter_by_job_category = "a.job_category = '" . $job_category . "' ";

                        // If admin logged in, display results for all children; if child logged in, only display that child's results
//                            $sql_results_filter_by_role = ($authRole === 'admin') ?  "u.role = 'child' ": "u.user_id = '{$authUserId}' ";
                        $sql_results_filter_by_id;
                        $sql_user_ids = "";
                        $first_iteration = true;
                        $arr_length = count($display_users_arr);
                        for($i = 0; $i < $arr_length; $i++):
                            echo "<script>console.log('The array display_users_arr includes ".count($display_users_arr)." children.')</script>";
                            echo "<script>console.log('The checkbox status for ".$display_users_arr[$i][0]." is: ".$display_users_arr[$i][2]."')</script>";
    
                            if($display_users_arr[$i][2] === 'on'):
                                if($first_iteration):
                                    $sql_user_ids .=$display_users_arr[$i][1];
                                    $first_iteration = false;
                             
                                else:
                                    $sql_user_ids .= ", ".$display_users_arr[$i][1];
                                endif;
                            endif;    
                        endfor;
                        

                        // Determine which users to include in results
                        if($authRole === 'child'):
                            $sql_results_filter_by_id = "AND u.user_id = '{$authUserId}' ";
                        else:
                            if($sql_user_ids !== ''):
                                $sql_results_filter_by_id = "AND user_id IN ({$sql_user_ids})";
                            endif;
                        endif;



                            // Fetch chorelist for above current pay period
                            $query_getchorelist = "SELECT 
                                                    first_name,
                                                    last_name, 
                                                    day_of_week,
                                                    time_due,
                                                    start_duedate,
                                                    job_name, 
                                                    job_description,
                                                    completion_date,
                                                    assignment_id,
                                                    isCompleted,
                                                    job_pay 

                                                FROM 
                                                    assignment AS a 
                                                    INNER JOIN 
                                                        user as u 
                                                        ON a.assignee_id = u.user_id 
                                                    INNER JOIN 
                                                        job AS j 
                                                        ON a.job_id = j.job_id 
                                                    INNER JOIN 
                                                        payperiod as p 
                                                        ON a.payperiod_id = p.payperiod_id 

                                                WHERE
                                                
                                                    a.payperiod_id = '{$payperiod_id}' 

                                                AND
                                                    {$sql_results_filter_by_job_category} 

                                                {$sql_results_filter_by_id} 
                                                ORDER BY 
                                                    a.assignee_id, 
                                                    a.start_duedate DESC";


                            if($DebugMode):
                                echo "<p>Retrieving chores for current user for current pay period...</p>"
                                    ."<p>{$query_getchorelist}</p>";
                            endif;

                            $result_assignment = mysqli_query($connection3,$query_getchorelist) or die ("Error: ".mysqli_error($connection3));
            //                    var_dump($query_getchorelist);

                            $chores_numrows = $result_assignment->num_rows;
                            if ($chores_numrows === 0):
                                global $chorelistExists;
                                $chorelistExists = false;
                                if($DebugMode):
                                    echo "<p class = 'text-center'>The date and time is currently {$local_timestamp_formatted}.<br> NO CHORELIST WAS FOUND FOR CURRENT PAY PERIOD  BEGINNING {$sqlStartDate} & ENDING {$sqlEndDate}.</p>";
                                    echo "<p>No chores have been assigned yet for this payperiod (based on the following query: <br>{$query_getchorelist}</p>";
                                endif;
                            else:
                                global $chorelistExists;
                                $chorelistExists = true;
                                if($DebugMode): 
                                    echo "<br><br>SUCCESS! FETCHING CHORES...";
                                    echo "<p>The following query returned some results:<br>{$query_getchorelist}</p>";
                                endif;

                                $arr_index_num_chores = 0; // used to advance index of $arr_num_chores when finished 
                                $arr_num_chores = []; // stores number of chores for each child
                                $prev_name = ""; // Helps determine whether to display child's name before a chore (prevents redundancies)

                                while ($row = $result_assignment -> fetch_array(MYSQLI_ASSOC)):
                                // while ($row = $result_assignment -> fetch_assoc()): // alternative way of executing above command
                                    $counter++;
                                    $this_first_name = $row['first_name'];
                                    $this_last_name = $row['last_name'];
                                    $this_child = $this_first_name." ".$this_last_name;
                                    $thisChoreName = "choreID_".$counter;
                                    $thisChoreValue = $row['assignment_id'];
                                    $job_description = $row["job_description"]  ;
                                    $job_name = $row["job_name"];
                                    $label_name = "label_".$counter;
                                    $checkmark_name =  "checkmark_".$counter;
                                    $checkbox_name = "checkbox_".$counter;
                                    $isCompleted = $row["isCompleted"];
                                    $completion_date = new DateTime("now", new DateTimeZone($authTimeZone));


                                    $completionClass = "";
                                    $job_pay = $row["job_pay"];
                                    $job_pay_dollars = "$".$job_pay;

                                    $due_day = $row["day_of_week"];
                                    $duetime_str = $row["time_due"]; // Format in DB is text: hh:mm:ssAM                            
                                    $due_timestamp = strtotime("{$due_day} {$duetime_str} this week"); // Finds the requested day of the week prior to the next Sunday (i.e. prior to "this [upcoming] week")

                                    $duedate_display = date("h:ia, l M. jS", $due_timestamp);

                                    // Following hidden input field captures 
                                    // assignment_id of this chore so it can be 
                                    // passed to form data array ($_GET or $_POST)

                                    echo "<input type = \"hidden\"  
                                            id = \"{$thisChoreName}\" 
                                            name = \"{$thisChoreName}\" 
                                            value = \"{$thisChoreValue}\">

                                          <!--  ==============================
                                                CHILD NAME BANNER (Admin view)
                                                ============================== -->
                                            ";                         

                                    if(($authRole == 'admin') AND ($prev_name !== $this_child OR $prev_name == '')):
                                        echo "
                                        <!-- Print each child's name on top row of their chore list  -->
                                        <div class='row'>
                                            <div class='col-12 bg-warning text-black py-1 mb-3 text-center font-weight-bold'>
                                                {$this_child}<br>
                                            </div>
                                        </div>";
                                        if($prev_name !== ''): // Increment array index each time a new name displays (i.e., when >1 child)
                                            global $arr_index_num_chores;
                                            $arr_index_num_chores++;

                                        endif;
                                    endif;

                                    $prev_name = $this_child;

                                    echo "
                                          <!--  ==========
                                                INFO ICON
                                               =========== -->           
                                    <div class=\"row chore\">
                                        <div class=\"col-2 col-sm-2 text-right\">
                                            <i 
                                                class=\"material-icons
                                                py-3 text-info info-icon\">info</i>
                                        </div>


                                          <!--  ======================
                                                TASK CONTAINER (BUBBLE)
                                               ======================= -->
                                        <!-- This column space reserved for task bubble -->
                                        <div class=\"col-7 col-sm-7\">
                                            <div class=\"row task-bubble\">
                                                <div class=\"col-11 col-sm-11\">

                                            <!--    ====================
                                                    DUE DATE & CHORE NAME
                                                    ==================== -->
                                                    <!-- DUE DATE -->
                                                       <p class=\"text-primary due-date\">
                                                        Due by {$duedate_display}</p>


                                                    <!-- JOB PAY -->
                                                    <p class=\"text-truncate text-left\">
                                                        {$job_name}</p>

                                            <!--    =============
                                                    CHORE DETAILS
                                                    ============= -->
                                                    <p class=
                                                            \"text-wrap 
                                                            py-1 details
                                                            text-primary
                                                            d-none
                                                            \">
                                                        {$job_description}</p>

                                                </div> <!-- End job name/details text -->    
                                            <!--    ===============================
                                                    CHECKBOX LABEL & INITIAL VALUES
                                                    =============================== -->
                                                <div class=\"col-1 col-sm-1 text-right 
                                                        py-2\">

                                    "; // End of Double quote section                   


                                    echo "<label id=\"{$label_name}\">";
                                        // Declare some local variables
                                        $value = 0;
                                        $checkmarkClass = '';
                                        $checkboxStatus = '';

                                        // Show correct CSS, value, and progress
                                        // if chore has been completed
                                        if($isCompleted):
                                            global $earnings;
                                            $value = $job_pay; 
                                            $checkmarkClass = "text-success";
                                            $checkboxStatus = "checked";
                                            // increases green checkmarks
                                            // on progress meter
                                            $total_complete += 1; 
                                            $completionClass = "text-success";
                                            $earnings += $value;

                                        // Show correct CSS, value, and progress
                                        // if chore has NOT been completed
                                        else:                                       $value= 0;
                                            $checkmarkClass = "text-muted";
                                            $checkboxStatus = " ";
                                            $total_incomplete += 1; 
                                            //increase gray checkmarks
                                            // on progress meter;
                                            $completionClass = "d-none";

                                        endif;

                                    echo "

                                            <!--    ===============================
                                                    CHECKBOX
                                                    =============================== -->                                        
                                                        <input type = \"checkbox\"  
                                                            class = \"d-none\"  
                                                            id = \"{$checkbox_name}\"  
                                                            name = \"{$checkbox_name}\"   
                                                            value = \"{$value}\" 
                                                            {$checkboxStatus}>






                                            <!--    ===============================
                                                    CHECKMARK ICON
                                                    =============================== -->
                                                        <i id = \"{$checkmark_name}\"  
                                                            class = \"material-icons align-middle {$checkmarkClass} \">        
                                                            check_circle </i>                                                

                                                    </label>


                                                </div> <!-- End .col-3 .col-sm-3 .text-right .py-2 -->
                                            </div> <!-- End .row .task-bubble -->
                                        </div> <!-- End task bubble contents (.col-8 .col-sm-8) -->
                                        <!--    ===============================
                                                        COMMENT ICON 
                                                =============================== -->            
                                                <!-- Empty Comment icon -->
                                                <div class= \"col-2 col-sm-2\">
                                                    <i class=\"material-icons py-3 comment-icon text-secondary\">chat_bubble</i>
                                                </div> <!-- End empty comment icon --> 
                                            </div> <!--  End div.row (Task #1) -->

                                            <div class=\"row\"> <!--  Next row: Completion time-stamp -->
                                                 <!--  Left spacer -->
                                                <div class=\"col-2 col-sm-2\"></div>

                                         <!--    ===============================
                                                        COMPLETION DATE
                                                =============================== -->            
                                                <!--  Completion Date -->
                                                <div class=\"col-3 col-sm-3 task-bubble-footer text-nowrap pl-3\">

                                                    <p class=\"time-stamp text-left  {$completionClass}\">
                                                    Completed ";
                                                if($completion_date != null ):
                                                    $completion_date_formatted = date_format($completion_date, 'm-d-y @ h:i A'); // Formats date in user's timezone
                                                    echo "{$completion_date_formatted}";
                                                endif;
                                                echo "</p>
                                                </div>
                                                <div class=\"col-3 col-sm-3\"> </div>
                                                <!--  column spacer  -->

                                                <!--  Job Value/Pay -->
                                                <div class=\"col-2 col-sm-2 task-bubble-footer text-nowrap pl-3\">
                                                 <p class = \"text-success job-pay\">Value: $ {$job_pay}</p>
                                                </div>

                                            </div> <!--  End div.row  -->";
                                endwhile;

                                // Include $counter in $_POST data
                                echo "<input type = \"hidden\" id = \"form_counter\"  
                                      name = \"form_counter\" value = \"{$counter}\">";

                                //Free up the result memory and close the connection
            //                    $result_assignment-> mysqli_free_result();  // check variable name and syntax
                                $connection3 -> close();
                            endif; // (numrows !== 0)
                        endif; // (connection successful)

                        ?>
                    </section> <!--  End of middle section -->

                    <!-- =========================================== -->
                    <!-- BOTTOM SECTION: EARNINGS MODULE -->
                    <!-- =========================================== -->
                    <section class='earnings-section-wrapper text-center bg-light'>
                    <?php
                        echo "
                            <!-- =========================================== -->
                            <!-- Row for Days Left, Trophy image, and Earnings -->
                            <!-- =========================================== -->
                            <div class='row earnings-section bg-white'>
                                <!-- =========================================== -->
                                <!-- Days Left -->
                                <!-- =========================================== -->
                                <div class='col-4 col-sm-4'>
                                    <p class='text-nowrap days-left mb-1'>Days Left</p>
                                    <div id='countdown' class='px-3 py-3 my-1'>
                                        {$daysLeft}
                                    </div>                
                                </div>
                                <!-- =========================================== -->
                                <!-- Trophy img -->
                                <!-- =========================================== -->
                                <div id='trophy-img' class='col-4 col-sm-4 my-2
                                ";
                                    global $chorelistExists;
                                    if ($chorelistExists):
                                        // Adds either bonus or nobonus class
                                        if($total_incomplete == 0 && $total_complete > 0):
                                            echo "bonus'>";
                                        else:
                                            echo " nobonus'>";
                                        endif;
                                    else:
                                        echo "blank_trophy'>";
                                    endif;
                                echo " 

                                </div>  <!-- End of div.col-4. ol-sm-4  -->

                                <!-- =========================================== -->
                                <!-- EARNINGS -->
                                <!-- =========================================== -->
                                ";

                                global $counter;
                                global $earnings;
                                global $extraEarnings;
                                global $totalEarnings;
                                $displayed_multiplier = "";
                                if($counter > 0 AND $total_complete == $counter):
                                    $bonusMultiplier = 2; // Doubles allowance earnings
                                    // Activates each week if all allowance chores
                                    // are completed by their due dates
                                    $displayed_multiplier = " (2x)";
                                endif;
                                $totalEarnings = $earnings * $bonusMultiplier;

                                echo 
                                "<div class='col-4 col-sm-4'>
                                      <p class='earnings mb-1'>Earnings{$displayed_multiplier}</p>
                                    <div id='amount-due' class='px-3 py-3 my-1'>"."$".$totalEarnings."
                                        </div>
                                </div>  <!-- End of final column group (div.earnings)  -->

                                </div> <!-- End of div .row .earnings-section  -->

                                <!-- =========================================== -->
                                <!-- ROW: PROGRESS METER (Checkmarks under trophy image) -->
                                <!-- =========================================== -->
                                <div class= 'row bg-dark pt-1' >

                                    <div class='d-flex container-fluid 
                                                justify-content-center bg-dark'>";

                                    for ($i = 0; $i < $total_complete; $i++):
                                         echo "<i class=\"material-icons text-success\">check_circle</i>";
                                    endfor;

                                    for ($i = 0; $i < $total_incomplete; $i++):
                                        echo "<i class=\"material-icons text-muted\">check_circle</i>";
                                    endfor;

                                echo "
                                    </div>  <!-- End of div.d-flex  -->
                                </div>  <!-- End of .row .bg-dark  -->";
                    ?>
                    </section> <!-- End of Earnings Section Wrapper -->
                </section> <!-- End of container for CHORELIST and EARNINGS  -->            
                <!-- End of earnings section-wrapper -->
            
                <!-- Save & Update Button-->
                <section class ='container save-update-btn-container border-top'>
                    <div class = 'row bg-light'>
                        <div class = 'col-12'>
                            <p class = 'text-center bg-muted pt-1 pb-1'>
                                <button type = 'submit' name='submit-tasklist' value ='save'  class='btn btn-success text-nowrap update-btn'>
                                    Save & Update
                                 </button>
                            </p>
                        </div>
                    </div>
                    <!-- Hidden field: Timestamp data-->
                    <p class = 'd-none'>        
                        <label>Page Loaded Timestamp: <input 
                            id = 'localTimestamp'
                            type = 'text'
                            name = 'local_timestamp'
                            >
                        </label>        
                    </p>
                </section>
            </main> <!-- End of div.main -->

        </form>
    </body>
</html>    