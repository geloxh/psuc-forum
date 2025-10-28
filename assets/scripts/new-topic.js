document.addEventListener('DOMContentLoaded', function() {
    // File input enhancement
    const fileInput = document.getElementById('attachments');
    const fileInputWrapper = document.querySelector('.file-input-wrapper');
    const fileInputText = document.querySelector('.file-input-text span');
    
    if (fileInput && fileInputWrapper && fileInputText) {
        const originalText = fileInputText.textContent;
        
        // Handle file selection
        fileInput.addEventListener('change', function() {
            const files = this.files;
            if (files.length > 0) {
                const fileNames = Array.from(files).map(file => file.name);
                if (files.length === 1) {
                    fileInputText.textContent = fileNames[0];
                } else {
                    fileInputText.textContent = `${files.length} files selected`;
                }
                fileInputWrapper.style.borderColor = 'var(--success-color)';
                fileInputWrapper.style.background = 'rgba(16, 185, 129, 0.05)';
            } else {
                fileInputText.textContent = originalText;
                fileInputWrapper.style.borderColor = '';
                fileInputWrapper.style.background = '';
            }
        });
        
        // Drag and drop functionality
        fileInputWrapper.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--secondary-blue)';
            this.style.background = 'rgba(59, 130, 246, 0.05)';
        });
        
        fileInputWrapper.addEventListener('dragleave', function(e) {
            e.preventDefault();
            if (!fileInput.files.length) {
                this.style.borderColor = '';
                this.style.background = '';
            }
        });
        
        fileInputWrapper.addEventListener('drop', function(e) {
            e.preventDefault();
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        });
    }
    
    // Form validation enhancement
    const form = document.querySelector('.topic-form');
    const titleInput = document.getElementById('title');
    const contentTextarea = document.getElementById('content');
    
    if (form && titleInput && contentTextarea) {
        // Real-time character count for title
        const titleMaxLength = titleInput.getAttribute('maxlength');
        if (titleMaxLength) {
            const titleGroup = titleInput.closest('.form-group');
            const titleLabel = titleGroup.querySelector('label');
            const charCounter = document.createElement('span');
            charCounter.className = 'char-counter';
            charCounter.style.fontSize = '0.8rem';
            charCounter.style.color = 'var(--text-secondary)';
            titleLabel.appendChild(charCounter);
            
            function updateTitleCounter() {
                const remaining = titleMaxLength - titleInput.value.length;
                charCounter.textContent = `${remaining} characters remaining`;
                if (remaining < 20) {
                    charCounter.style.color = 'var(--danger-color)';
                } else {
                    charCounter.style.color = 'var(--text-secondary)';
                }
            }
            
            titleInput.addEventListener('input', updateTitleCounter);
            updateTitleCounter();
        }
        
        // Auto-resize textarea
        function autoResize() {
            this.style.height = 'auto';
            this.style.height = Math.max(200, this.scrollHeight) + 'px';
        }
        
        contentTextarea.addEventListener('input', autoResize);
        
        // Form submission enhancement
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('.btn-primary');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                submitBtn.disabled = true;
            }
        });
    }
    
    // Smooth scroll to error messages
    const errorAlert = document.querySelector('.alert-error');
    if (errorAlert) {
        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});