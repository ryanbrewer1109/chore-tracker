'use strict';
var counter = 0;

// var choreCheckboxes = document.querySelectorAll('check-clickable');

// var info_icons = document.getElementsByClassName('info-icon');
// var comments = document.getElementsByClassName('comment-icon');

document.addEventListener('DOMContentLoaded', function () {
    console.log('loading document');
    var choreBoxLabels = document.querySelectorAll('div.row.chore label');
    var infoIcons = document.querySelectorAll('.info-icon');
    var jobCategoryAllowance = document.getElementById('allowance');
    var jobCategoryExtra = document.getElementById('extra');
    var jobCategoryPenalty = document.getElementById('penalty');
    var showDebugComments = 1;
    

    choreBoxLabels.forEach(function (bxLabel) {
        bxLabel.addEventListener("click", toggle_complete);
        console.dir('box label', bxLabel);
    });


    infoIcons.forEach(function (infoIcon) {
        infoIcon.addEventListener("click", show_details);
        console.dir('infoIcon object: ', infoIcon);
    });


    jobCategoryAllowance.addEventListener("click", function(){
        var jobType = "allowance";
        select_category(jobType);
    });
    
    jobCategoryExtra.addEventListener("click", function(){
        var jobType = "extra";
        select_category(jobType);
    });
    
    jobCategoryPenalty.addEventListener("click", function(){
        var jobType = "penalty";
        select_category(jobType);
    });
    


//    get_date(showDebugComments);   // This approach attempts to use JS to retrieve and use local tim and stores in $_SESSION. Problem: This means the time retrieved from $_SESSION is not the actual current time
    
    function toggle_complete(e) {
        e.preventDefault();
        e.stopPropagation();
        var label = e.currentTarget;
        var icon = label.querySelector('i');
        var check = label.querySelector('input');
        console.log("Here is the checkbox object:", check);
        // var check.setAttribute("value", "0");
        // var checkVal = check.value;
        
        var toggleValues = (icon.classList.contains('text-muted'))? 
            [true, 1, 'text-muted', 'text-success']: 
            [false, 0,'text-success', 'text-muted'];
        
        check.checked = toggleValues[0];
        check.value = toggleValues[1];
        icon.classList.remove(toggleValues[2]);
        icon.classList.add(toggleValues[3]);
        
    
    } // end function toggle_complete
    
    
    function show_details(e) {
        e.preventDefault();
        e.stopPropagation();
        var icon = e.currentTarget;
        var jobName = icon.parentNode.parentNode.querySelector('div.task-bubble p.due-date + p');
        var jobDescription = icon.parentNode.parentNode.querySelector('div.task-bubble p.due-date + p + p');
        console.log("jobName object:", jobName);
        console.log("jobDescription object:", jobDescription);

        var toggleDisplay = (jobDescription.classList.contains('d-none'))? 
            jobDescription.classList.remove('d-none'):
            jobDescription.classList.add('d-none');
    
        
    } // end function show_details
    
    
    
    function select_category(jobType) {
        var jobTypeInput = document.getElementById('job_category');
        jobTypeInput.setAttribute("value", jobType);
    }

    function get_date(showDebugComments) {
            const d = new Date();
            console.dir(d);
            let nowJS = d.getTime();
            let tzOffsetMinutes = d.getTimezoneOffset();
            if(showDebugComments) { alert('nowJS: ' + nowJS);}
            
            formattedTimestamp = date("M jS, Y",int(nowJS));
            if(showDebugComments) { alert('formattedTimestamp');}
            
        
            if(showDebugComments) { alert('Determining Timezone Offset...');}

            if(showDebugComments) { alert('Timezone offset: ' + tzOffsetMinutes + ' minutes');}
            
            let localTime = nowJS + (tzOffsetMinutes * 60 * 1000);
            if(showDebugComments) { alert('localTime before offset is ' +  nowJS + ', and after offset is ' + localTime);}

            let phpTimestamp = Math.floor(localTime / 1000); // Convert and round down JS epoch time (in milliseconds) to PHP epoch time (in seconds)
            if(showDebugComments) { alert('nowJS = ' + nowJS + ', and phpTimestamp = ' + phpTimestamp);}
            
            let timeInputObj = document.getElementById('localTimestamp');
            timeInputObj.setAttribute('value', phpTimestamp);
            
            
            
//        return true; // Activate this line of code if implemeting as onsubmit function for form button; form's ACTION only proceeds if this returns TRUE
    } // end function get_date
    

    
}); // end DOM content loaded