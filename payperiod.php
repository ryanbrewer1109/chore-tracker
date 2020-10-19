<?php
    // Include this file wherever you need to determine the current payperiod and 
    // either locate it in the DB or add it to the DB (if it does not yet exist)
    
    // Connect to SQL database
    $conn_payprd = new mysqli($_HOSTNAME, $_USERNAME, $_PASSWORD, $_DBNAME);

    $authTimeZone = (isset($_SESSION["auth_timezone"])) ? $_SESSION["auth_timezone"]: "America/Chicago";

    if ($conn_payprd -> connect_errno):
        echo "Failed to connect to MySQL: " .  $conn_payprd -> connect_error;
        exit();
        
    else:
        // Retrieve chores from current payperiod only.
        // If current pay period does not yet exist, create it.

        // See https://www.php.net/manual/en/datetime.settimezone.php
        global $DebugMode;
        global $authTimeZone;

        if($DebugMode){
            echo "<script>alert('User's selected timezone is: {$authTimeZone}');</script>";
        }

        global $local_timestamp;
        $local_timestamp = date_create(); // Instead of retrieving from $_SESSION['timestamp'] 

        date_timezone_set($local_timestamp, timezone_open($authTimeZone));
        $local_timestamp_epoch = date_timestamp_get($local_timestamp);
        $local_timestamp_formatted = date_format($local_timestamp, 'M. j, Y g:ia');
        $local_timestamp_day = date_format($local_timestamp, 'l');
        $local_timestamp_date = date_format($local_timestamp, 'y-m-d');
        $currentPayPrdTimestamp;

        // DEBUG
        if($DebugMode) {
            echo "<script>alert('Variable local_timestamp =  {$local_timestamp_epoch} and variable local_timestamp_formatted = {$local_timestamp_formatted}.);</script>";
        }

        // Format current date for sql database                     
//        $sqlNow = formatDateTimeForSQL($local_timestamp_epoch); Not used since DB auto-creates and updates createdAt and updatedAt TIMESTAMP values
        $sqlToday = formatDateForSQL(strtotime($local_timestamp_date));

        // DEBUG
        if($DebugMode) {
            echo "<script>alert('sqlToday is: {$sqlToday}');</script>";
        }

        // Determine pay period start date
        $currentPayPrdStartDate;
        $currentPayPrdStartDateTime;

        if($local_timestamp_day == "Sunday"):
            $currentPayPrdStartDate = $local_timestamp_date;
        //  ALTERNATIVE CODE: $currentPayPrdStartDate = date('Y-m-d', (strtotime($local_timestamp_date)));
            $currentPayPrdStartDateTime = $local_timestamp_date.' 12:00:00 AM';

        else:
            $currentPayPrdTimestamp = (strtotime('last Sunday '.$local_timestamp_date));
            $currentPayPrdStartDate = date('Y-m-d', $currentPayPrdTimestamp);
        endif;
        global $currentPayPrdTimestamp;
        $payPrdFourwksAgoTimestamp = strtotime('three weeks ago '.$currentPayPrdTimestamp);
        $payPrdFourwksAgoStartDate = date('Y-m-d', $payPrdFourwksAgoTimestamp);
        
        // DEBUG        
        if($DebugMode) {
            echo "<script>alert('currentPayPrdStart:  {$currentPayPrdStartDate}');</script>";    
        }

        // Format pay period start date for database
        $sqlStartDate = formatDateForSQL(strtotime($currentPayPrdStartDate));

        // End of pay period                    
        $currentPayPrdEnd;

        if($local_timestamp_day == "Saturday"):
            $currentPayPrdEnd = date($local_timestamp_date.' 11:59:59 PM');
        else:
            $currentPayPrdEnd = date('Y-m-d', (strtotime('next Saturday '.$local_timestamp_date)));
        endif;

        // DEBUG        
        if($DebugMode) {
            echo "<script>alert('currentPayPrdEnd:  {$currentPayPrdEnd}');</script>";    
        }

        // Format pay period end date for database
        $seconds_per_day = 60 * 60 * 24;
        $sqlEndDate = formatDateForSQL(strtotime($currentPayPrdEnd) + ($seconds_per_day - 1));

        // Determine # of days left in current pay period                     
        global $daysLeft;
        $daysLeftArr = ['Saturday','Friday','Thursday', 'Wednesday', 'Tuesday', 'Monday', 'Sunday'];
        $daysLeft = array_search($local_timestamp_day, $daysLeftArr); // Index corresponds to # days left in weekly pay period (e.g., on Sunday, days left = 6)

        // See if current pay period exists in DB
        $query_payprd = "SELECT payperiod_id from payperiod WHERE start_date = '{$sqlStartDate}'";
        $payprd_result = $conn_payprd-> query($query_payprd);                   
        if(!$payprd_result):
            echo "<p>Database access failed. Reload this page to try again.</p>";
            
        else:
            $payprd_numrows = $payprd_result->num_rows;
            $payprd_row = $payprd_result->fetch_array(MYSQLI_ASSOC);
            
            if($payprd_numrows === 0):
            // Create a new pay period if none exists
            // 9-13-2020 UPDATE: Removed insertion of 'days left' and 'today' into DB
            // These values should be determined at time of session rather than by extracting old data from DB
            $query_addPayPrd = "INSERT INTO payperiod 
                               (start_date,
                               end_date) 
                               VALUES (
                               '{$sqlStartDate}',
                               '{$sqlEndDate}'
                               )";
             $last_pp_id = mysqli_insert_id($connection);
                // DEBUG
                if($DebugMode) {
                    echo "<div class=\"alert alert-success\" role=\"alert\">
                    Creating a new pay period with the following command:\n' 
                    {$query_addPayPrd}</div>";
                 }
             
            $conn_payprd->query($query_addPayPrd);
            
            else: // if ($payprd_numrows > 0)
                // DEBUG
                if($DebugMode){
                    echo "<div class=\"alert alert-success\" role=\"alert\">
                    The requested payperiod has the following payperiod_id number: 
                    {$payprd_row['payperiod_id']}</div>";
                }
                
                $_SESSION['current_payperiod_id'] = $payprd_row['payperiod_id'];
                $_SESSION['$payPrd_4wksAgo'] = $payPrdFourwksAgoStartDate;
            endif;
        endif;
    endif;
    $conn_payprd->close();
?>

    
                