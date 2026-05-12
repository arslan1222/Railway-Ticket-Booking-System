$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Form validation
    $('form').on('submit', function(e) {
        var isValid = true;
        $(this).find('input[required], select[required]').each(function() {
            if($(this).val() === '') {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if(!isValid) {
            e.preventDefault();
            alert('Please fill all required fields');
        }
    });
    
    // Date validation for travel date
    var today = new Date().toISOString().split('T')[0];
    $('input[type="date"]').attr('min', today);
});