<?php
$pageTitle = 'Contact Finder';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Validate URL params
if (!isset($_GET['item_id']) || !is_numeric($_GET['item_id']) || !isset($_GET['type']) || !in_array($_GET['type'], ['lost', 'found'])) {
    header('Location: index.php');
    exit;
}

$itemId = (int)$_GET['item_id'];
$itemType = $_GET['type'];

// Connect to DB
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// --- Get item details ---
$tableName = $itemType . '_items'; // lost_items or found_items

$sql = "SELECT i.*, u.id AS user_id, u.full_name, u.email, u.phone, u.department, 
               d.name AS department_name, c.name AS category_name 
        FROM `$tableName` AS i
        JOIN users AS u ON i.user_id = u.id
        JOIN departments AS d ON u.department = d.id
        JOIN categories AS c ON i.category_id = c.id
        WHERE i.id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<pre><b>SQL ERROR:</b> " . $conn->error . "</pre><pre><b>QUERY:</b> " . htmlspecialchars($sql) . "</pre>");
}
$stmt->bind_param('i', $itemId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$item = $result->fetch_assoc();

// --- Handle form submission ---
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');

    if (empty($message)) {
        $errors[] = 'Message is required.';
    }

    // Handle proof image upload
    $proofImage = '';
    if (!empty($_FILES['proof_image']['name'])) {
        $uploadResult = uploadImage($_FILES['proof_image'], 'proof');
        if (isset($uploadResult['error'])) {
            $errors[] = $uploadResult['error'];
        } else {
            $proofImage = $uploadResult['filename'];
        }
    }

    if (empty($errors)) {
        $currentUser = getCurrentUser();
        if (!$currentUser || !isset($currentUser['id'])) {
            $errors[] = 'You must be logged in to send a message.';
        } else {
            // ✅ Corrected SQL (includes user_id and status)
            $insertSql = "INSERT INTO contact_messages 
                (sender_id, receiver_id, user_id, item_id, item_type, message, proof_image, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

            $insertStmt = $conn->prepare($insertSql);
            if (!$insertStmt) {
                $errors[] = 'Database error: ' . $conn->error;
            } else {
                $insertStmt->bind_param(
                    'iiiisss',
                    $currentUser['id'],   // sender_id
                    $item['user_id'],     // receiver_id
                    $currentUser['id'],   // user_id (who initiated the contact)
                    $itemId,              // item_id
                    $itemType,            // item_type
                    $message,             // message
                    $proofImage           // proof_image
                );

                if ($insertStmt->execute()) {
                    $success = true;
                } else {
                    $errors[] = 'Failed to send message. Please try again.';
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item">
                        <a href="<?php echo $itemType; ?>_item.php?id=<?php echo $itemId; ?>">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Contact</li>
                </ol>
            </nav>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-paper-plane me-2"></i>Contact About <?php echo ucfirst($itemType); ?> Item
                    </h4>
                </div>

                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>Your message has been sent successfully!
                        </div>
                        <div class="text-center mt-4">
                            <a href="<?php echo $itemType; ?>_item.php?id=<?php echo $itemId; ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Item
                            </a>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <?php if (!empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php else: ?>
                                    <div class="no-image-placeholder d-flex align-items-center justify-content-center bg-light rounded" style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <h5 class="border-bottom pb-2 mb-3"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong><i class="fas fa-tag me-2"></i>Category:</strong> <?php echo htmlspecialchars($item['category_name']); ?></li>
                                    <li class="mb-2">
                                        <strong><i class="fas fa-calendar-alt me-2"></i><?php echo $itemType === 'lost' ? 'Date Lost:' : 'Date Found:'; ?></strong>
                                        <?php echo formatDate($itemType === 'lost' ? $item['date_lost'] : $item['date_found']); ?>
                                    </li>
                                    <li class="mb-2"><strong><i class="fas fa-map-marker-alt me-2"></i>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></li>
                                    <li class="mb-2"><strong><i class="fas fa-user me-2"></i>Reported by:</strong> <?php echo htmlspecialchars($item['full_name']); ?></li>
                                </ul>
                            </div>
                        </div>

                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Introduce yourself and explain why you believe this item belongs to you."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="proof_image" class="form-label">Upload Proof (Optional)</label>
                                <input type="file" class="form-control" id="proof_image" name="proof_image" accept="image/*">
                                <div class="form-text">Upload an image that proves your ownership.</div>
                                <div id="image-preview" class="mt-2 d-none">
                                    <img src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?php echo $itemType; ?>_item.php?id=<?php echo $itemId; ?>" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Contact Information
                    </h5>
                </div>
                <div class="card-body">
                    <p>You can also contact the person directly using the following information:</p>
                    <ul class="list-unstyled contact-info">
                        <li class="mb-3"><i class="fas fa-phone fa-fw me-2"></i><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($item['phone']); ?>"><?php echo htmlspecialchars($item['phone']); ?></a></li>
                        <li class="mb-3"><i class="fas fa-envelope fa-fw me-2"></i><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($item['email']); ?>"><?php echo htmlspecialchars($item['email']); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('proof_image').addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        const previewImg = preview.querySelector('img');

        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('d-none');
            }
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.classList.add('d-none');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
<?php $conn->close(); ?>