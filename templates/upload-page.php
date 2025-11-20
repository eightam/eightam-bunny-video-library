<?php
if (!defined('ABSPATH')) {
    exit;
}

$bunny_api = new Eightam_Bunny_Video_Library_API();
?>

<div class="wrap">
    <h1>Upload to Bunny.net</h1>
    
    <div class="bunny-upload-container">
        <div class="bunny-upload-section">
            <div class="bunny-drop-zone" id="bunny-drop-zone">
                <div class="bunny-drop-icon">📁</div>
                <div class="bunny-drop-text">Drag & Drop Videos Here</div>
                <div class="bunny-drop-subtext">or click to select files</div>
                <input type="file" id="bunny-file-input" class="bunny-file-input" multiple accept="video/*">
            </div>
            
            <!-- API credentials kept server-side for security -->
            
            <div class="bunny-selected-files" id="bunny-selected-files"></div>
            
            <button type="button" id="bunny-upload-btn" class="bunny-upload-btn" disabled>
                <span class="btn-text">Upload Videos</span>
                <span class="btn-progress" style="display: none;">Uploading...</span>
            </button>
            
            <div class="bunny-upload-results" id="bunny-upload-results"></div>
        </div>
    </div>
</div>

<style>
.bunny-upload-container {
    max-width: 800px;
    margin: 20px 0;
}

.bunny-upload-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.bunny-drop-zone {
    border: 3px dashed #ddd;
    border-radius: 10px;
    padding: 60px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafafa;
    margin: 20px;
}

.bunny-drop-zone:hover,
.bunny-drop-zone.dragover {
    border-color: #0073aa;
    background: #f0f8ff;
    transform: scale(1.02);
}

.bunny-drop-zone.dragover {
    border-color: #ff6b6b;
    background: #fff5f5;
}

.bunny-drop-icon {
    font-size: 4em;
    color: #ddd;
    margin-bottom: 20px;
}

.bunny-drop-zone:hover .bunny-drop-icon {
    color: #0073aa;
}

.bunny-drop-text {
    font-size: 1.2em;
    color: #666;
    margin-bottom: 10px;
}

.bunny-drop-subtext {
    color: #999;
    font-size: 0.9em;
}

.bunny-file-input {
    display: none;
}

.bunny-upload-btn {
    background: linear-gradient(45deg, #0073aa, #00a0d2);
    color: white;
    border: none;
    padding: 15px 30px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    margin: 20px;
    transition: all 0.3s ease;
    width: calc(100% - 40px);
}

.bunny-upload-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,115,170,0.3);
}

.bunny-upload-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.bunny-selected-files {
    margin: 20px;
}

.bunny-file-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    margin: 10px 0;
    background: #f9f9f9;
    border-radius: 5px;
    border-left: 4px solid #0073aa;
}

.bunny-file-info {
    flex: 1;
}

.bunny-file-name {
    font-weight: bold;
    color: #333;
}

.bunny-file-size {
    color: #666;
    font-size: 0.9em;
}

.bunny-file-remove {
    background: #dc3545;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
}

.bunny-file-remove:hover {
    background: #c82333;
}

.bunny-upload-results {
    margin: 20px;
}

.bunny-upload-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
}

.bunny-upload-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
}

.bunny-upload-progress {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
}

.bunny-upload-url {
    background: #e9ecef;
    padding: 10px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 0.9em;
    margin: 10px 0;
    word-break: break-all;
}

.bunny-copy-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
    margin-left: 10px;
}

.bunny-copy-btn:hover {
    background: #218838;
}

.bunny-copy-btn.copied {
    background: #ffc107;
    color: #212529;
}

.bunny-upload-progress-container {
    margin: 20px;
    padding: 20px;
    background: #f0f8ff;
    border: 1px solid #0073aa;
    border-radius: 5px;
}

.bunny-upload-progress-text {
    font-size: 14px;
    color: #333;
    margin-bottom: 10px;
    font-weight: 500;
}

