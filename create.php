<?php
require_once 'config.php';
require_once 'auth.php';

requireLogin();

$pageTitle = 'Create New Post';
$errors = [];
$title = '';
$content = '';
$excerpt = '';
$topic_id = '';
$cover_image = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $topic_id = !empty($_POST['topic_id']) ? (int)$_POST['topic_id'] : null;
    $cover_image = trim($_POST['cover_image'] ?? '');
    
    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) > 255) {
        $errors[] = 'Title must be less than 255 characters';
    }

    if (empty($content)) {
        $errors[] = 'Blog content is required';
    }

    if (empty($errors)) {
        $conn = getDBConnection();
        $userId = getCurrentUserId();
        $reading_time = getReadingTime($content);

        $stmt = $conn->prepare("
            INSERT INTO blogPost (user_id, title, content, excerpt, topic_id, cover_image, reading_time)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('isssisi', $userId, $title, $content, $excerpt, $topic_id, $cover_image, $reading_time);

        if ($stmt->execute()) {
            $newPostId = $stmt->insert_id;
            $stmt->close();
            redirectWithSuccess('Blog post created successfully!', 'view.php?id=' . $newPostId);
        } else {
            $errors[] = 'Failed to save post. Please try again.';
            $stmt->close();
        }
    }
}

$topics = getTopics();

require_once 'includes/header.php';
?>

