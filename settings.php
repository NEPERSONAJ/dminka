<?php
include 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_admin':
                $currentPassword = $_POST['current_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];
                
                // Verify current password
                $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($currentPassword, $admin['password'])) {
                    if ($newPassword === $confirmPassword) {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                        $stmt->execute([$hashedPassword, $_SESSION['admin_id']]);
                        $success = "Password updated successfully";
                    } else {
                        $error = "New passwords do not match";
                    }
                } else {
                    $error = "Current password is incorrect";
                }
                break;
                
            case 'update_telegram':
                $botToken = $_POST['bot_token'];
                $chatId = $_POST['chat_id'];
                
                $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE name = 'telegram_bot_token'");
                $stmt->execute([$botToken]);
                
                $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE name = 'telegram_chat_id'");
                $stmt->execute([$chatId]);
                
                $success = "Telegram settings updated successfully";
                break;
                
            case 'update_ai':
                $apiKey = $_POST['api_key'];
                $apiEndpoint = $_POST['api_endpoint'];
                
                $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE name = 'ai_api_key'");
                $stmt->execute([$apiKey]);
                
                $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE name = 'ai_api_endpoint'");
                $stmt->execute([$apiEndpoint]);
                
                $success = "AI settings updated successfully";
                break;
        }
    }
}

// Fetch current settings
$stmt = $conn->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['name']] = $row['value'];
}
?>

<div class="settings-container">
    <h1>Settings</h1>
    
    <?php if (isset($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="admin-card">
        <h2>Admin Password</h2>
        <form method="POST" class="dynamic-form">
            <input type="hidden" name="action" value="update_admin">
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn-primary">Update Password</button>
        </form>
    </div>
    
    <div class="admin-card">
        <h2>Telegram Settings</h2>
        <form method="POST" class="dynamic-form">
            <input type="hidden" name="action" value="update_telegram">
            
            <div class="form-group">
                <label for="bot_token">Bot Token</label>
                <input type="text" id="bot_token" name="bot_token" value="<?php echo htmlspecialchars($settings['telegram_bot_token'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="chat_id">Chat ID</label>
                <input type="text" id="chat_id" name="chat_id" value="<?php echo htmlspecialchars($settings['telegram_chat_id'] ?? ''); ?>" required>
            </div>
            
            <button type="button" onclick="testTelegramBot()" class="btn-secondary">Test Bot</button>
            <button type="submit" class="btn-primary">Save Settings</button>
        </form>
    </div>
    
    <div class="admin-card">
        <h2>AI Settings</h2>
        <form method="POST" class="dynamic-form">
            <input type="hidden" name="action" value="update_ai">
            
            <div class="form-group">
                <label for="api_key">API Key</label>
                <input type="text" id="api_key" name="api_key" value="<?php echo htmlspecialchars($settings['ai_api_key'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="api_endpoint">API Endpoint</label>
                <input type="url" id="api_endpoint" name="api_endpoint" value="<?php echo htmlspecialchars($settings['ai_api_endpoint'] ?? ''); ?>" required>
            </div>
            
            <button type="submit" class="btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>