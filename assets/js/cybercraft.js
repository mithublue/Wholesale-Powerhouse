jQuery(document).ready(function ($) {
    let currentSource = 'cybercraftit';
    let pluginsData = {};

    // Tab switching
    $('.cc-tab').on('click', function () {
        $('.cc-tab').removeClass('active');
        $(this).addClass('active');
        currentSource = $(this).data('source');
        loadPlugins(currentSource);
    });

    // Load plugins on page load
    loadPlugins('cybercraftit');

    // Load plugins from source
    function loadPlugins(source) {
        $('#cc-loading').removeClass('cc-hidden');
        $('#cc-plugins-grid').addClass('cc-hidden');
        $('#cc-messages').html('');

        $.ajax({
            url: cybercraftAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cc_fetch_plugins',
                source: source,
                nonce: cybercraftAjax.nonce
            },
            success: function (response) {
                $('#cc-loading').addClass('cc-hidden');

                if (response.success) {
                    pluginsData[source] = response.data;
                    displayPlugins(response.data);
                } else {
                    showMessage(response.data || 'Failed to load plugins', 'error');
                }
            },
            error: function () {
                $('#cc-loading').addClass('cc-hidden');
                showMessage('Failed to connect to plugin repository', 'error');
            }
        });
    }

    // Display plugins in grid
    function displayPlugins(plugins) {
        if (!plugins || plugins.length === 0) {
            $('#cc-plugins-grid').html('<p style="text-align: center; color: #6b7280; padding: 40px;">No plugins found.</p>');
            $('#cc-plugins-grid').removeClass('cc-hidden');
            return;
        }

        let html = '';
        plugins.forEach(plugin => {
            const initials = plugin.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
            const isInstalled = plugin.installed || false;
            const isActive = plugin.active || false;

            html += `
                <div class="cc-plugin-card" data-slug="${plugin.slug}">
                    <div class="cc-plugin-header">
                        <div class="cc-plugin-icon">${initials}</div>
                        <div class="cc-plugin-info">
                            <h3 class="cc-plugin-name">${plugin.name}</h3>
                            <p class="cc-plugin-author">by ${plugin.author}</p>
                        </div>
                    </div>
                    <p class="cc-plugin-description">${plugin.description || 'No description available'}</p>
                    <div class="cc-plugin-meta">
                        <span class="cc-meta-item">
                            <span class="dashicons dashicons-download"></span>
                            ${plugin.downloads || 0} downloads
                        </span>
                        <span class="cc-meta-item">
                            <span class="dashicons dashicons-star-filled"></span>
                            ${plugin.rating || 'N/A'} rating
                        </span>
                        <span class="cc-meta-item">
                            <span class="dashicons dashicons-update"></span>
                            v${plugin.version || '1.0.0'}
                        </span>
                    </div>
                    ${isInstalled ? '<span class="cc-badge cc-badge-installed">Installed</span>' : ''}
                    ${isActive ? '<span class="cc-badge cc-badge-active">Active</span>' : ''}
                    <div class="cc-plugin-actions" style="margin-top: 15px;">
                        ${getActionButtons(plugin, isInstalled, isActive)}
                    </div>
                </div>
            `;
        });

        $('#cc-plugins-grid').html(html).removeClass('cc-hidden');
    }

    // Get action buttons based on plugin status
    function getActionButtons(plugin, isInstalled, isActive) {
        let buttons = '';

        if (!isInstalled) {
            buttons += `<button class="cc-btn cc-btn-primary cc-install-btn" data-slug="${plugin.slug}" data-download-url="${plugin.download_url || ''}">
                <span class="dashicons dashicons-download"></span>
                Install
            </button>`;
        } else if (!isActive) {
            buttons += `<button class="cc-btn cc-btn-success cc-activate-btn" data-slug="${plugin.slug}">
                <span class="dashicons dashicons-yes"></span>
                Activate
            </button>`;
            buttons += `<button class="cc-btn cc-btn-danger cc-delete-btn" data-slug="${plugin.slug}">
                <span class="dashicons dashicons-trash"></span>
                Delete
            </button>`;
        } else {
            buttons += `<button class="cc-btn cc-btn-secondary cc-deactivate-btn" data-slug="${plugin.slug}">
                <span class="dashicons dashicons-no"></span>
                Deactivate
            </button>`;
            buttons += `<button class="cc-btn cc-btn-danger cc-delete-btn" data-slug="${plugin.slug}">
                <span class="dashicons dashicons-trash"></span>
                Delete
            </button>`;
        }

        return buttons;
    }

    // Install plugin
    $(document).on('click', '.cc-install-btn', function () {
        const $btn = $(this);
        const slug = $btn.data('slug');
        const downloadUrl = $btn.data('download-url');

        if (!downloadUrl) {
            showMessage('Download URL not available', 'error');
            return;
        }

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> Installing...');

        $.ajax({
            url: cybercraftAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cc_install_plugin',
                slug: slug,
                download_url: downloadUrl,
                nonce: cybercraftAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    showMessage(response.data, 'success');
                    loadPlugins(currentSource);
                } else {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Install');
                    showMessage(response.data || 'Installation failed', 'error');
                }
            },
            error: function () {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Install');
                showMessage('Installation request failed', 'error');
            }
        });
    });

    // Activate plugin
    $(document).on('click', '.cc-activate-btn', function () {
        const $btn = $(this);
        const slug = $btn.data('slug');

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> Activating...');

        $.ajax({
            url: cybercraftAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cc_activate_plugin',
                slug: slug,
                nonce: cybercraftAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    showMessage(response.data, 'success');
                    loadPlugins(currentSource);
                } else {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Activate');
                    showMessage(response.data || 'Activation failed', 'error');
                }
            },
            error: function () {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Activate');
                showMessage('Activation request failed', 'error');
            }
        });
    });

    // Deactivate plugin
    $(document).on('click', '.cc-deactivate-btn', function () {
        const $btn = $(this);
        const slug = $btn.data('slug');

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> Deactivating...');

        $.ajax({
            url: cybercraftAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cc_deactivate_plugin',
                slug: slug,
                nonce: cybercraftAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    showMessage(response.data, 'success');
                    loadPlugins(currentSource);
                } else {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-no"></span> Deactivate');
                    showMessage(response.data || 'Deactivation failed', 'error');
                }
            },
            error: function () {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-no"></span> Deactivate');
                showMessage('Deactivation request failed', 'error');
            }
        });
    });

    // Delete plugin
    $(document).on('click', '.cc-delete-btn', function () {
        if (!confirm('Are you sure you want to delete this plugin? This action cannot be undone.')) {
            return;
        }

        const $btn = $(this);
        const slug = $btn.data('slug');

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> Deleting...');

        $.ajax({
            url: cybercraftAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cc_delete_plugin',
                slug: slug,
                nonce: cybercraftAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    showMessage(response.data, 'success');
                    loadPlugins(currentSource);
                } else {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Delete');
                    showMessage(response.data || 'Deletion failed', 'error');
                }
            },
            error: function () {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Delete');
                showMessage('Deletion request failed', 'error');
            }
        });
    });

    // Show message
    function showMessage(message, type) {
        const classes = {
            success: 'cc-message cc-message-success',
            error: 'cc-message cc-message-error'
        };

        const html = `<div class="${classes[type]}">${message}</div>`;
        $('#cc-messages').html(html);

        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(() => {
                $('#cc-messages').fadeOut(500, function () {
                    $(this).html('').show();
                });
            }, 5000);
        }

        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    // Escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
});
