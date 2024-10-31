<?php
include 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nameEn = $_POST['name_en'];
                $nameRu = $_POST['name_ru'];
                $description = $_POST['description'];
                
                // Handle image upload
                $targetDir = "uploads/games/";
                $fileName = time() . basename($_FILES["image"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                    $stmt = $conn->prepare("INSERT INTO games (name_en, name_ru, description, image) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nameEn, $nameRu, $description, $fileName]);
                }
                break;
                
            case 'edit':
                // Similar to add but with UPDATE query
                break;
                
            case 'delete':
                $id = $_POST['game_id'];
                $stmt = $conn->prepare("DELETE FROM games WHERE id = ?");
                $stmt->execute([$id]);
                break;
        }
    }
}

// Fetch all games
$stmt = $conn->query("SELECT * FROM games ORDER BY name_en");
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="games-container">
    <h1>Manage Games</h1>
    
    <div class="admin-card">
        <h2>Add New Game</h2>
        <form method="POST" enctype="multipart/form-data" class="dynamic-form">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="name_en">Name (English)</label>
                <input type="text" id="name_en" name="name_en" required>
            </div>
            
            <div class="form-group">
                <label for="name_ru">Name (Russian)</label>
                <input type="text" id="name_ru" name="name_ru" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Game Image</label>
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
            
            <button type="submit" class="btn-primary">Add Game</button>
        </form>
    </div>
    
    <div class="admin-card">
        <h2>Existing Games</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name (EN)</th>
                    <th>Name (RU)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                <tr>
                    <td><img src="uploads/games/<?php echo $game['image']; ?>" alt="<?php echo $game['name_en']; ?>" style="width: 50px;"></td>
                    <td><?php echo $game['name_en']; ?></td>
                    <td><?php echo $game['name_ru']; ?></td>
                    <td>
                        <button onclick="editGame(<?php echo $game['id']; ?>)" class="btn-primary">Edit</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                            <button type="submit" class="btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>