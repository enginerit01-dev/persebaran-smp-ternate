// Map configuration and utilities
// Main functions are already embedded in dashboard.php and detail_sekolah.php

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function showToast(message, type = 'success') {
    var toast = $('<div class="toast-message">' + message + '</div>');
    toast.css({
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        background: type === 'success' ? '#1e5631' : '#dc2626',
        color: 'white',
        padding: '12px 20px',
        borderRadius: '10px',
        zIndex: 9999,
        boxShadow: '0 5px 15px rgba(0,0,0,0.2)'
    });
    $('body').append(toast);
    setTimeout(function() {
        toast.fadeOut(300, function() { $(this).remove(); });
    }, 3000);
}