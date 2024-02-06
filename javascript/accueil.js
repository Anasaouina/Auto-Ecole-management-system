function navigateTo(page) {
    window.location.href = page;
}
window.onload = function() {
    toggleDateFields(); // Invoke toggleDateFields when the page loads
};

function toggleDateFields() {
    var formuleSelect = document.getElementById("formule_code");
    var dateFinGroup = document.getElementById("date_fin_group");
    
    if (formuleSelect.value === "Code") {
        dateFinGroup.style.display = "block";
        
        setMaxEndDate();
    } else {
        dateFinGroup.style.display = "none";   
    }
}
function setMaxEndDate() {
    var dateDebutInput = document.getElementById("date_debut");
    var dateFinInput = document.getElementById("date_fin");

    // Check if dateDebutInput value is not null or empty
    if (dateDebutInput.value) {
        // Get the selected start date
        var startDate = new Date(dateDebutInput.value);
        // Calculate and set the maximum end date (6 months from start date)
        var maxEndDate = new Date(startDate);
        maxEndDate.setMonth(maxEndDate.getMonth() + 6);
        // Convert the maximum end date to yyyy-mm-dd format
        var maxEndDateFormatted = maxEndDate.toISOString().split('T')[0];
        // Set the max attribute of the end date input
        dateFinInput.max = maxEndDateFormatted;
        dateFinInput.min = startDate.toISOString().split('T')[0];

    }
}



