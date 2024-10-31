<?php
include 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

// Get statistics
$stmt = $conn->query("SELECT COUNT(*) as total_games FROM games");
$gamesCount = $stmt->fetch(PDO::FETCH_ASSOC)['total_games'];

$stmt = $conn->query("SELECT COUNT(*) as total_accounts FROM accounts");
$accountsCount = $stmt->fetch(PDO::FETCH_ASSOC)['total_accounts'];

$stmt = $conn->query("SELECT COUNT(*) as pending_reviews FROM reviews WHERE status = 'pending'");
$pendingReviews = $stmt->fetch(PDO::FETCH_ASSOC)['pending_reviews'];
?>

<div class="dashboard-container">
    <h1>Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Games</h3>
            <p class="stat-number"><?php echo $gamesCount; ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Total Accounts</h3>
            <p class="stat-number"><?php echo $accountsCount; ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Pending Reviews</h3>
            <p class="stat-number"><?php echo $pendingReviews; ?></p>
        </div>
    </div>
    
    <div class="recent-activity">
        <h2>Recent Activity</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 10");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>{$row['action']}</td>";
                    echo "<td>{$row['description']}</td>";
                    echo "<td>{$row['created_at']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>