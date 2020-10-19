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
    <?php
        require_once 'include.php'; 
        if(empty($_SESSION["auth_user_id"])):
             header('Location: login.php');        // No active user. Re-direct to login page
        endif;  
    ?>
	<title>Chore Tracker Login</title>
</head>
<body>
    
<?php
    require_once 'include.php';
    $_HOSTNAME = $_SESSION['db_host'];
    $_USERNAME = $_SESSION['db_login'];
    $_PASSWORD = $_SESSION['db_pw'];
    $_DBNAME = $_SESSION['db_table'];
    
    $DebugMode = false;

    $auth_role =$_SESSION["auth_role"] ?? '';

    $authenticated = FALSE;
    
    $job_name = $_REQUEST['job_name'] ?? '';
    $job_description = $_REQUEST['job_description'] ?? '';


    $connection = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
    if ($connection -> connect_errno):
        $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection -> connect_error .");</script>";
        echo "alert({$errStr});";
        exit();
    else: // If connection to DB is successful
        if(isset($_REQUEST['deleteJob'])):
//            $thisJobName = $_REQUEST['job_name'];
//            echo "<script>let confirmDelete = confirm('Delete chore \"{$job_name}\"?');</script>";
            
            $job_id = $_REQUEST['deleteJob'];

            $query_delete = "DELETE FROM job WHERE job_id = ".$job_id;
            $connection -> query($query_delete);       
        
        // Ensure user has submitted an email address and password
        elseif(empty($_REQUEST["job_name"]) OR empty($_REQUEST["job_description"]) OR empty($_REQUEST["submit"])):
            global $auth_role;
            if($auth_role !== 'admin'):
                echo " <div class=\"alert alert-warning\" role=\"alert\">
                NOTE: Only Admin accounts can add a new chore.</div>";
            else:
                echo " <div class=\"alert alert-success\" role=\"alert\">
                Use this screen to add a new chore.</div>";
            endif;

        else:
            if(isset($_REQUEST["submit"])):
                // see notes in include.php re: get_formdata function
                $job_name = get_formdata($connection, "job_name");
                $job_description = get_formdata($connection, "job_description");
                
                $sql = "INSERT INTO job (job_name, job_description) VALUES ('{$job_name}', '{$job_description}')";
                //DEBUG
                if($DebugMode){
                    echo "<script>alert('Submitting the following query: {$sql}');</script>";}
                $queryResult = $connection-> query($sql);

            endif;
        endif;
    endif;

    $connection -> close();

?>




    <div class="container text-center bg-light">
        <form action="create-chore.php" method="get" name = "form_create-chore">
            
            <!-- Uses bootstrap 12-column grid system  -->
            <div class="row">
                <!-- 'CHORE TRACKER' (SCREEN TITLE) -->    
                <div class="col-12 text-center">
                            <h1 class = "m-4">Create New Chore</h1>
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
                
                
                <div class="col-3 text-left"></div>
                
                <div class="col-6 text-center bg-light border bg-white pt-3 pb-1 mb-4">
                    <div class = "row"> <!-- Begin 1st nested row-->
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="job_name">
                                Chore Name:
                                </label>
                            </p>
                            
                        </div> <!-- End col-4 (row 1) -->  
                        <div class = "col-6">
                            <p class = "text-left">
                               <input  type='text' placeholder='Enter chore name' id='job_name' name = 'job_name'>
                            </p>
                        </div>   <!-- End col-8 (row 1) -->  
                    </div>     <!-- End 1st nested row-->    

                    <div class = "row"> <!-- Begin 2nd nested row-->        
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="job_description">
                                Chore Description:
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
                                 <input  type='text' placeholder='Enter chore description & requirements' id='job_description' name='job_description'>
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
                                <input type = "submit" class = "btn btn-block btn-success"  id = "save" name = "submit" value="Submit">
                            </p>
                        </div>
                        <div class = "col-3 col-md-4"></div>                        
                    </div>     <!-- End 3rd nested row-->



    
         <?php
            $connection4 = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
                if ($connection4 -> connect_errno):
                    $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection4 -> connect_error .");</script>";
                    echo "alert({$errStr});";
                    exit();

                else: // if connection is OK
                    global $payperiod_id;
                    $payperiod_id = $_SESSION['current_payperiod_id'];
                    $getJobQuery = 
                            "SELECT "
                                . "job_id, "
                                . "job_name, "
                                . "job_description "
                            . "FROM "
                                . "job";


                    $jobResults = $connection4->query($getJobQuery);
                    global $DebugMode;
                    if($DebugMode):
                        echo "<div class = bg-info>";
                        echo $getJobQuery;
                        echo "<br>";
                        print_r($jobResults);
                        echo "</div>";
                    endif;

                    echo "
                        <div class='row border-top border-bottom ml-5 mr-5 mb-3 font-weight-bold bg-warning'>
                            <!-- 'Results: Table Headers' -->                         
                            <div class= col-3 text-left'>Jobs</div>
                            <div class='col-5 text-left'>Requirements</div>
                            <div class='col-4 text-center'>Actions</div>

                        </div> <!-- End .row -->";

                        while($row_job = $jobResults->fetch_assoc()):
                            $job_id = ($row_job ['job_id']);
                            $job_name = ($row_job ['job_name']);
                            $job_description = ($row_job ['job_description']);

                            echo "
                                <div class='row border-top border-bottom ml-5 mr-5 py-2'>
                                    <!-- 'Results: Table Headers' -->                         
                                    <div class= col-3 text-left'>{$job_name}</div>

                                    <div class='col-5 text-left'>{$job_description}</div>


                                    <div class='col-2 text-left'>
                                        <!-- Delete chore  -->
                                        
                                        <a  class='btn btn-block btn-danger'  onClick='return confirm(\"Deleting chore. Are you sure?\")' href='create-chore.php?deleteJob=" .$job_id . "'>Delete</a>
                                        </div>

                                    <div class='col-2 text-left'>
                                        <!-- Edit  chore  -->
                                        <a  class='btn btn-block btn-warning' href='process-edits.php?source=create-chore.php&edit={$job_id}';>Edit</a></div>
                                </div> <!-- End .row -->    
                            ";
                        endwhile;           
                    endif;
                $connection4 -> close();
            ?>  
            </div> <!-- End of login box row -->

        </form>                    
    </div> <!-- End of container -->        
</body>
</html>