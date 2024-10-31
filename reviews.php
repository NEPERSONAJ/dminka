<?php
include 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'approve':
                $id = $_POST['review_id'];
                $stmt = $conn->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
                $stmt->execute([$id]);
                break;
                
            case 'reject':
                $id = $_POST['review_id'];
                $stmt = $conn->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$id]);
                break;
                
            case 'delete':
                $id = $_POST['review_id'];
                $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
                $stmt->execute([$id]);
                break;
        }
    }
}

// Fetch all reviews
$stmt = $conn->query("
    SELECT r.*, a.title_en as account_title 
    FROM reviews r 
    LEFT JOIN accounts a ON r.account_id = a.id 
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="reviews-container">
    <h1>Manage Reviews</h1>
    
    <div class="admin-card">
        <h2>Pending Reviews</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($reviews as $review):
                    if ($review['status'] === 'pending'):
                ?>
                <tr>
                    <td><?php echo $review['account_title']; ?></td>
                    <td><?php echo $review['rating']; ?>/5</td>
                    <td><?php echo htmlspecialchars($review['content']); ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($review['created_at'])); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                            <button type="submit" class="btn-success">Approve</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                            <button type="submit" class="btn-danger">Reject</button>
                        </form>
                    </td>
                </tr>
                <?php 
                    endif;
                endforeach; 
                ?>
            </tbody>
        </table>
    </div>
    
    <div class="admin-card">
        <h2>Approved Reviews</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($reviews as $review):
                    if ($review['status'] === 'approved'):
                ?>
                <tr>
                    <td><?php echo $review['account_title']; ?></td>
                    <td><?php echo $review['rating']; ?>/5</td>
                    <td><?php echo htmlspecialchars($review['content']); ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($review['created_at'])); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                            <button type="submit" class="btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php 
                    endif;
                endforeach; 
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>