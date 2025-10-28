document.addEventListener('DOMContentLoaded', function() {
    // Compose modal functionality
    const composeModal = document.getElementById('compose-modal');
    const composeOverlay = document.getElementById('compose-overlay');
    
    window.toggleCompose = function() {
        composeModal.classList.toggle('active');
        composeOverlay.classList.toggle('active');
        
        if (composeModal.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
            // Focus on the first input
            const firstInput = composeModal.querySelector('select, input');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        } else {
            document.body.style.overflow = '';
        }
    };
    
    // Close compose on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && composeModal.classList.contains('active')) {
            toggleCompose();
        }
    });
    
    // Auto-hide success toast
    const successToast = document.querySelector('.success-toast');
    if (successToast) {
        setTimeout(() => {
            successToast.style.opacity = '0';
            successToast.style.transform = 'translateY(-50%) translateX(20px)';
            setTimeout(() => successToast.remove(), 300);
        }, 3000);
    }
    
    // Message row interactions
    const messageRows = document.querySelectorAll('.message-row');
    messageRows.forEach(row => {
        // Prevent checkbox clicks from navigating
        const checkbox = row.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                
                // Toggle row selection
                row.classList.toggle('selected-for-action');
                updateBulkActions();
            });
        }
        
        // Add hover effects
        row.addEventListener('mouseenter', function() {
            if (!this.classList.contains('selected')) {
                this.style.backgroundColor = '#f5f5f5';
            }
        });
        
        row.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.backgroundColor = '';
            }
        });
    });
    
    // Bulk actions functionality
    function updateBulkActions() {
        const selectedRows = document.querySelectorAll('.message-row.selected-for-action');
        const bulkActions = document.querySelector('.bulk-actions');
        
        if (selectedRows.length > 0) {
            if (!bulkActions) {
                createBulkActionsBar();
            }
            document.querySelector('.bulk-count').textContent = selectedRows.length;
        } else if (bulkActions) {
            bulkActions.remove();
        }
    }
    
    function createBulkActionsBar() {
        const listHeader = document.querySelector('.list-header');
        const bulkActions = document.createElement('div');
        bulkActions.className = 'bulk-actions';
        bulkActions.innerHTML = `
            <div class="bulk-info">
                <span class="bulk-count">0</span> selected
            </div>
            <div class="bulk-buttons">
                <button class="bulk-btn" onclick="markAsRead()">
                    <i class="fas fa-envelope-open"></i>
                    Mark as read
                </button>
                <button class="bulk-btn" onclick="deleteSelected()">
                    <i class="fas fa-trash"></i>
                    Delete
                </button>
            </div>
        `;
        listHeader.appendChild(bulkActions);
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Only handle shortcuts when not in input fields
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
            return;
        }
        
        switch(e.key) {
            case 'c':
                if (!composeModal.classList.contains('active')) {
                    toggleCompose();
                }
                break;
            case 'r':
                // Reply to selected message
                const selectedMessage = document.querySelector('.message-row.selected');
                if (selectedMessage) {
                    // Implement reply functionality
                    console.log('Reply to message');
                }
                break;
            case 'Delete':
            case 'Backspace':
                // Delete selected message
                const selected = document.querySelector('.message-row.selected');
                if (selected && confirm('Delete this message?')) {
                    // Implement delete functionality
                    console.log('Delete message');
                }
                break;
        }
    });
    
    // Auto-resize textarea in compose
    const composeTextarea = document.querySelector('.compose-form textarea');
    if (composeTextarea) {
        composeTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(200, this.scrollHeight) + 'px';
        });
    }
    
    // Form submission enhancement
    const composeForm = document.querySelector('.compose-form');
    if (composeForm) {
        composeForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.send-btn');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                submitBtn.disabled = true;
            }
        });
    }
    
    // Mobile responsive adjustments
    function handleResize() {
        const isMobile = window.innerWidth <= 640;
        const messageList = document.querySelector('.message-list');
        const messageDetail = document.querySelector('.message-detail');
        
        if (isMobile && messageDetail && !messageDetail.classList.contains('no-selection')) {
            messageList.classList.add('hidden-mobile');
        } else {
            messageList.classList.remove('hidden-mobile');
        }
    }
    
    window.addEventListener('resize', handleResize);
    handleResize();
    
    // Smooth animations for message list
    const messageList = document.querySelector('.messages-list');
    if (messageList) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        messageRows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            row.style.transition = `all 0.3s ease ${index * 50}ms`;
            observer.observe(row);
        });
    }
});

// Global functions for bulk actions
window.markAsRead = function() {
    const selectedRows = document.querySelectorAll('.message-row.selected-for-action');
    selectedRows.forEach(row => {
        row.classList.remove('unread');
        const checkbox = row.querySelector('input[type=\"checkbox\"]');
        if (checkbox) checkbox.checked = false;
        row.classList.remove('selected-for-action');
    });
    
    const bulkActions = document.querySelector('.bulk-actions');
    if (bulkActions) bulkActions.remove();
    
    // Here you would typically make an AJAX call to update the server
    console.log('Marked messages as read');
};

window.deleteSelected = function() {
    const selectedRows = document.querySelectorAll('.message-row.selected-for-action');
    if (selectedRows.length > 0 && confirm(`Delete ${selectedRows.length} message(s)?`)) {
        selectedRows.forEach(row => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(-100%)';
            setTimeout(() => row.remove(), 300);
        });
        
        const bulkActions = document.querySelector('.bulk-actions');
        if (bulkActions) bulkActions.remove();
        
        // Here you would typically make an AJAX call to delete from server
        console.log('Deleted selected messages');
    }
};