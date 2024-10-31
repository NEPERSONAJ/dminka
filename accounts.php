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
                $titleEn = $_POST['title_en'];
                $titleRu = $_POST['title_ru'];
                $price = $_POST['price'];
                $resources = $_POST['resources'];
                $heroes = isset($_POST['heroes']) ? json_encode($_POST['heroes']) : '[]';
                
                // Handle images upload
                $images = [];
                if (isset($_FILES['images'])) {
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $fileName = time() . '_' . $_FILES['images']['name'][$key];
                        $targetPath = "uploads/accounts/" . $fileName;
                        
                        if (move_uploaded_file($tmp_name, $targetPath)) {
                            $images[] = $fileName;
                        }
                    }
                }
                
                $stmt = $conn->prepare("
                    INSERT INTO accounts (
                        game_id, title_en, title_ru, price, resources, heroes, 
                        images, seo_title, seo_description
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $gameId,
                    $titleEn,
                    $titleRu,
                    $price,
                    $resources,
                    $heroes,
                    json_encode($images),
                    $_POST['seo_title'],
                    $_POST['seo_description']
                ]);
                break;
                
            case 'edit':
                // Similar to add but with UPDATE query
                break;
                
            case 'delete':
                $id = $_POST['account_id'];
                $stmt = $conn->prepare("DELETE FROM accounts WHERE id = ?");
                $stmt->execute([$id]);
                break;
        }
    }
}

// Fetch all accounts with game names
$stmt = $conn->query("
    SELECT a.*, g.name_en as game_name 
    FROM accounts a 
    JOIN games g ON a.game_id = g.id 
    ORDER BY g.name_en, a.title_en
");
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="accounts-container">
    <h1>Manage Accounts</h1>
    
    <div class="admin-card">
        <h2>Add New Account</h2>
        <form method="POST" enctype="multipart/form-data" class="dynamic-form">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="game_id">Game</label>
                <select id="game_id" name="game_id" required onchange="loadGameHeroes(this.value)">
                    <option value="">Select Game</option>
                    <?php foreach ($games as $game): ?>
                        <option value="<?php echo $game['id']; ?>"><?php echo $game['name_en']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="title_en">Title (English)</label>
                <input type="text" id="title_en" name="title_en" required>
            </div>
            
            <div class="form-group">
                <label for="title_ru">Title (Russian)</label>
                <input type="text" id="title_ru" name="title_ru" required>
            </div>
            
            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="resources">Resources</label>
                <textarea id="resources" name="resources" required></textarea>
            </div>
            
            <div class="form-group" id="heroes-container" style="display: none;">
                <label>Heroes</label>
                <div id="heroes-list"></div>
            </div>
            
            <div class="form-group">
                <label for="images">Account Images</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple required class="image-upload" data-preview="images-preview">
                <div id="images-preview" class="images-preview-container"></div>
            </div>
            
            <div class="form-group">
                <label for="seo_title">SEO Title</label>
                <input type="text" id="seo_title" name="seo_title" required>
            </div>
            
            <div class="form-group">
                <label for="seo_description">SEO Description</label>
                <textarea id="seo_description" name="seo_description" required></textarea>
            </div>
            
            <button type="submit" class="btn-primary">Add Account</button>
        </form>
    </div>
    
    <div class="admin-card">
        <h2>Existing Accounts</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Images</th>
                    <th>Game</th>
                    <th>Title (EN)</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accounts as $account): ?>
                <tr>
                    <td>
                        <?php 
                        $images = json_decode($account['images'], true);
                        if (!empty($images)) {
                            echo '<img src="uploads/accounts/' . $images[0] . '" alt="' . $account['title_en'] . '" style="width: 50px;">';
                        }
                        ?>
                    </td>
                    <td><?php echo $account['game_name']; ?></td>
                    <td><?php echo $account['title_en']; ?></td>
                    <td>$<?php echo number_format($account['price'], 2); ?></td>
                    <td>
                        <button onclick="editAccount(<?php echo $account['id']; ?>)" class="btn-primary">Edit</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="account_id" value="<?php echo $account['id']; ?>">
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
function loadGameHeroes(gameId) {
    if (!gameId) {
        document.getElementById('heroes-container').style.display = 'none';
        return;
    }
    
    fetch(`api/heroes.php?game_id=${gameId}`)
        .then(response => response.json())
        .then(heroes => {
            const container = document.getElementById('heroes-list');
            container.innerHTML = '';
            
            heroes.forEach(hero => {
                const div = document.createElement('div');
                div.className = 'hero-checkbox';
                div.innerHTML = `
                    <label>
                        <input type="checkbox" name="heroes[]" value="${hero.id}">
                        ${hero.name_en} (${hero.type})
                    </label>
                `;
                container.appendChild(div);
            });
            
            document.getElementById('heroes-container').style.display = 'block';
        });
}

function editAccount(id) {
    // Fetch account data and populate edit form
    fetch(`api/accounts.php?id=${id}`)
        .then(response => response.json())
        .then(account => {
            // Populate edit form
            document.getElementById('edit-form').style.display = 'block';
            // ... populate form fields
        });
}
</script>

<?php include 'includes/footer.php'; ?>