document.addEventListener('DOMContentLoaded', function() {
    initMobileNav();
    initEditorTabs();
    initEditorToolbar();
    initMarkdownPreview();
    initFormEnhancements();
    initCoverImageUpload();
    initInlineImageUpload();
    initLikeButton();
    initBookmarkButton();
    initShareButtons();
    initScrollAnimations();
    initStatsCounter();
    initNewsletterForm();
    initReadingProgress();
    initBackToTop();
    initImageLightbox();
    initImageFallback();
    initWordCount();
    initPasswordStrength();
    initSearch();
    initRippleButtons();
    initThemeToggle();
});

function initThemeToggle() {
    const toggle = document.getElementById('theme-toggle');
    if (!toggle) return;

    const root = document.documentElement;

    const setTheme = (theme) => {
        root.setAttribute('data-theme', theme);
        localStorage.setItem('inkbloom-theme', theme);
        toggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    };

    // Header inline script already set the initial attribute; just sync the button state.
    toggle.setAttribute('aria-pressed', root.getAttribute('data-theme') === 'dark' ? 'true' : 'false');

    toggle.addEventListener('click', function () {
        const current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        setTheme(next);
    });

    // Follow the OS theme automatically until the user picks one explicitly
    if (!localStorage.getItem('inkbloom-theme') && window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('inkbloom-theme')) {
                root.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        });
    }
}

// Polyfill window.Inkbloom if not present
window.Inkbloom = window.Inkbloom || {};

function initMobileNav() {
    const toggle = document.getElementById('nav-toggle');
    const links = document.querySelector('.nav-links');
    if (toggle && links) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            links.classList.toggle('active');
            toggle.innerHTML = links.classList.contains('active') ? '✕' : '☰';
        });
        
        document.addEventListener('click', function(e) {
            if (links.classList.contains('active') && !links.contains(e.target) && e.target !== toggle) {
                links.classList.remove('active');
                toggle.innerHTML = '☰';
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && links.classList.contains('active')) {
                links.classList.remove('active');
                toggle.innerHTML = '☰';
            }
        });
    }
}

function initEditorTabs() {
    const writeBtn = document.querySelector('.toolbar-btn[data-action="preview"]');
    const writePane = document.getElementById('write-pane');
    const previewPane = document.getElementById('preview-pane');
    const content = document.getElementById('content');
    const previewContent = document.getElementById('preview-content');
    
    if (writeBtn && writePane && previewPane) {
        writeBtn.addEventListener('click', function() {
            const isPreviewing = previewPane.style.display === 'block';
            if (isPreviewing) {
                previewPane.style.display = 'none';
                writePane.style.display = 'flex';
                writeBtn.innerHTML = '👁 Preview';
                writeBtn.classList.remove('is-active');
            } else {
                writePane.style.display = 'none';
                previewPane.style.display = 'block';
                writeBtn.innerHTML = '✎ Write';
                writeBtn.classList.add('is-active');
                if (content && previewContent) {
                    previewContent.innerHTML = convertMarkdownToHtml(content.value || '<em>Nothing to preview...</em>');
                }
            }
        });
    }
}

function initEditorToolbar() {
    const textarea = document.getElementById('content');
    if (!textarea) return;

    const toolbar = document.querySelector('.editor-toolbar');
    if (!toolbar) return;

    toolbar.addEventListener('click', function(e) {
        const btn = e.target.closest('.toolbar-btn');
        if (!btn) return;
        
        const action = btn.getAttribute('data-action');
        if (!action || action === 'preview' || action === 'image') return; // Handled separately
        
        e.preventDefault();
        textarea.focus();

        switch (action) {
            case 'bold': wrapSelectedText(textarea, '**', '**'); break;
            case 'italic': wrapSelectedText(textarea, '*', '*'); break;
            case 'underline': wrapSelectedText(textarea, '__', '__'); break;
            case 'h1': prependToLine(textarea, '# '); break;
            case 'h2': prependToLine(textarea, '## '); break;
            case 'h3': prependToLine(textarea, '### '); break;
            case 'link': 
                const url = prompt('Enter URL:');
                if (url) wrapSelectedText(textarea, '[', `](${url})`);
                break;
            case 'quote': prependToLine(textarea, '> '); break;
            case 'ul': prependToLine(textarea, '- '); break;
        }
        
        // Trigger input event to update word count
        textarea.dispatchEvent(new Event('input'));
    });
}

