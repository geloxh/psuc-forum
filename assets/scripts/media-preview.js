function initMediaPreview() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    console.log('Found file inputs:', fileInputs.length);
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            console.log('File input changed, files:', e.target.files.length);
            const files = Array.from(e.target.files);
            const previewContainer = getOrCreatePreviewContainer(input);
            previewContainer.innerHTML = ''; // Clear previous previews
            
            files.forEach(file => {
                console.log('Processing file:', file.name, file.type);
                if (isValidMediaFile(file)) {
                    console.log('Creating preview for:', file.name);
                    createPreview(file, previewContainer);
                } else {
                    console.log('File type not supported:', file.type);
                }
            });
        });
    });
}

function getOrCreatePreviewContainer(input) {
    let container = input.parentNode.querySelector('.media-preview-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'media-preview-container';
        input.parentNode.appendChild(container);
        console.log('Created preview container');
    }
    console.log('Preview container:', container);
    return container;
}

function isValidMediaFile(file) {
    const validTypes = ['image/', 'video/', 'application/pdf'];
    return validTypes.some(type => file.type.startsWith(type));
}

function createPreview(file, container) {
    console.log('Creating preview item for:', file.name);
    const previewItem = document.createElement('div');
    previewItem.className = 'media-preview-item';
    
    const removeBtn = document.createElement('button');
    removeBtn.innerHTML = '×';
    removeBtn.className = 'preview-remove-btn';
    removeBtn.onclick = () => previewItem.remove();
    
    if (file.type.startsWith('image/')) {
        console.log('Creating image preview');
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.onload = () => {
            console.log('Image loaded');
            URL.revokeObjectURL(img.src);
        };
        previewItem.appendChild(img);
    } else if (file.type.startsWith('video/')) {
        console.log('Creating video preview');
        const video = document.createElement('video');
        video.src = URL.createObjectURL(file);
        video.controls = true;
        video.onloadeddata = () => URL.revokeObjectURL(video.src);
        previewItem.appendChild(video);
    } else if (file.type === 'application/pdf') {
        console.log('Creating PDF preview');
        const pdfPreview = document.createElement('div');
        pdfPreview.className = 'pdf-preview';
        pdfPreview.innerHTML = `<i class="fas fa-file-pdf"></i><span>${file.name}</span>`;
        previewItem.appendChild(pdfPreview);
    }
    
    previewItem.appendChild(removeBtn);
    container.appendChild(previewItem);
    console.log('Preview item added to container');
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMediaPreview);
} else {
    initMediaPreview();
}

// Also try to initialize after a short delay as fallback
setTimeout(initMediaPreview, 100);