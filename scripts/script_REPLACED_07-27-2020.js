'use strict';

$(document).ready(function() {
    console.log("In script.js function now.");
    let jobs_compl = 5;
    const total_jobs = 6;

    // Add event listeners to various icons
    // let checkmarks = document.getElementsByClassName('material-icons');
    let checkmarks = document.getElementsByClassName('check-icon');
    
    for(let el of checkmarks) {
        el.addEventListener('click', mark_complete);
        console.log("In for-of checkmarks loop ");
    }

    function mark_complete(e) {
        console.log("in mark-complete function");
        let checkmark = e.target;
        checkmark.classList.toggle('text-success');
        // Still need to add functionality to update database
    }



        // Toggles bonus trophy image between locked/unlocked
        let trophy = $(#trophy-img);
        trophy.addEventListener('click', toggleTrophy);
   
        function toggleTrophy(a,b){
        var element = document.getElementById("trophy-img");
        if (element.classList) { 
            element.classList.toggle("bonus");
        } else {
            // For IE9
            var classes = element.className.split(" ");
            var i = classes.indexOf("bonus");
            
            if (i >= 0) 
                classes.splice(i, 1);
            else 
                classes.push("bonus");
                element.className = classes.join(" "); 
        } // end else for IE9

    } // end toggleTrophy() definition

});