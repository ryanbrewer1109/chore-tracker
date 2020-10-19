<?php
    require_once 'include.php'; 
    $_HOSTNAME = $_SESSION['db_host'];
    $_USERNAME = $_SESSION['db_login'];
    $_PASSWORD = $_SESSION['db_pw'];                                  
    $_DBNAME = $_SESSION['db_table']; 

    $name = $_REQUEST['name'] ?? '';
    $email = $_REQUEST['email'] ?? '';

    // Connect to SQL database
    $connection = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);
    echo "<script>console.log('In PHP script.');</script>";
    if ($connection -> connect_errno):
        $errStr= "process-edits.php failed to connect to MySQL. Reason: " .  $connection -> connect_error;
        echo "alert({$errStr});";
        exit();
        
    else:
        if(isset($_REQUEST['delete'])):
            $record_id = get_formdata($connection, 'delete');
            $query_delete = "DELETE FROM assignment WHERE assignment_id = ".$record_id;
            $connection -> query($query_delete);
            
//        elseif(isset($_REQUEST['edit'])):
//              $form_values = $_REQUEST->getArrayCopy();
//            $thisURL = window.location.href;
//            $assignment_id = $_REQUEST['edit'];
//            $job_name = $_REQUEST['job_name']);
//            $job_category = $_REQUEST['job_category']);
//            $job_pay = $_REQUEST['job_pay']);
//            $due_day = $_REQUEST['day_of_week']);
//            $due_time = $_REQUEST['time_due']);
//            $duedate = $_REQUEST['start_duedate']);
//            $duedate =  $_REQUEST['start_duedate']);
//            $isCompleted = $_REQUEST['isCompleted'] != 0 ? "Yes":"No";
//            $completion_date = $_REQUEST['completion_date']);
//            $isRecurring = $_REQUEST['isRecurring'] ? "Yes":"No";
//            $duedate = $_REQUEST['start_duedate']);
//
//            $edit_query = "UPDATE assignment SET job"
//            $connection -> query($query_edit);
//            endif; // End ($_REQUEST['edit'])
         endif; // End (isset($_REQUEST['delete']))
    endif; // End (check connection)
    
    $destination_page = $_REQUEST['source'];
    header('Location: '.$destination_page.'?name='.$name);
        
?>