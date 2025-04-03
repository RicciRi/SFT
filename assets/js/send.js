document.addEventListener('turbo:load', () => {
    if (document.body.dataset.page === 'app_send') {
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('file-input');
        const fileList = document.getElementById('file-list');
        const fileListContainer = document.getElementById('file-list-container');
        const fileCount = document.getElementById('file-count');
        const uploadProgress = document.getElementById('upload-progress');
        const progressBar = uploadProgress.querySelector('.progress-bar');
        const transferForm = document.getElementById('transfer-form');
        const submitBtn = document.getElementById('submit-btn');
        const api_reset_files = document.getElementById('api_reset_files').value;
        const api_upload_files = document.getElementById('api_upload_files').value;
        const api_file_transfer_create = document.getElementById('api_file_transfer_create').value;

        flatpickr("#expirationAt", {
            dateFormat: "Y-m-d",
            defaultDate: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000),
            minDate: "today",
            maxDate: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000),
        });


        let uploadedFiles = [];

        resetFilesSession();

        function resetFilesSession() {
            fetch(api_reset_files, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                }
            )
                .then(response => response.json())
                .catch(error => console.error('Error resetting files session:', error));
        }

        fileInput.addEventListener('change', function () {
            handleFiles(Array.from(this.files));
        });

        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('active');
        });

        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('active');
        });

        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('active');

            const items = e.dataTransfer.items;
            const files = e.dataTransfer.files;

            if (files.length > 0 && (!items[0].webkitGetAsEntry)) {
                handleFiles(Array.from(files));
                return;
            }

            if (items.length > 0 && items[0].webkitGetAsEntry) {
                const entries = [];
                for (let i = 0; i < items.length; i++) {
                    const entry = items[i].webkitGetAsEntry();
                    if (entry) {
                        entries.push(entry);
                    }
                }

                processEntries(entries);
            }
        });

        function processEntries(entries) {
            const allFiles = [];
            let pendingDirectories = entries.length;

            const allEntriesProcessed = () => {
                if (allFiles.length > 0) {
                    handleFiles(allFiles);
                }
            };

            const processEntry = (entry) => {
                if (entry.isFile) {
                    entry.file(file => {
                        allFiles.push(file);

                        if (--pendingDirectories === 0) {
                            allEntriesProcessed();
                        }
                    });
                } else if (entry.isDirectory) {
                    const reader = entry.createReader();

                    const readEntries = () => {
                        reader.readEntries(entries => {
                            if (entries.length) {
                                pendingDirectories += entries.length - 1;
                                entries.forEach(processEntry);
                                readEntries();
                            } else if (--pendingDirectories === 0) {
                                allEntriesProcessed();
                            }
                        });
                    };

                    readEntries();
                }
            };

            entries.forEach(processEntry);
        }

        function handleFiles(files) {
            if (files.length === 0) return;

            uploadFiles(files);
        }

        async function uploadFiles(files) {
            uploadProgress.style.display = 'block';

            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', api_upload_files
                )
                ;

                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBar.style.width = percentComplete + '%';
                    }
                });

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);

                        if (response.success) {
                            uploadedFiles = [...uploadedFiles, ...response.files];
                            updateFileList();
                            submitBtn.disabled = uploadedFiles.length === 0;
                        } else {
                            showFlash('error', response.error || 'Error uploading files')
                        }
                    } else {
                        showFlash('error', 'Server error: ' + xhr.status)
                    }

                    uploadProgress.style.display = 'none';
                };

                xhr.onerror = function () {
                    showFlash('error', 'Connection error')
                    uploadProgress.style.display = 'none';
                };

                xhr.send(formData);
            } catch (error) {
                showFlash('error', 'Error sending request to server')
                uploadProgress.style.display = 'none';
            }
        }

        function updateFileList() {
            if (uploadedFiles.length > 0) {
                fileListContainer.style.display = 'block';
                fileCount.textContent = uploadedFiles.length;

                fileList.innerHTML = '';
                uploadedFiles.forEach((file, index) => {
                    const li = document.createElement('li');
                    li.className = 'file-item fade-in';

                    li.innerHTML = `
                        <div class="file-icon">
                            <i class="${getFileIcon(file.mimeType)}"></i>
                        </div>
                        <div class="file-info">
                            <div class="file-name">${file.originalFilename}</div>
                            <div class="file-meta">${file.fileSize} &middot; ${getFileType(file.mimeType)}</div>
                        </div>
                        <button type="button" class="remove-btn" data-index="${index}" data-token="${file.sessionToken}">
                            <i class="fas fa-times"></i>
                        </button>
                    `;

                    fileList.appendChild(li);
                });

                document.querySelectorAll('.remove-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const index = parseInt(this.dataset.index);
                        const token = this.dataset.token;
                        removeFile(index, token);
                    });
                });
            } else {
                fileListContainer.style.display = 'none';
            }
        }

        function removeFile(index, token) {
            uploadedFiles.splice(index, 1);
            updateFileList();
            submitBtn.disabled = uploadedFiles.length === 0;

            fetch(`/send/api/remove-file/${token}`, {
                method: 'DELETE'
            })
                .then(response => response.json())
                .then(data => {
                    console.log('File removed from session', data);
                })
                .catch(error => {
                    showFlash('error','Error removing file from session:', error);
                });
        }

        transferForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (uploadedFiles.length === 0) {
                showFlash('error', 'At least one file must be uploaded')
                return;
            }

            const formData = {
                recipientEmail: document.getElementById('recipient-email').value,
                subject: document.getElementById('subject').value,
                message: document.getElementById('message').value,
                expirationAt: document.getElementById('expirationAt').value
            };

            if (!formData.recipientEmail || !formData.subject || !formData.message || !formData.expirationAt) {
                showFlash('error', 'Please fill in all required form fields')
                return;
            }
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Sending... <i class="fas fa-spinner fa-spin"></i>';

            try {
                const response = await fetch(api_file_transfer_create, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(formData)
                        }
                    )
                ;

                const result = await response.json();

                if (response.ok) {
                    showFlash('success', 'Files sent successfully! The recipient will be notified via email.')
                    uploadedFiles = [];
                    updateFileList();
                    transferForm.reset();
                } else {
                    showFlash('error', result.error || 'Error creating file transfer')
                    submitBtn.disabled = false;
                }
            } catch (error) {
                showFlash('error', 'Error sending request to server')
                submitBtn.disabled = false;
            } finally {
                submitBtn.innerHTML = 'Send Files <i class="fas fa-paper-plane"></i>';
                submitBtn.disabled = uploadedFiles.length === 0;
            }
        });

        function getFileIcon(mimeType) {
            if (mimeType.startsWith('image/')) return 'fas fa-file-image';
            if (mimeType.startsWith('video/')) return 'fas fa-file-video';
            if (mimeType.startsWith('audio/')) return 'fas fa-file-audio';
            if (mimeType === 'application/pdf') return 'fas fa-file-pdf';
            if (mimeType.includes('spreadsheet') || mimeType.includes('excel')) return 'fas fa-file-excel';
            if (mimeType.includes('document') || mimeType.includes('word')) return 'fas fa-file-word';
            if (mimeType.includes('presentation') || mimeType.includes('powerpoint')) return 'fas fa-file-powerpoint';
            if (mimeType === 'application/zip' || mimeType === 'application/x-zip-compressed') return 'fas fa-file-archive';

            return 'fas fa-file';
        }

        function getFileType(mimeType) {
            const types = {
                'image/': 'Image',
                'video/': 'Video',
                'audio/': 'Audio',
                'application/pdf': 'PDF',
                'application/zip': 'Archive',
                'application/x-zip-compressed': 'Archive',
                'application/msword': 'Word',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'Word',
                'application/vnd.ms-excel': 'Excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'Excel',
                'application/vnd.ms-powerpoint': 'PowerPoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'PowerPoint'
            };

            for (const [key, value] of Object.entries(types)) {
                if (mimeType === key || mimeType.startsWith(key)) {
                    return value;
                }
            }

            return 'File';
        }
    }
});
