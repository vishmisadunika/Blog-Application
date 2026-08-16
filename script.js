/**
 * Blog Application Client-side JavaScript
 * Handles: Markdown preview, tab switching, mobile nav, form enhancements
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    initMobileNav();
    initEditorTabs();
    initMarkdownPreview();
    initFormEnhancements();
});

/**
 * Mobile navigation toggle
 */
function initMobileNav() {
    const toggle = document.querySelector('.nav-toggle');
    const menu = document.querySelector('.nav-menu');

    if (!toggle || !menu) return;

    toggle.addEventListener('click', function() {
        menu.classList.toggle('active');
        toggle.classList.toggle('active');
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!toggle.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('active');
            toggle.classList.remove('active');
        }
    });

    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            menu.classList.remove('active');
            toggle.classList.remove('active');
        }
    });
}

/**
 * Editor tab switching (Write / Preview)
 */
function initEditorTabs() {
    const tabs = document.querySelectorAll('.editor-tab');
    const writePane = document.getElementById('write-pane');
    const previewPane = document.getElementById('preview-pane');

    if (!tabs.length || !writePane || !previewPane) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Show corresponding pane
            if (targetTab === 'write') {
                writePane.classList.add('active');
                previewPane.classList.remove('active');
            } else if (targetTab === 'preview') {
                writePane.classList.remove('active');
                previewPane.classList.add('active');

                // Update preview content when switching to preview tab
                updateMarkdownPreview();
            }
        });
    });
}

/**
 * Live Markdown preview
 */
function initMarkdownPreview() {
    const textarea = document.getElementById('content');
    const previewContent = document.getElementById('preview-content');

    if (!textarea || !previewContent) return;

    // Update preview on input (debounced)
    let previewTimeout;
    textarea.addEventListener('input', function() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(updateMarkdownPreview, 300);
    });

    // Initial preview if content exists
    if (textarea.value.trim()) {
        updateMarkdownPreview();
    }
}

/**
 * Convert markdown text to HTML preview
 */
function updateMarkdownPreview() {
    const textarea = document.getElementById('content');
    const previewContent = document.getElementById('preview-content');

    if (!textarea || !previewContent) return;

    const markdown = textarea.value.trim();

    if (!markdown) {
        previewContent.innerHTML = '<em>Preview will appear here...</em>';
        return;
    }

    // Convert markdown to HTML
    const html = convertMarkdownToHtml(markdown);
    previewContent.innerHTML = html;
}

/**
 * Basic Markdown to HTML converter (client-side)
 * Matches the server-side logic in auth.php
 */
function convertMarkdownToHtml(text) {
    let html = escapeHtml(text);

    // Headers (h3 before h2 before h1 to avoid partial matches)
    html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>');
    html = html.replace(/^## (.+)$/gm, '<h2>$1</h2>');
    html = html.replace(/^# (.+)$/gm, '<h1>$1</h1>');

    // Bold
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

    // Italic
    html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');

    // Inline code
    html = html.replace(/`(.+?)`/g, '<code>$1</code>');

    // Links [text](url)
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');

    // Unordered lists
    html = html.replace(/^- (.+)$/gm, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>\n?)+/gs, '<ul>$&</ul>');

    // Ordered lists
    html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>\n?)+/gs, match => {
        // Only wrap if not already wrapped in <ul>
        if (!match.includes('<ul>')) {
            return '<ol>' + match + '</ol>';
        }
        return match;
    });

    // Paragraphs (split by double newlines)
    const paragraphs = html.split(/\n\n+/);
    html = paragraphs.map(p => {
        p = p.trim();
        if (!p) return '';
        // Don't wrap block elements in <p>
        if (/^<(h[1-6]|ul|ol|pre|blockquote)/.test(p)) {
            return p;
        }
        return '<p>' + p.replace(/\n/g, '<br>') + '</p>';
    }).join('');

    // Clean up empty paragraphs
    html = html.replace(/<p>\s*<\/p>/g, '');
    html = html.replace(/<p>(<h[1-6]>)/g, '$1');
    html = html.replace(/(<\/h[1-6]>)<\/p>/g, '$1');
    html = html.replace(/<p>(<ul>)/g, '$1');
    html = html.replace(/(<\/ul>)<\/p>/g, '$1');
    html = html.replace(/<p>(<ol>)/g, '$1');
    html = html.replace(/(<\/ol>)<\/p>/g, '$1');

    return html;
}

/**
 * Escape HTML special characters
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Form enhancements
 */
function initFormEnhancements() {
    // Auto-focus first input on auth pages
    const firstInput = document.querySelector('.auth-form input:first-of-type');
    if (firstInput) {
        // Only auto-focus if not on mobile (keyboard would pop up)
        if (window.innerWidth > 768) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }

    // Confirm before leaving editor with unsaved changes
    const blogForm = document.getElementById('blog-form');
    if (blogForm) {
        let formChanged = false;

        blogForm.addEventListener('input', function() {
            formChanged = true;
        });

        blogForm.addEventListener('submit', function() {
            formChanged = false;
        });

        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
    }

    // Character counter for title input
    const titleInput = document.querySelector('input[name="title"]');
    if (titleInput && titleInput.maxLength) {
        const maxLen = parseInt(titleInput.maxLength);
        const counter = document.createElement('small');
        counter.className = 'form-hint';
        counter.style.float = 'right';

        function updateCounter() {
            const remaining = maxLen - titleInput.value.length;
            counter.textContent = `${remaining} characters remaining`;
            counter.style.color = remaining < 20 ? '#dc2626' : '';
        }

        titleInput.addEventListener('input', updateCounter);
        titleInput.parentNode.appendChild(counter);
        updateCounter();
    }

    // Password strength indicator (simple)
    const passwordInput = document.querySelector('input[name="password"]');
    if (passwordInput && document.querySelector('input[name="confirm_password"]')) {
        passwordInput.addEventListener('input', function() {
            const strength = getPasswordStrength(this.value);
            // Could add visual indicator here if desired
            this.setCustomValidity(strength === 'weak' && this.value.length > 0
                ? 'Password is too weak. Use at least 6 characters with a mix of letters and numbers.'
                : '');
        });
    }
}

/**
 * Simple password strength checker
 */
function getPasswordStrength(password) {
    if (password.length < 6) return 'weak';
    if (password.length < 8) return 'medium';
    const hasLetter = /[a-zA-Z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    if (hasLetter && hasNumber) return 'strong';
    return 'medium';
}

/**
 * Utility: Show a temporary toast notification
 * (Can be used for future enhancements)
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'error'}`;
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '1000';
    toast.style.maxWidth = '300px';
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Expose some functions globally for debugging if needed
window.BlogApp = {
    updatePreview: updateMarkdownPreview,
    showToast: showToast
};