function wrapSelectedText(textarea, before, after) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selected = text.substring(start, end);
    
    textarea.value = text.substring(0, start) + before + selected + after + text.substring(end);
    textarea.selectionStart = start + before.length;
    textarea.selectionEnd = end + before.length;
}

function prependToLine(textarea, prefix) {
    const start = textarea.selectionStart;
    const text = textarea.value;
    
    // Find start of line
    let lineStart = start;
    while (lineStart > 0 && text[lineStart - 1] !== '\n') {
        lineStart--;
    }
    
    textarea.value = text.substring(0, lineStart) + prefix + text.substring(lineStart);
    textarea.selectionStart = start + prefix.length;
    textarea.selectionEnd = start + prefix.length;
}

function initMarkdownPreview() {
    const content = document.getElementById('content');
    const previewContent = document.getElementById('preview-content');
    
    if (content && previewContent) {
        content.addEventListener('input', function() {
            const previewPane = document.getElementById('preview-pane');
            if (previewPane && previewPane.style.display === 'block') {
                previewContent.innerHTML = convertMarkdownToHtml(content.value || '<em>Nothing to preview...</em>');
            }
        });
    }
}

function convertMarkdownToHtml(markdown) {
    if (!markdown) return '';
    
    let html = escapeHtml(markdown);
    
    // Horizontal rule
    html = html.replace(/^---$/gm, '<hr>');
    
    // Headers
    html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
    html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
    html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
    
    // Blockquote
    html = html.replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>');
    
    // Bold, Italic, Underline
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
    html = html.replace(/__(.*?)__/g, '<u>$1</u>');
    
    // Images
    html = html.replace(/!\[([^\]]+)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="content-image">');
    
    // Links
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
    
    // Lists
    html = html.replace(/^\- (.*$)/gim, '<ul><li>$1</li></ul>');
    html = html.replace(/<\/ul>\n<ul>/g, '\n');
    
    // Paragraphs
    html = html.split('\n\n').map(p => {
        if (!p.trim().startsWith('<h') && !p.trim().startsWith('<ul') && !p.trim().startsWith('<blockquote') && !p.trim().startsWith('<hr')) {
            return `<p>${p}</p>`;
        }
        return p;
    }).join('\n');
    
    return html;
}

function escapeHtml(unsafe) {
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

function initFormEnhancements() {
    // Basic form submission logic handled by standard HTML forms
}

function initCoverImageUpload() {
    const zone = document.getElementById('cover-upload-zone');
    const input = document.getElementById('cover-image-input');
    const preview = document.getElementById('cover-preview');
    const previewImg = document.getElementById('cover-preview-img');
    const hiddenUrl = document.getElementById('cover-image-url');
    const removeBtn = document.getElementById('remove-cover');
    
    if (!zone || !input) return;
    
    zone.addEventListener('click', () => input.click());
    
    zone.addEventListener('dragover', (e) => {
        e.preventDefault();
        zone.style.background = 'var(--accent-light)';
    });
    
    zone.addEventListener('dragleave', () => {
        zone.style.background = 'var(--bg-card)';
    });
    
    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.style.background = 'var(--bg-card)';
        if (e.dataTransfer.files.length) {
            handleFileUpload(e.dataTransfer.files[0], 'cover');
        }
    });
    
    input.addEventListener('change', function() {
        if (this.files.length) {
            handleFileUpload(this.files[0], 'cover');
        }
    });
    
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            preview.style.display = 'none';
            zone.style.display = 'block';
            hiddenUrl.value = '';
            input.value = '';
        });
    }
}

function initInlineImageUpload() {
    const btn = document.querySelector('.toolbar-btn[data-action="image"]');
    if (!btn) return;
    
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        
        input.addEventListener('change', function() {
            if (this.files.length) {
                handleFileUpload(this.files[0], 'inline');
            }
        });
        
        input.click();
    });
}

