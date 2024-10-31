<?php
include 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $titleEn = $_POST['title_en'];
                $titleRu = $_POST['title_ru'];
                $contentEn = $_POST['content_en'];
                $contentRu = $_POST['content_ru'];
                
                // Handle image upload
                $targetDir = "uploads/blog/";
                $fileName = time() . basename($_FILES["image"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                    $stmt = $conn->prepare("
                        INSERT INTO blog_posts (
                            title_en, title_ru, content_en, content_ru, 
                            image, seo_title, seo_description, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $titleEn,
                        $titleRu,
                        $contentEn,
                        $contentRu,
                        $fileName,
                        $_POST['seo_title'],
                        $_POST['seo_description']
                    ]);
                }
                break;
                
            case 'edit':
                $id = $_POST['post_id'];
                $titleEn = $_POST['title_en'];
                $titleRu = $_POST['title_ru'];
                $contentEn = $_POST['content_en'];
                $contentRu = $_POST['content_ru'];
                
                if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                    $targetDir = "uploads/blog/";
                    $fileName = time() . basename($_FILES["image"]["name"]);
                    $targetFilePath = $targetDir . $fileName;
                    
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                        $stmt = $conn->prepare("
                            UPDATE blog_posts SET 
                                title_en = ?, title_ru = ?, content_en = ?, content_ru = ?,
                                image = ?, seo_title = ?, seo_description = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $titleEn,
                            $titleRu,
                            $contentEn,
                            $contentRu,
                            $fileName,
                            $_POST['seo_title'],
                            $_POST['seo_description'],
                            $id
                        ]);
                    }
                } else {
                    $stmt = $conn->prepare("
                        UPDATE blog_posts SET 
                            title_en = ?, title_ru = ?, content_en = ?, content_ru = ?,
                            seo_title = ?, seo_description = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $titleEn,
                        $titleRu,
                        $contentEn,
                        $contentRu,
                        $_POST['seo_title'],
                        $_POST['seo_description'],
                        $id
                    ]);
                }
                break;
                
            case 'delete':
                $id = $_POST['post_id'];
                $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
                $stmt->execute([$id]);
                break;
        }
    }
}

// Fetch all blog posts
$stmt = $conn->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="blog-container">
    <h1>Manage Blog Posts</h1>
    
    <div class="admin-card">
        <h2>Add New Blog Post</h2>
        <form method="POST" enctype="multipart/form-data" class="dynamic-form">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="title_en">Title (English)</label>
                <input type="text" id="title_en" name="title_en" required>
            </div>
            
            <div class="form-group">
                <label for="title_ru">Title (Russian)</label>
                <input type="text" id="title_ru" name="title_ru" required>
            </div>
            
            <div class="form-group">
                <label for="content_en">Content (English)</label>
                <textarea id="content_en" name="content_en" class="rich-editor" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="content_ru">Content (Russian)</label>
                <textarea id="content_ru" name="content_ru" class="rich-editor" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Featured Image</label>
                <input type="file" id="image" name="image" accept="image/*" required class="image-upload" data-preview="image-preview">
                <img id="image-preview" src="#" alt="Preview" style="display: none; max-width: 200px; margin-top: 10px;">
            </div>
            
            <div class="form-group">
                <label for="seo_title">SEO Title</label>
                <input type="text" id="seo_title" name="seo_title" required>
            </div>
            
            <div class="form-group">
                <label for="seo_description">SEO Description</label>
                <textarea id="seo_description" name="seo_description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="ai_prompt">AI Content Generation</label>
                <textarea id="ai_prompt" placeholder="Enter your prompt for AI content generation"></textarea>
                <button type="button" id="ai-generate" class="btn-secondary">Generate Content</button>
            </div>
            
            <button type="submit" class="btn-primary">Add Blog Post</button>
        </form>
    </div>
    
    <div class="admin-card">
        <h2>Existing Blog Posts</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title (EN)</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td><img src="uploads/blog/<?php echo $post['image']; ?>" alt="<?php echo $post['title_en']; ?>" style="width: 50px;"></td>
                    <td><?php echo $post['title_en']; ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></td>
                    <td>
                        <button onclick="editPost(<?php echo $post['id']; ?>)" class="btn-primary">Edit</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Initialize rich text editor
document.querySelectorAll('.rich-editor').forEach(editor => {
    ClassicEditor
        .create(editor)
        .catch(error => {
            console.error(error);
        });
});

function editPost(id) {
    // Fetch post data and populate edit form
    fetch(`api/blog.php?id=${id}`)
        .then(response => response.json())
        .then(post => {
            // Populate edit form
            document.getElementById('edit-form').style.display = 'block';
            // ... populate form fields
        });
}
</script>

<?php include 'includes/footer.php'; ?>