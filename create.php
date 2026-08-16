<?php
/**
 * Create New Blog Post
 */
require_once 'config.php';
require_once 'auth.php';

// Require authentication
requireLogin();

$pageTitle = 'Create New Post';

$errors = [];
$title = '';
$content = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) > 255) {
        $errors[] = 'Title must be less than 255 characters';
    }

    if (empty($content)) {
        $errors[] = 'Blog content is required';
    }

    // Save to database
    if (empty($errors)) {
        $conn = getDBConnection();
        $userId = getCurrentUserId();

        $stmt = $conn->prepare("
            INSERT INTO blogPost (user_id, title, content)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('iss', $userId, $title, $content);

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

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>Create New Post</h1>
    <p class="page-subtitle">Share your thoughts with the community</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
            <p><?php echo escape($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" action="create.php" class="blog-form" id="blog-form">
    <div class="form-group">
        <label for="title">Post Title</label>
        <input
            type="text"
            id="title"
            name="title"
            value="<?php echo escape($title); ?>"
            placeholder="Enter a compelling title..."
            required
            maxlength="255"
        >
    </div>

    <div class="form-group">
        <div class="editor-tabs">
            <button type="button" class="editor-tab active" data-tab="write">Write</button>
            <button type="button" class="editor-tab" data-tab="preview">Preview</button>
        </div>

        <div class="editor-container">
            <!-- Write Tab -->
            <div class="editor-pane active" id="write-pane">
                <textarea
                    id="content"
                    name="content"
                    placeholder="Write your blog post here...&#10;&#10;Supports Markdown:&#10;**bold** *italic* `code`&#10;# Header&#10;- List items"
                    required
                    rows="20"
                ><?php echo escape($content); ?></textarea>
                <div class="editor-help">
                    <small>
                        Markdown supported:
                        <code>**bold**</code>
                        <code>*italic*</code>
                        <code>`code`</code>
                        <code># header</code>
                        <code>[link](url)</code>
                    </small>
                </div>
            </div>

            <!-- Preview Tab -->
            <div class="editor-pane" id="preview-pane">
                <div class="markdown-preview" id="preview-content">
                    <em>Preview will appear here...</em>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Publish Post</button>
        <a href="index.php" class="btn btn-outline">Cancel</a>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>