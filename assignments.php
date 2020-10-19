<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <!-- links for CSS  -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="styles/styles.css">
        <!-- links for jQuery library & local scripts -->
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script> 
        <script src="scripts/polyfills.js"></script>
        <script src="scripts/script.js"></script>
    
	<title>Chore Assignment</title>
    </head>
    <body>
        <?php
        // put your code here
        ?>

    <div class="container text-center bg-light">
        <form action="chore-assignment.php" method="get" name = "form_assignment" onsubmit="return get_date()">
            <!-- Uses bootstrap 12-column grid system  -->
            <div class="row">            
                <div class="col-12 text-center">
                            <h1 class = "m-4">Assign a Chore</h1>
                </div>

            <!-- Left margin/ spacer -->                
                <div class="col-3 text-center"></div>
                
                <!-- Form Content -->
                <!-- 
                ##########################
                BEGINNING 1st NESTED ROW
                ##########################
                -->    
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
                                    name="user"
                                    required>
                                    <option 
                                        value="blank"
                                        <?php
                                            if(empty($_REQUEST)):
                                                echo " selected>";
                                            elseif($_REQUEST["user"] == "blank"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        >Select your email
                                    </option>
                                        
<?php
        $connection1 = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
        if ($connection1 -> connect_errno):
            $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection1 -> connect_error .");</script>";
            echo "alert({$errStr});";
            exit(); 

        else:
            $getUsersQuery = "SELECT email FROM user ORDER BY email ASC";
            $userResults = $connection1-> query($getUsersQuery);
            
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
        
        $connection1 -> close();

    ?>

                                </select>
                            </p>

                        </div>   <!-- End col-8 (row 1) -->  
                    </div>     <!-- End 1st nested row-->    

<!-- 
##########################
BEGINNING 2nd NESTED ROW
##########################
-->    
                <!--<div class="col-6 text-center bg-light border bg-white pt-3 pb-1 mb-4">-->     
                    <div class = "row"> <!-- Begin 2nd nested row-->
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="user">
                                Job:
                                </label>
                            </p>
                            
                        </div> <!-- End col-4 (row 1) -->  
                        <div class = "col-8">
                            <p  class = "text-left">
                                <select 
                                    class = "w-75" 
                                    id="job" 
                                    name="job" 
                                    required>
                                    <option 
                                        value="blank"
                                        <?php
                                            if(empty($_REQUEST)):
                                                echo " selected>";
                                            elseif($_REQUEST["job"] == "blank"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        >Select a job
                                    </option>
                                        
<?php
        $connection2 = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
        if ($connection2 -> connect_errno):
            $errStr= "<script>alert(Failed to connect to MySQL. Reason: " .  $connection2 -> connect_error .");</script>";
            echo "alert({$errStr});";
            exit();        

        else:
            $getJobsQuery = "SELECT job_name FROM job ORDER BY job_name ASC";
            $jobResults = $connection2-> query($getJobsQuery);
            $numResults = $jobResults->num_rows;
            if($numResults == 0):
                echo "No jobs found.";
            else:
                while ($row = $jobResults -> fetch_assoc()):
                    $job = ($row[email]);
                    if($job === $_REQUEST['job']):
                      $selected = "selected";
                    else: 
                        $selected = "";
                    endif;
                    echo "
                        <option value = {$job} {$selected}>
                            {$job}
                        </option>
                    ".PHP_EOL;
                endwhile;
            endif;
        endif;
        
        $connection2 -> close();

    ?>
                                </select>
                            </p>

                        </div>   <!-- End col-8 (row 1) -->  
                    </div>     <!-- End 2nd nested row-->    


                <!-- 
                ##########################
                BEGINNING 3rd NESTED ROW
                ##########################
                -->    

<!--                <div class="col-6 text-center bg-light border bg-white pt-3 pb-1 mb-4">     -->
                  
                    <div class = "row"> <!-- Begin 3rd nested row-->
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="user">
                                Job Category:
                                </label>
                            </p>
                            
                        </div> <!-- End col-4 (row 1) -->  
                        <div class = "col-8">
                            <p  class = "text-left">
                                <select 
                                    class = "w-75" 
                                    id="category" 
                                    name="category">
                                    <option 
                                        value="blank"
                                        <?php
                                            if(empty($_REQUEST)):
                                                echo " selected>";
                                            elseif($_REQUEST["job"] == "blank"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        >Select a job
                                    </option>

                                    <option 
                                        value="allowance"
                                        <?php
                                            if(empty($_REQUEST)):
                                                echo " selected>";
                                            elseif($_REQUEST["job"] == "blank"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        >Allowance (Weekly Chore)
                                    </option>
                                        
                                    <option 
                                        value="extra"
                                        <?php
                                            if(empty($_REQUEST)):
                                                echo " selected>";
                                            elseif($_REQUEST["job"] == "blank"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        >Extra Cash
                                    </option>

                                    <option 
                                        value="penalty"
                                        <?php
                                            if(empty($_REQUEST)):
                                                echo " selected>";
                                            elseif($_REQUEST["job"] == "blank"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        >Penalty Box
                                    </option>

                                </select>
                            </p>

                        </div>   <!-- End col-8 (row 1) -->  
                    </div>     <!-- End 3rd nested row-->    


                <!-- 
                ##########################
                BEGINNING 4th NESTED ROW
                ##########################
                -->    

                <div class="col-6 text-center bg-light border bg-white pt-3 pb-1 mb-4">     
                    <div class = "row"> <!-- Begin 4th nested row-->
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="user">
                                Repeat weekly?
                                </label>
                            </p>
                            
                        </div> <!-- End col-4 (row 1) -->  
                        <div class = "col-8">
                            <p  class = "text-left">
                                <select 
                                    class = "w-75" 
                                    id="recurring" 
                                    name="recurring"
                                    required >
                                    <option 
                                        value="blank"
                                        <?php
                                            if(empty($_REQUEST)):
                                                echo " selected>";
                                            elseif($_REQUEST["recurring"] == "blank"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        >Select an option
                                    </option>

                                    <option 
                                        value="yes"
                                        <?php
                                            if($_REQUEST["recurring"] == "yes"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        >Yes
                                    </option>
                                        
                                    <option 
                                        value="yes"
                                        <?php
                                            if($_REQUEST["recurring"] == "no"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        >No
                                    </option>
                                </select>
                            </p>
                        </div>   <!-- End col-8 (row 1) -->  
                    </div>     <!-- End 4th nested row-->    

                    
                <!-- 
                #########################################
                BEGINNING 5th NESTED ROW : Pay Period
                #########################################
                -->    
                <div class="col-6 text-center bg-light border bg-white pt-3 pb-1 mb-4">     
                    <div class = "row"> <!-- Begin 5th nested row-->
                        <div class = "col-4">
                            <p class ="text-right font-weight-bold">
                                <label  for="startdate">
                                Pay Period
                                </label>
                            </p>
                            
                        </div> <!-- End col-4 (row 1) -->  
                        <div class = "col-8">
                            <p  class = "text-left">
                                <input  type ='date' 
                                        required 
                                        id = 'payperiod'
                                        name = 'payperiod' 
                                        value = 
                                            <?php
                                            if(isset($_REQUEST['payperiod'])):
                                                echo "{$_REQUEST['payperiod']}";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>

                                        <?php
                                            if(empty($_REQUEST)):
                                                echo " selected>";
                                            elseif($_REQUEST["frequency"] == "blank"):
                                                echo " selected>";
                                            else:
                                                echo ">";
                                            endif;
                                        ?>
                                        >Week beginning Sunday...
                            </p>
                        </div>   <!-- End col-8 (row 1) -->  
                    </div>     <!-- End 5th nested row-->    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
            </div> <!-- .row -->                                
        </form>
    </div> <!-- End of container -->
    </body>
</html>
