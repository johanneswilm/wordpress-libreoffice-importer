/**
 * LibreOffice Importer Admin JavaScript
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/assets/js
 */

(function($) {
    'use strict';

    /**
     * Main plugin object
     */
    const LibreOfficeImporter = {
        /**
         * Initialize the plugin
         */
        init: function() {
            this.setupTabs();
            this.setupODTUpload();
            this.setupHTMLImport();
            this.setupModal();
            this.setupFilePreview();
        },

        /**
         * Setup tab switching
         */
        setupTabs: function() {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                const tab = $(this).data('tab');
                
                // Update active tab
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Show corresponding content
                $('.tab-content').removeClass('active');
                $('#tab-' + tab).addClass('active');
                
                // Update URL hash
                window.location.hash = tab;
            });

            // Check if there's a hash in URL
            if (window.location.hash) {
                const hash = window.location.hash.substring(1);
                $('.nav-tab[data-tab="' + hash + '"]').trigger('click');
            }
        },

        /**
         * Setup ODT file upload handling
         */
        setupODTUpload: function() {
            const self = this;

            $('#odt-upload-form').on('submit', function(e) {
                e.preventDefault();

                const fileInput = $('#odt_file')[0];
                
                if (!fileInput.files || !fileInput.files[0]) {
                    self.showNotice(libreofficeImporter.strings.file_required, 'error');
                    return;
                }

                const file = fileInput.files[0];

                // Validate file extension
                if (!file.name.toLowerCase().endsWith('.odt')) {
                    self.showNotice('Please select a valid ODT file.', 'error');
                    return;
                }

                // Prepare form data
                const formData = new FormData();
                formData.append('action', 'libreoffice_import_odt');
                formData.append('nonce', libreofficeImporter.nonce);
                formData.append('odt_file', file);

                // Update UI
                self.showProgress('#upload-progress', libreofficeImporter.strings.uploading);
                $('#upload-submit').prop('disabled', true).addClass('loading');

                // Send AJAX request
                $.ajax({
                    url: libreofficeImporter.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percent = (e.loaded / e.total) * 100;
                                self.updateProgress('#upload-progress', percent);
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        if (response.success) {
                            self.hideProgress('#upload-progress');
                            self.showSuccessModal(response.data);
                            self.resetForm('#odt-upload-form');
                        } else {
                            self.hideProgress('#upload-progress');
                            self.showNotice(
                                libreofficeImporter.strings.error + ' ' + response.data.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        self.hideProgress('#upload-progress');
                        self.showNotice(
                            libreofficeImporter.strings.error + ' ' + error,
                            'error'
                        );
                    },
                    complete: function() {
                        $('#upload-submit').prop('disabled', false).removeClass('loading');
                    }
                });
            });
        },

        /**
         * Setup HTML content import handling
         */
        setupHTMLImport: function() {
            const self = this;
            const editor = $('#html-content-editor');
            const textarea = $('#html_content');

            // Handle paste events
            editor.on('paste', function(e) {
                setTimeout(function() {
                    self.updatePastePreview();
                }, 100);
            });

            // Handle input events
            editor.on('input', function() {
                self.updatePastePreview();
            });

            // Clear button
            $('#clear-paste').on('click', function() {
                editor.html('');
                textarea.val('');
                $('#paste-preview').hide();
            });

            // Form submission
            $('#html-import-form').on('submit', function(e) {
                e.preventDefault();

                const content = editor.html();
                
                if (!content || content.trim() === '') {
                    self.showNotice(libreofficeImporter.strings.html_required, 'error');
                    return;
                }

                // Update hidden textarea
                textarea.val(content);

                // Prepare data
                const data = {
                    action: 'libreoffice_import_html',
                    nonce: libreofficeImporter.nonce,
                    html_content: content
                };

                // Update UI
                self.showProgress('#paste-progress', libreofficeImporter.strings.processing);
                $('#paste-submit').prop('disabled', true).addClass('loading');

                // Send AJAX request
                $.ajax({
                    url: libreofficeImporter.ajax_url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            self.hideProgress('#paste-progress');
                            self.showSuccessModal(response.data);
                            self.resetForm('#html-import-form');
                            editor.html('');
                            $('#paste-preview').hide();
                        } else {
                            self.hideProgress('#paste-progress');
                            self.showNotice(
                                libreofficeImporter.strings.error + ' ' + response.data.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        self.hideProgress('#paste-progress');
                        self.showNotice(
                            libreofficeImporter.strings.error + ' ' + error,
                            'error'
                        );
                    },
                    complete: function() {
                        $('#paste-submit').prop('disabled', false).removeClass('loading');
                    }
                });
            });
        },

        /**
         * Setup file preview
         */
        setupFilePreview: function() {
            $('#odt_file').on('change', function() {
                const file = this.files[0];
                
                if (file) {
                    $('#odt-file-name').text('File: ' + file.name);
                    $('#odt-file-size').text('Size: ' + formatFileSize(file.size));
                    $('#odt-preview').slideDown();
                } else {
                    $('#odt-preview').slideUp();
                }
            });

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }
        },

        /**
         * Update paste preview
         */
        updatePastePreview: function() {
            const content = $('#html-content-editor').html();
            
            if (content && content.trim() !== '') {
                const previewContent = content.substring(0, 500);
                $('#paste-preview-content').html(previewContent + (content.length > 500 ? '...' : ''));
                $('#paste-preview').slideDown();
            } else {
                $('#paste-preview').slideUp();
            }
        },

        /**
         * Show progress indicator
         */
        showProgress: function(selector, message) {
            const $progress = $(selector);
            $progress.find('.progress-message').text(message);
            $progress.find('.progress-fill').css('width', '10%');
            $progress.slideDown();
        },

        /**
         * Update progress bar
         */
        updateProgress: function(selector, percent) {
            $(selector).find('.progress-fill').css('width', percent + '%');
        },

        /**
         * Hide progress indicator
         */
        hideProgress: function(selector) {
            $(selector).slideUp(function() {
                $(this).find('.progress-fill').css('width', '0');
            });
        },

        /**
         * Show notice message
         */
        showNotice: function(message, type) {
            const $notice = $('#import-notice');
            
            $notice.removeClass('notice-success notice-error notice-warning');
            
            if (type === 'success') {
                $notice.addClass('notice-success');
            } else if (type === 'error') {
                $notice.addClass('notice-error');
            } else {
                $notice.addClass('notice-warning');
            }
            
            $notice.find('p').text(message);
            $notice.slideDown();
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $notice.slideUp();
            }, 5000);
        },

        /**
         * Setup modal functionality
         */
        setupModal: function() {
            const self = this;
            const $modal = $('#success-modal');

            // Close modal on overlay click
            $modal.find('.modal-overlay').on('click', function() {
                self.hideModal();
            });

            // Close modal on close button click
            $modal.find('.modal-close').on('click', function() {
                self.hideModal();
            });

            // Close modal on ESC key
            $(document).on('keyup', function(e) {
                if (e.key === 'Escape' && $modal.is(':visible')) {
                    self.hideModal();
                }
            });

            // Import another button
            $('#modal-new-import').on('click', function() {
                self.hideModal();
            });
        },

        /**
         * Show success modal
         */
        showSuccessModal: function(data) {
            const $modal = $('#success-modal');
            
            // Populate modal data
            $('#modal-title').text(data.post_title || 'Untitled');
            $('#modal-status').text(this.formatStatus(data.post_status));
            $('#modal-author').text(data.author || 'Unknown');
            $('#modal-images').text(data.images_count + ' image(s) imported');
            
            // Set up links
            $('#modal-edit-link').attr('href', data.edit_url);
            $('#modal-view-link').attr('href', data.view_url);
            
            // Show modal
            $modal.fadeIn(300);
        },

        /**
         * Hide modal
         */
        hideModal: function() {
            $('#success-modal').fadeOut(300);
        },

        /**
         * Format post status
         */
        formatStatus: function(status) {
            const statuses = {
                'publish': 'Published',
                'draft': 'Draft',
                'pending': 'Pending Review',
                'private': 'Private'
            };
            
            return statuses[status] || status.charAt(0).toUpperCase() + status.slice(1);
        },

        /**
         * Reset form
         */
        resetForm: function(selector) {
            $(selector)[0].reset();
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        if ($('.libreoffice-importer-wrap').length) {
            LibreOfficeImporter.init();
        }
    });

})(jQuery);