<div class="editor-page">
    <div class="page-header animate-fade-in" style="margin-bottom: 2rem;">
        <h1 class="page-title script-font" style="font-size: 2.5rem; color: var(--primary);">Start writing ♡</h1>
        <p class="page-subtitle" style="color: var(--text-light);">Share your thoughts. May others feel a little less alone.</p>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="background: var(--accent-light); color: var(--danger); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php foreach ($errors as $error): ?>
                <p><?php echo escape($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="create.php" class="editor-form" id="blog-form" enctype="multipart/form-data">
        <div class="editor-layout" style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem;">
            <div class="editor-main">
                <!-- Title input -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="title" style="display: block; margin-bottom: 0.5rem; color: var(--primary-dark); font-weight: 500;">Title</label>
                    <input type="text" id="title" name="title" placeholder="Give your article a catchy title..." required maxlength="255" value="<?php echo escape($title); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; font-family: 'Playfair Display', serif; font-size: 1.25rem;">
                </div>
                
                <!-- Topic Selector -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="topic_id" style="display: block; margin-bottom: 0.5rem; color: var(--primary-dark); font-weight: 500;">Topic</label>
                    <select id="topic_id" name="topic_id" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-card); color: var(--text);">
                        <option value="">Select a topic</option>
                        <?php foreach ($topics as $topic): ?>
                            <option value="<?php echo $topic['id']; ?>" <?php echo $topic_id === $topic['id'] ? 'selected' : ''; ?>>
                                <?php echo escape($topic['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Excerpt -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="excerpt" style="display: block; margin-bottom: 0.5rem; color: var(--primary-dark); font-weight: 500;">Excerpt</label>
                    <textarea id="excerpt" name="excerpt" rows="2" placeholder="A brief summary of your article..." maxlength="500" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; resize: vertical;"><?php echo escape($excerpt); ?></textarea>
                </div>
                
                <!-- Rich Editor Toolbar -->
                <div class="editor-toolbar" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px 8px 0 0; padding: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.25rem; align-items: center;">
                    <button type="button" class="toolbar-btn" data-action="bold" title="Bold" style="padding: 0.25rem 0.5rem; background: none; border: none; cursor: pointer; border-radius: 4px;"><b>B</b></button>
                    <button type="button" class="toolbar-btn" data-action="italic" title="Italic" style="padding: 0.25rem 0.5rem; background: none; border: none; cursor: pointer; border-radius: 4px;"><i>I</i></button>
                    <button type="button" class="toolbar-btn" data-action="underline" title="Underline" style="padding: 0.25rem 0.5rem; background: none; border: none; cursor: pointer; border-radius: 4px;"><u>U</u></button>
                    <span class="toolbar-divider" style="width: 1px; height: 1.5rem; background: var(--border); margin: 0 0.25rem;"></span>
                    <button type="button" class="toolbar-btn" data-action="h1" title="Heading 1" style="padding: 0.25rem 0.5rem; background: none; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">H1</button>
                    <button type="button" class="toolbar-btn" data-action="h2" title="Heading 2" style="padding: 0.25rem 0.5rem; background: none; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">H2</button>
                    <button type="button" class="toolbar-btn" data-action="h3" title="Heading 3" style="padding: 0.25rem 0.5rem; background: none; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">H3</button>
                    <span class="toolbar-divider" style="width: 1px; height: 1.5rem; background: var(--border); margin: 0 0.25rem;"></span>
                    <button type="button" class="toolbar-btn" data-action="link" title="Insert Link" style="padding: 0.25rem 0.5rem; background: none; border: none; cursor: pointer; border-radius: 4px;">🔗</button>
                    <button type="button" class="toolbar-btn" data-action="image" title="Insert Image" style="padding: 0.25rem 0.5rem; background: none; border: none; cursor: pointer; border-radius: 4px;">🖼️</button>
                    <button type="button" class="toolbar-btn" data-action="quote" title="Blockquote" style="padding: 0.25rem 0.5rem; background: none; border: none; cursor: pointer; border-radius: 4px;">❝</button>
                    <button type="button" class="toolbar-btn" data-action="ul" title="Bullet List" style="padding: 0.25rem 0.5rem; background: none; border: none; cursor: pointer; border-radius: 4px;">• List</button>
                    <span class="toolbar-divider" style="width: 1px; height: 1.5rem; background: var(--border); margin: 0 0.25rem; flex-grow: 1;"></span>
                    <button type="button" class="toolbar-btn preview-toggle-btn" data-action="preview" title="Preview">👁 Preview</button>
                </div>
                
                <!-- Tabs: Write / Preview -->
                <div class="editor-container" style="border: 1px solid var(--border); border-top: none; border-radius: 0 0 8px 8px; background: var(--bg-card); min-height: 400px; display: flex; flex-direction: column;">
                    <div class="editor-pane active" id="write-pane" style="flex-grow: 1; display: flex; flex-direction: column;">
                        <textarea id="content" name="content" placeholder="Start writing your story..." required rows="18" style="width: 100%; height: 100%; min-height: 400px; padding: 1rem; border: none; resize: vertical; outline: none; font-family: 'DM Sans', sans-serif; line-height: 1.6; background: var(--bg-card); color: var(--text);"><?php echo escape($content); ?></textarea>
                    </div>
                    <div class="editor-pane" id="preview-pane" style="display: none; padding: 1.5rem; flex-grow: 1; overflow-y: auto; background: var(--bg-card); color: var(--text);">
                        <div class="markdown-preview" id="preview-content"><em>Preview will appear here...</em></div>
                    </div>
                </div>
                
                <!-- Word count -->
                <div class="editor-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                    <span class="word-count" id="word-count" style="color: var(--text-light); font-size: 0.9rem;">0 words · 0 min read</span>
                    <div class="form-actions" style="display: flex; gap: 0.5rem;">
                        <a href="index.php" class="btn btn-outline">Cancel</a>
                        <button type="submit" name="action" value="publish" class="btn btn-primary">Publish article ♡</button>
                    </div>
                </div>
            </div>
            
            <div class="editor-sidebar">
                <!-- Cover Image Upload -->
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--primary-dark); font-weight: 500;">Add cover image</label>
                    <div class="cover-upload" id="cover-upload-zone" style="border: 2px dashed var(--border); border-radius: 8px; padding: 2rem 1rem; text-align: center; cursor: pointer; background: var(--bg-card); transition: all 0.2s;">
                        <div class="cover-upload-icon" style="font-size: 2rem; margin-bottom: 0.5rem;">📷</div>
                        <div class="cover-upload-text" style="color: var(--text-light); font-size: 0.9rem;">Upload image<br>or drag and drop</div>
                        <input type="file" id="cover-image-input" accept="image/*" style="display:none">
                    </div>
                    <div class="cover-preview" id="cover-preview" style="display:none; position: relative; border-radius: 8px; overflow: hidden; margin-top: 0.5rem;">
                        <img src="" alt="Cover preview" id="cover-preview-img" style="width: 100%; height: auto; display: block;">
                        <button type="button" class="btn btn-icon remove-cover" id="remove-cover" style="position: absolute; top: 0.5rem; right: 0.5rem; background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">✕</button>
                    </div>
                    <input type="hidden" name="cover_image" id="cover-image-url" value="<?php echo escape($cover_image); ?>">
                </div>
                
                <!-- Writing Tips -->
                <div class="writing-tips" style="background: var(--accent-light); padding: 1.5rem; border-radius: 8px;">
                    <h3 class="script-font" style="color: var(--primary-dark); font-size: 1.5rem; margin-bottom: 1rem;">Writing tips ♡</h3>
                    <ul style="color: var(--text); padding-left: 1.25rem; font-size: 0.95rem; line-height: 1.6; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li>Write from the heart</li>
                        <li>Be honest and helpful</li>
                        <li>Add examples or stories</li>
                        <li>Proofread before publishing</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>