function handleFileUpload(file, type) {
    // Basic client-side guard rails matching upload.php's own checks
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showToast('Only JPG, PNG, GIF, and WebP images are allowed.', 'error');
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        showToast('File is too large. Maximum size is 5MB.', 'error');
        return;
    }

    const zone = document.getElementById('cover-upload-zone');
    let progressBar = null;

    if (type === 'cover' && zone) {
        // Show an animated progress bar inside the drop zone while uploading
        zone.classList.add('uploading');
        progressBar = document.createElement('div');
        progressBar.className = 'upload-progress';
        progressBar.innerHTML = '<div class="upload-progress-fill"></div><span class="upload-progress-label">Uploading… 0%</span>';
        zone.appendChild(progressBar);
    } else {
        showToast('Uploading image…', 'info');
    }

    const formData = new FormData();
    formData.append('image', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload.php', true);

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable && progressBar) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.querySelector('.upload-progress-fill').style.width = percent + '%';
            progressBar.querySelector('.upload-progress-label').textContent = `Uploading… ${percent}%`;
        }
    });

    xhr.onload = function() {
        if (progressBar) progressBar.remove();
        if (zone) zone.classList.remove('uploading');

        let data;
        try {
            data = JSON.parse(xhr.responseText);
        } catch (err) {
            showToast('Upload failed. Please try again.', 'error');
            return;
        }

        if (!data.success) {
            showToast(data.error || 'Upload failed.', 'error');
            return;
        }

        const url = data.url;

        if (type === 'cover') {
            const preview = document.getElementById('cover-preview');
            const previewImg = document.getElementById('cover-preview-img');
            const hiddenUrl = document.getElementById('cover-image-url');

            zone.style.display = 'none';
            preview.style.display = 'block';
            previewImg.src = url;
            hiddenUrl.value = url;
        } else if (type === 'inline') {
            const textarea = document.getElementById('content');
            const textToInsert = `\n![${file.name}](${url})\n`;

            const start = textarea.selectionStart;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + textToInsert + text.substring(textarea.selectionEnd);
            textarea.selectionStart = textarea.selectionEnd = start + textToInsert.length;
            textarea.dispatchEvent(new Event('input'));
        }

        showToast('Image uploaded successfully! ♡', 'success');
    };

    xhr.onerror = function() {
        if (progressBar) progressBar.remove();
        if (zone) zone.classList.remove('uploading');
        showToast('Upload failed. Please check your connection.', 'error');
    };

    xhr.send(formData);
}

function initLikeButton() {
    const btn = document.getElementById('like-btn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        if (this.dataset.busy === '1') return; // prevent double-clicks mid-request
        this.dataset.busy = '1';

        const postId = this.getAttribute('data-post-id');
        const countSpan = document.getElementById('like-count');

        fetch('like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'post_id=' + encodeURIComponent(postId)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                if (data.error && data.error.toLowerCase().includes('logged in')) {
                    showToast('Please log in to like posts.', 'error');
                    setTimeout(() => { window.location.href = 'login.php'; }, 1200);
                } else {
                    showToast(data.error || 'Something went wrong.', 'error');
                }
                return;
            }

            // Pop / burst animation
            this.classList.add('animate');
            setTimeout(() => this.classList.remove('animate'), 400);

            if (data.liked) {
                this.classList.add('liked');
                this.innerHTML = '♥';
                spawnHeartBurst(this);
                showToast('Liked! ♡', 'success');
            } else {
                this.classList.remove('liked');
                this.innerHTML = '♡';
            }

            if (countSpan) countSpan.textContent = data.count;
        })
        .catch(() => showToast('Network error. Please try again.', 'error'))
        .finally(() => { this.dataset.busy = '0'; });
    });
}

function spawnHeartBurst(btn) {
    const burst = document.createElement('span');
    burst.className = 'heart-burst';
    burst.textContent = '♥';
    btn.appendChild(burst);
    burst.addEventListener('animationend', () => burst.remove());
}

function initBookmarkButton() {
    const btn = document.getElementById('bookmark-btn');
    if (!btn) return;

    btn.addEventListener('click', function() {
        if (this.dataset.busy === '1') return;
        this.dataset.busy = '1';

        const postId = this.getAttribute('data-post-id');

        fetch('bookmark.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'post_id=' + encodeURIComponent(postId)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                if (data.error && data.error.toLowerCase().includes('logged in')) {
                    showToast('Please log in to bookmark posts.', 'error');
                    setTimeout(() => { window.location.href = 'login.php'; }, 1200);
                } else {
                    showToast(data.error || 'Something went wrong.', 'error');
                }
                return;
            }

            this.classList.add('animate');
            setTimeout(() => this.classList.remove('animate'), 400);

            if (data.bookmarked) {
                this.classList.add('bookmarked');
                this.innerHTML = '🔖';
                showToast('Saved to your bookmarks! ♡', 'success');
            } else {
                this.classList.remove('bookmarked');
                this.innerHTML = '🏷';
                showToast('Removed from bookmarks', 'info');
            }
        })
        .catch(() => showToast('Network error. Please try again.', 'error'))
        .finally(() => { this.dataset.busy = '0'; });
    });
}

