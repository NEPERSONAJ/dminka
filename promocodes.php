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
                $descriptionEn = $_POST['description_en'];
                $descriptionRu = $_POST['description_ru'];
                $code = $_POST['code'];
                $expiryDate = $_POST['expiry_date'];
                
                // Handle image upload
                $targetDir = "uploads/promocodes/";
                $fileName = time() . basename($_FILES["image"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                    $stmt = $conn->prepare("
                        INSERT INTO promocodes (
                            title_en, title_ru, description_en, description_ru,
                            code, image, expiry_date, seo_title, seo_description,
                            created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $titleEn,
                        $titleRu,
                        $descriptionEn,
                        $descriptionRu,
                        $code,
                        $fileName,
                        $expiryDate,
                        $_POST['seo_title'],
                        $_POST['seo_description']
                    ]);
                }
                break;
                
            case 'edit':
                // Similar to add but with UPDATE query
                break;
                
            case 'delete':
                $id = $_POST['promocode_id'];
                $stmt = $conn->prepare("DELETE FROM promocodes WHERE id = ?");
                $stmt->execute([$id]);
                break;
        }
    }
}

// Fetch all promocodes
$stmt = $conn->query("SELECT * FROM promocodes ORDER BY created_at DESC");
$promocodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="promocodes-container">
    <h1>Manage Promocodes</h1>
    
    <div class="admin-card">
        <h2>Add New Promocode</h2>
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
                <label for="description_en">Description (English)</label>
                <textarea id="description_en" name="description_en" class="rich-editor" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="description_ru">Description (Russian)</label>
                <textarea id="description_ru" name="description_ru" class="rich-editor" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="code">Promocode</label>
                <input type="text" id="code" name="code" required>
            </div>
            
            <div class="form-group">
                <label for="expiry_date">Expiry Date</label>
                <input type="datetime-local" id="expiry_date" name="expiry_date" required>
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
            
            <button type="submit" class="btn-primary">Add Promocode</button>
        </form>
    </div>
    
    <div class="admin-card">
        <h2>Existing Promocodes</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title (EN)</th>
                    <th>Code</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promocodes as $promo): ?>
                <tr>
                    <td><img src="uploads/promocodes/<?php echo $promo['image']; ?>" alt="<?php echo $promo['title_en']; ?>" style="width: 50px;"></td>
                    <td><?php echo $promo['title_en']; ?></td>
                    <td><?php echo $promo['code']; ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($promo['expiry_date'])); ?></td>
                    <td>
                        <button onclick="editPromocode(<?php echo $promo['id']; ?>)" class="btn-primary">Edit</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="promocode_id" value="<?php echo $promo['id']; ?>">
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

function editPromocode(id) {
    // Fetch promocode data and populate edit form
    fetch(`api/promocodes.php?id=${id}`)
        .then(response => response.json())
        .then(promo => {
            // Populate edit form
            document.getElementById('edit-form').style.display = 'block';
            // ... populate form fields
        });
}
</script>

<?php include 'includes/footer.php'; ?>