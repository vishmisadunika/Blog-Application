<?php
/**
 * Edit Existing Blog Post
 */
require_once 'config.php';
require_once 'auth.php';

// Require authentication
requireLogin();

$pageTitle = 'Edit Post';

$errors = [];
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$title = '';
$content = '';
$postUserId = 0;

// Fetch existing post
if ($postId > 0) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT id, user_id, title, content
        FROM blogPost
        WHERE id = ?
    ");
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();

    if (!$post) {
        redirectWithError('Post not found', 'index.php');
    }

    $title = $post['title'];
    $content = $post['content'];
    $postUserId = $post['user_id'];

    // Check ownership
    if (!isPostOwner($postUserId)) {
        redirectWithError('You do not have permission to edit this post', 'index.php');
    }
} else {
    redirectWithError('Invalid post ID', 'index.php');
}

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

    // Update database
    if (empty($errors)) {
        $conn = getDBConnection();

        $stmt = $conn->prepare("
            UPDATE blogPost
            SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param('ssii', $title, $content, $postId, $postUserId);

        if ($stmt->execute()) {
            $stmt->close();
            redirectWithSuccess('Post updated successfully!', 'view.php?id=' . $postId);
        } else {
            $errors[] = 'Failed to update post. Please try again.';
            $stmt->close();
        }
    }
}

$pageTitle = 'Edit: ' . $title;
require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>Edit Post</h1>
    <p class="page-subtitle">Make changes to your blog post</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
            <p><?php echo escape($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" action="edit.php?id=<?php echo $postId; ?>" class="blog-form" id="blog-form">
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
                    placeholder="Write your blog post here..."
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
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="view.php?id=<?php echo $postId; ?>" class="btn btn-outline">Cancel</a>
        <a href="delete.php?id=<?php echo $postId; ?>"
           class="btn btn-danger"
           style="margin-left: auto;"
           onclick="return confirm('Are you sure you want to delete this post?');">
            Delete Post
        </a>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>