function initShareButtons() {
    const btns = document.querySelectorAll('.share-btn');
    btns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            
            if (action === 'twitter') {
                window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
            } else if (action === 'facebook') {
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
            } else if (action === 'copy') {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    showToast('Link copied!', 'success');
                }).catch(err => {
                    showToast('Failed to copy', 'error');
                });
            }
        });
    });
}

function initScrollAnimations() {
    const elements = document.querySelectorAll('.animate-fade-in');
    
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    // We can optionally unobserve if we only want it to animate once
                    // observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        elements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = `opacity 0.6s ease, transform 0.6s ease`;
            el.style.transitionDelay = `${(index % 5) * 0.1}s`; // Stagger delay based on order
            observer.observe(el);
        });
    } else {
        // Fallback for older browsers
        elements.forEach(el => {
            el.style.opacity = '1';
            el.style.transform = 'none';
            el.classList.add('visible');
        });
    }
}

function initStatsCounter() {
    const stats = document.querySelectorAll('.stat-number[data-count]');
    
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCount(entry.target);
                    observer.unobserve(entry.target); // Only count once
                }
            });
        }, { threshold: 0.5 });
        
        stats.forEach(stat => observer.observe(stat));
    } else {
        stats.forEach(stat => {
            stat.textContent = formatNumber(parseInt(stat.getAttribute('data-count')));
        });
    }
}

function animateCount(element) {
    const target = parseInt(element.getAttribute('data-count'));
    const duration = 2000; // ms
    const startTime = performance.now();
    
    function updateCount(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function: easeOutQuart
        const easeOut = 1 - Math.pow(1 - progress, 4);
        const current = Math.floor(target * easeOut);
        
        element.textContent = formatNumber(current);
        
        if (progress < 1) {
            requestAnimationFrame(updateCount);
        } else {
            element.textContent = formatNumber(target);
        }
    }
    
    requestAnimationFrame(updateCount);
}

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num;
}

function initNewsletterForm() {
    const forms = document.querySelectorAll('.newsletter-form');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const emailInput = this.querySelector('input[name="email"]');
            const originalText = btn.innerHTML;

            btn.innerHTML = 'Subscribing…';
            btn.disabled = true;

            fetch('subscribe.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'email=' + encodeURIComponent(emailInput.value)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Thanks for subscribing! ♡', 'success');
                    this.reset();
                    btn.innerHTML = 'Subscribed! ✓';
                } else {
                    showToast(data.error || 'Could not subscribe. Please try again.', 'error');
                    btn.innerHTML = originalText;
                }
            })
            .catch(() => {
                showToast('Network error. Please try again.', 'error');
                btn.innerHTML = originalText;
            })
            .finally(() => {
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 2500);
            });
        });
    });
}

function initReadingProgress() {
    const content = document.querySelector('.blog-single-content');
    const progressBar = document.getElementById('reading-progress');
    
    if (!content || !progressBar) return;
    
    window.addEventListener('scroll', () => {
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        
        // Calculate based on whole page for simplicity
        const scrollPercent = (scrollTop / (documentHeight - windowHeight)) * 100;
        progressBar.style.width = scrollPercent + '%';
    });
}

function initBackToTop() {
    let btn = document.getElementById('back-to-top');
    
    if (!btn) {
        btn = document.createElement('button');
        btn.id = 'back-to-top';
        btn.className = 'back-to-top btn btn-icon';
        btn.innerHTML = '↑';
        btn.style.position = 'fixed';
        btn.style.bottom = '20px';
        btn.style.right = '20px';
        btn.style.display = 'none';
        btn.style.zIndex = '999';
        btn.style.background = 'var(--primary)';
        btn.style.color = 'white';
        btn.style.border = 'none';
        btn.style.borderRadius = '50%';
        btn.style.width = '40px';
        btn.style.height = '40px';
        btn.style.cursor = 'pointer';
        btn.style.boxShadow = 'var(--shadow)';
        document.body.appendChild(btn);
    }
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            btn.style.display = 'block';
        } else {
            btn.style.display = 'none';
        }
    });
    
    btn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

function initImageLightbox() {
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const closeBtn = document.getElementById('lightbox-close');
    
    if (!lightbox || !lightboxImg) return;
    
    // Add click to images in single view or generated from markdown
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'IMG' && (e.target.closest('.blog-single-content') || e.target.classList.contains('content-image'))) {
            lightboxImg.src = e.target.src;
            lightbox.style.display = 'flex';
        }
    });
    
    closeBtn.addEventListener('click', () => {
        lightbox.style.display = 'none';
    });
    
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            lightbox.style.display = 'none';
        }
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.style.display === 'flex') {
            lightbox.style.display = 'none';
        }
    });
}

