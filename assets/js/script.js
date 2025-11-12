// Custom JavaScript for Pengaduan website

// Confirm delete action
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus ini?') {
    return confirm(message);
}

// Attach confirm delete to all delete buttons
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirmDelete()) {
                e.preventDefault();
            }
        });
    });
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    });
});

// Toggle comment form visibility
function toggleCommentForm(postId) {
    const form = document.getElementById('comment-form-' + postId);
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

// Offcanvas sidebar menggunakan Bootstrap JS, tidak perlu JavaScript tambahan