.bunny-upload-progress-text .current {
    color: #0073aa;
    font-weight: bold;
}

.bunny-upload-current-file {
    font-size: 13px;
    color: #666;
    margin-bottom: 10px;
    padding: 8px 12px;
    background: white;
    border-radius: 4px;
    border-left: 3px solid #0073aa;
    font-family: monospace;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: none;
}

.bunny-upload-progress-bar {
    width: 100%;
    height: 30px;
    background: #e0e0e0;
    border-radius: 15px;
    overflow: hidden;
    position: relative;
}

.bunny-upload-progress-fill {
    height: 100%;
    background: linear-gradient(45deg, #0073aa, #00a0d2);
    transition: width 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 12px;
    border-radius: 15px;
}
</style>

<script>
jQuery(document).ready(function($) {
    const dropZone = document.getElementById('bunny-drop-zone');
    const fileInput = document.getElementById('bunny-file-input');
    const uploadBtn = document.getElementById('bunny-upload-btn');
    const selectedFiles = document.getElementById('bunny-selected-files');
    const uploadResults = document.getElementById('bunny-upload-results');
    
    let files = [];
    let uploadResultsData = [];
    
    // Event listeners
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', handleDragOver);
    dropZone.addEventListener('dragleave', handleDragLeave);
    dropZone.addEventListener('drop', handleDrop);
    fileInput.addEventListener('change', handleFileSelect);
    uploadBtn.addEventListener('click', startUpload);
    
    function handleDragOver(e) {
        e.preventDefault();
        dropZone.classList.add('dragover');
    }
    
    function handleDragLeave(e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
    }
    
    function handleDrop(e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        addFiles(Array.from(e.dataTransfer.files));
    }
    
    function handleFileSelect(e) {
        addFiles(Array.from(e.target.files));
    }
    
    function addFiles(newFiles) {
        newFiles.forEach(file => {
            if (file.type.startsWith('video/')) {
                files.push(file);
            }
        });
        updateSelectedFiles();
    }
    
    function removeFile(index) {
        files.splice(index, 1);
        updateSelectedFiles();
    }
    
    function updateSelectedFiles() {
        selectedFiles.innerHTML = '';
        
        files.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'bunny-file-item';
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'bunny-file-info';
            
            const fileName = document.createElement('div');
            fileName.className = 'bunny-file-name';
            fileName.textContent = file.name;
            
            const fileSize = document.createElement('div');
            fileSize.className = 'bunny-file-size';
            fileSize.textContent = formatFileSize(file.size);
            
            fileInfo.appendChild(fileName);
            fileInfo.appendChild(fileSize);
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'bunny-file-remove';
            removeBtn.textContent = 'Remove';
            removeBtn.addEventListener('click', () => removeFile(index));
            
            fileItem.appendChild(fileInfo);
            fileItem.appendChild(removeBtn);
            selectedFiles.appendChild(fileItem);
        });
        
        uploadBtn.disabled = files.length === 0;
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function startUpload() {
        if (files.length === 0) return;
        
        uploadBtn.disabled = true;
        uploadBtn.querySelector('.btn-text').style.display = 'none';
        uploadBtn.querySelector('.btn-progress').style.display = 'inline';
        uploadResults.innerHTML = '';
        uploadResultsData = [];
        
        // Create progress container
        const progressContainer = document.createElement('div');
        progressContainer.className = 'bunny-upload-progress-container';
        progressContainer.innerHTML = `
            <div class="bunny-upload-progress-text">Uploading <span class="current">0</span> of <span class="total">${files.length}</span> files...</div>
            <div class="bunny-upload-current-file"></div>
            <div class="bunny-upload-progress-bar">
                <div class="bunny-upload-progress-fill" style="width: 0%"></div>
            </div>
        `;
        uploadResults.appendChild(progressContainer);
        
        const currentFileDisplay = progressContainer.querySelector('.bunny-upload-current-file');
        
        let completedUploads = 0;
        
        // Upload files sequentially to avoid overwhelming the server
        uploadFilesSequentially(0);
        
        function uploadFilesSequentially(index) {
            if (index >= files.length) {
                // All uploads complete
                progressContainer.remove();
                displayResults(uploadResultsData);
                uploadBtn.disabled = false;
                uploadBtn.querySelector('.btn-text').style.display = 'inline';
                uploadBtn.querySelector('.btn-progress').style.display = 'none';
                files = [];
                updateSelectedFiles();
                return;
            }
            
            // Show current file being uploaded
            currentFileDisplay.textContent = '📤 ' + files[index].name;
            currentFileDisplay.style.display = 'block';
            
            uploadFile(files[index], index).then(result => {
                uploadResultsData.push(result);
                completedUploads++;
                
                // Update progress
                const percent = Math.round((completedUploads / files.length) * 100);
                progressContainer.querySelector('.current').textContent = completedUploads;
                progressContainer.querySelector('.bunny-upload-progress-fill').style.width = percent + '%';
                
                // Upload next file
                uploadFilesSequentially(index + 1);
            }).catch(error => {
                uploadResultsData.push({
                    file: files[index].name,
                    success: false,
                    message: 'Upload failed: ' + error.message
                });
                completedUploads++;
                
                // Update progress and continue
                const percent = Math.round((completedUploads / files.length) * 100);
                progressContainer.querySelector('.current').textContent = completedUploads;
                progressContainer.querySelector('.bunny-upload-progress-fill').style.width = percent + '%';
                
                // Upload next file
                uploadFilesSequentially(index + 1);
            });
        }
    }
    
    async function uploadFile(file, index) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'bunny_video_upload');
        formData.append('nonce', '<?php echo wp_create_nonce('bunny_video_upload'); ?>');
        
        try {
            const response = await fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            return {
                file: file.name,
                success: result.success,
                message: result.data?.message || result.message,
                url: result.data?.url || '',
                videoId: result.data?.videoId || ''
            };
        } catch (error) {
            return {
                file: file.name,
                success: false,
                message: error.message
            };
        }
    }
    
    function displayResults(results) {
        uploadResults.innerHTML = '';
        
        results.forEach(result => {
            const resultDiv = document.createElement('div');
            resultDiv.className = result.success ? 'bunny-upload-success' : 'bunny-upload-error';
            
            const message = document.createElement('div');
            const strong = document.createElement('strong');
            strong.textContent = result.file + ':';
            message.appendChild(strong);
            message.appendChild(document.createTextNode(' ' + result.message));
            resultDiv.appendChild(message);
            
            if (result.success && result.url) {
                const urlDiv = document.createElement('div');
                urlDiv.className = 'bunny-upload-url';
                urlDiv.textContent = 'Direct URL: ' + result.url + ' ';
                
                const copyBtn = document.createElement('button');
                copyBtn.type = 'button';
                copyBtn.className = 'bunny-copy-btn';
                copyBtn.textContent = 'Copy';
                copyBtn.addEventListener('click', (e) => copyToClipboard(e, result.url));
                
                urlDiv.appendChild(copyBtn);
                resultDiv.appendChild(urlDiv);
            }
            
            uploadResults.appendChild(resultDiv);
        });
    }
    
    function copyToClipboard(event, text) {
        const btn = event.target;
        const originalText = btn.textContent;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                btn.textContent = 'Copied!';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.classList.remove('copied');
                }, 2000);
            }).catch(() => {
                fallbackCopy(text, btn, originalText);
            });
        } else {
            fallbackCopy(text, btn, originalText);
        }
    }
    
    function fallbackCopy(text, btn, originalText) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            btn.textContent = 'Copied!';
            btn.classList.add('copied');
        } catch (err) {
            btn.textContent = 'Failed';
        }
        
        document.body.removeChild(textArea);
        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('copied');
        }, 2000);
    }
});
</script>