function initImageFallback() {
    // If a cover/content image URL is broken (missing file, wrong path,
    // server blocking /uploads/, etc.) show a soft placeholder instead of
    // the browser's default broken-image icon + alt text. <img> is a
    // replaced element so ::after content isn't reliable on it directly —
    // swap in a plain <div> that inherits the same size/classes instead.
    const handleError = (img) => {
        if (!img.isConnected || img.dataset.fallbackApplied === '1') return;
        img.dataset.fallbackApplied = '1';

        const placeholder = document.createElement('div');
        placeholder.className = img.className + ' img-fallback';
        placeholder.title = img.getAttribute('alt') || '';
        img.replaceWith(placeholder);
    };

    document.querySelectorAll('img').forEach(img => {
        if (!img.getAttribute('src')) return;
        if (img.complete && img.naturalWidth === 0) {
            handleError(img);
        } else {
            img.addEventListener('error', () => handleError(img), { once: true });
        }
    });
}

function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.bottom = '20px';
        container.style.left = '50%';
        container.style.transform = 'translateX(-50%)';
        container.style.zIndex = '10000';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '10px';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Styles
    toast.style.padding = '12px 24px';
    toast.style.borderRadius = '30px';
    toast.style.color = 'white';
    toast.style.fontFamily = "'DM Sans', sans-serif";
    toast.style.fontSize = '0.9rem';
    toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(20px)';
    toast.style.transition = 'all 0.3s ease';
    
    if (type === 'success') toast.style.background = 'var(--success, #27ae60)';
    else if (type === 'error') toast.style.background = 'var(--danger, #c0392b)';
    else toast.style.background = 'var(--primary, #8b6f5c)';
    
    container.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);
    
    // Remove after 3s
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function initWordCount() {
    const textarea = document.getElementById('content');
    const wordCountSpan = document.getElementById('word-count');
    
    if (!textarea || !wordCountSpan) return;
    
    const updateCount = () => {
        const text = textarea.value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        const minutes = Math.ceil(words / 200); // Assume 200 wpm reading speed
        wordCountSpan.textContent = `${words} words · ${minutes} min read`;
    };
    
    textarea.addEventListener('input', updateCount);
    // Initial calculation
    updateCount();
}

function initSearch() {
    const input = document.getElementById('search-input');
    if (!input) return;

    // Pre-fill from an existing query string, if any
    const params = new URLSearchParams(window.location.search);
    if (params.has('q') && window.location.pathname.endsWith('search.php')) {
        input.value = params.get('q');
    }

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = input.value.trim();
            if (query) {
                window.location.href = 'search.php?q=' + encodeURIComponent(query);
            }
        }
    });
}

function initRippleButtons() {
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn');
        if (!btn) return;

        const rect = btn.getBoundingClientRect();
        const ripple = document.createElement('span');
        const size = Math.max(rect.width, rect.height);
        ripple.className = 'btn-ripple';
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';

        btn.appendChild(ripple);
        ripple.addEventListener('animationend', () => ripple.remove());
    });
}

function initPasswordStrength() {
    const passwordInput = document.getElementById('password');
    const strengthContainer = document.querySelector('.password-strength');
    const strengthFill = document.querySelector('.strength-fill');
    
    if (!passwordInput || !strengthContainer || !strengthFill) return;
    
    passwordInput.addEventListener('input', function() {
        const val = this.value;
        if (!val) {
            strengthContainer.style.display = 'none';
            return;
        }
        
        strengthContainer.style.display = 'block';
        
        let strength = 0;
        if (val.length >= 6) strength += 1;
        if (val.length >= 10) strength += 1;
        if (/[A-Z]/.test(val)) strength += 1;
        if (/[0-9]/.test(val)) strength += 1;
        if (/[^A-Za-z0-9]/.test(val)) strength += 1;
        
        if (strength <= 1) {
            strengthFill.style.width = '33%';
            strengthFill.style.background = 'var(--danger)';
        } else if (strength <= 3) {
            strengthFill.style.width = '66%';
            strengthFill.style.background = 'var(--accent)';
        } else {
            strengthFill.style.width = '100%';
            strengthFill.style.background = 'var(--success, #27ae60)';
        }
    });
}
