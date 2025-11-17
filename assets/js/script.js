// Custom JavaScript for Pengaduan website

// Custom Confirmation Modal Function
function customConfirm(message, title = 'Konfirmasi', onConfirm = null, onCancel = null) {
    return new Promise((resolve) => {
        // Create modal HTML
        const modalHTML = `
            <div class="modal fade custom-confirm-modal" id="customConfirmModal" tabindex="-1" aria-labelledby="customConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="customConfirmModalLabel">${title}</h5>
                        </div>
                        <div class="modal-body">
                            ${message}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-confirm" id="confirmBtn">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if present
        const existingModal = document.getElementById('customConfirmModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Get modal elements
        const modal = new bootstrap.Modal(document.getElementById('customConfirmModal'));
        const confirmBtn = document.getElementById('confirmBtn');

        // Handle confirm button click
        confirmBtn.addEventListener('click', function() {
            modal.hide();
            if (onConfirm && typeof onConfirm === 'function') {
                onConfirm();
            }
            resolve(true);
        });

        // Handle modal hide (cancel)
        document.getElementById('customConfirmModal').addEventListener('hidden.bs.modal', function() {
            if (onCancel && typeof onCancel === 'function') {
                onCancel();
            }
            resolve(false);
            // Remove modal from DOM
            this.remove();
        });

        // Show modal
        modal.show();
    });
}

// Legacy confirm function for backward compatibility
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus ini?') {
    return customConfirm(message, 'Konfirmasi Hapus');
}

// Attach custom confirm to all delete buttons
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const confirmed = await confirmDelete();
            if (confirmed) {
                // If confirmed, proceed with the action
                if (button.tagName === 'A' && button.href) {
                    window.location.href = button.href;
                } else if (button.type === 'submit') {
                    button.closest('form').submit();
                }
            }
        });
    });

    // Attach custom confirm to bulk action buttons
    const bulkButtons = document.querySelectorAll('button[name="bulk_restore"], button[name="bulk_permanent_delete"]');
    bulkButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();

            // Check if any checkbox is selected
            const form = button.closest('form');
            const checkboxes = form.querySelectorAll('input[name="post_ids[]"]:checked');
            if (checkboxes.length === 0) {
                alert('Pilih setidaknya satu postingan untuk melakukan bulk action.');
                return;
            }

            const isRestore = button.name === 'bulk_restore';
            const message = isRestore
                ? 'Yakin ingin mengembalikan postingan yang dipilih?'
                : 'Yakin ingin menghapus permanen postingan yang dipilih? Tindakan ini tidak bisa dibatalkan!';

            const confirmed = await customConfirm(message, 'Konfirmasi Bulk Action');
            if (confirmed) {
                form.submit();
            }
        });
    });

    // Attach custom confirm to individual action buttons
    const actionButtons = document.querySelectorAll('a[href*="restore_post"], a[href*="permanent_delete"]');
    actionButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const isRestore = button.href.includes('restore_post');
            const message = isRestore
                ? 'Yakin ingin mengembalikan postingan ini?'
                : 'Yakin ingin menghapus permanen? Tindakan ini tidak bisa dibatalkan!';

            const confirmed = await customConfirm(message, 'Konfirmasi Action');
            if (confirmed) {
                window.location.href = button.href;
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
