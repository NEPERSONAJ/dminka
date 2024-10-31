<?php
include 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

// Fetch all games for the dropdown
$stmt = $conn->query("SELECT id, name_en FROM games ORDER BY name_en");
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $gameId = $_POST['game_id'];
                $nameEn = $_POST['name_en'];
                $nameRu = $_POST['name_ru'];
                $type = $_POST['type']; // legendary or epic
                
                // Handle image upload
                $targetDir = "uploads/heroes/";
                $fileName = time() . basename($_FILES["image"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                    $stmt = $conn->prepare("INSERT INTO heroes (game_id, name_en, name_ru, type, image, seo_title, seo_description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $gameId, 
                        $nameEn, 
                        $nameRu, 
                        $type, 
                        $fileName,
                        $_POST['seo_title'],
                        $_POST['seo_description']
                    ]);
                }
                break;
                
            case 'edit':
                $id = $_POST['hero_id'];
                $gameId = $_POST['game_id'];
                $nameEn = $_POST['name_en'];
                $nameRu = $_POST['name_ru'];
                $type = $_POST['type'];
                
                if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                    $targetDir = "uploads/heroes/";
                    $fileName = time() . basename($_FILES["image"]["name"]);
                    $targetFilePath = $targetDir . $fileName;
                    
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                        $stmt = $conn->prepare("UPDATE heroes SET game_id = ?, name_en = ?, name_ru = ?, type = ?, image = ?, seo_title = ?, seo_description = ? WHERE id = ?");
                        $stmt->execute([
                            $gameId, 
                            $nameEn, 
                            $nameRu, 
                            $type, 
                            $fileName,
                            $_POST['seo_title'],
                            $_POST['seo_description'],
                            $id
                        ]);
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE heroes SET game_id = ?, name_en = ?, name_ru = ?, type = ?, seo_title = ?, seo_description = ? WHERE id = ?");
                    $stmt->execute([
                        $gameId, 
                        $nameEn, 
                        $nameRu, 
                        $type,
                        $_POST['seo_title'],
                        $_POST['seo_description'],
                        $id
                    ]);
                }
                break;
                
            case 'delete':
                $id = $_POST['hero_id'];
                $stmt = $conn->prepare("DELETE FROM heroes WHERE id = ?");
                $stmt->execute([$id]);
                break;
        }
    }
}

// Fetch all heroes with game names
$stmt = $conn->query("
    SELECT h.*, g.name_en as game_name 
    FROM heroes h 
    JOIN games g ON h.game_id = g.id 
    ORDER BY g.name_en, h.name_en
");
$heroes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="heroes-container">
    <h1>Manage Heroes</h1>
    
    <div class="admin-card">
        <h2>Add New Hero</h2>
        <form method="POST" enctype="multipart/form-data" class="dynamic-form">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="game_id">Game</label>
                <select id="game_id" name="game_id" required>
                    <option value="">Select Game</option>
                    <?php foreach ($games as $game): ?>
                        <option value="<?php echo $game['id']; ?>"><?php echo $game['name_en']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="name_en">Name (English)</label>
                <input type="text" id="name_en" name="name_en" required>
            </div>
            
            <div class="form-group">
                <label for="name_ru">Name (Russian)</label>
                <input type="text" id="name_ru" name="name_ru" required>
            </div>
            
            <div class="form-group">
                <label for="type">Hero Type</label>
                <select id="type" name="type" required>
                    <option value="legendary">Legendary</option>
                    <option value="epic">Epic</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Hero Image</label>
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
            
            <button type="submit" class="btn-primary">Add Hero</button>
        </form>
    </div>
    
    <div class="admin-card">
        <h2>Existing Heroes</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Game</th>
                    <th>Name (EN)</th>
                    <th>Name (RU)</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($heroes as $hero): ?>
                <tr>
                    <td><img src="uploads/heroes/<?php echo $hero['image']; ?>" alt="<?php echo $hero['name_en']; ?>" style="width: 50px;"></td>
                    <td><?php echo $hero['game_name']; ?></td>
                    <td><?php echo $hero['name_en']; ?></td>
                    <td><?php echo $hero['name_ru']; ?></td>
                    <td><?php echo ucfirst($hero['type']); ?></td>
                    <td>
                        <button onclick="editHero(<?php echo $hero['id']; ?>)" class="btn-primary">Edit</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="hero_id" value="<?php echo $hero['id']; ?>">
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
function editHero(id) {
    // Fetch hero data and populate edit form
    fetch(`api/heroes.php?id=${id}`)
        .then(response => response.json())
        .then(hero => {
            // Populate edit form
            document.getElementById('edit-form').style.display = 'block';
            document.getElementById('edit-hero-id').value = hero.id;
            document.getElementById('edit-game-id').value = hero.game_id;
            document.getElementById('edit-name-en').value = hero.name_en;
            document.getElementById('edit-name-ru').value = hero.name_ru;
            document.getElementById('edit-type').value = hero.type;
            document.getElementById('current-image').src = `uploads/heroes/${hero.image}`;
        });
}
</script>

<?php include 'includes/footer.php'; ?>