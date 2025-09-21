<?php
// update_contact.php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    http_response_code(403);
    exit('Not logged in.');
}

if ($_SESSION['role'] !== 'student') {
    http_response_code(403);
    exit('Only students can update contact info here.');
}

$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!$contact || !$email) {
        exit('Contact and email are required.');
    }

    try {
        $stmt = $pdo->prepare("UPDATE students SET contact = :ct, email = :em, address = :addr WHERE user_id = :uid");
        $stmt->execute([':ct' => $contact, ':em' => $email, ':addr' => $address, ':uid' => $userId]);
        echo 'Contact info updated.';
    } catch (Exception $e) {
        exit('Update failed: ' . htmlspecialchars($e->getMessage()));
    }
} else {
    // show current values
    $stmt = $pdo->prepare("SELECT contact, email, address FROM students WHERE user_id = :uid LIMIT 1");
    $stmt->execute([':uid' => $userId]);
    $row = $stmt->fetch();
    if (!$row) exit('Student record not found.');

    ?>
    <form method="post">
      <input name="contact" value="<?=htmlspecialchars($row['contact'])?>" required>
      <input name="email" value="<?=htmlspecialchars($row['email'])?>" required>
      <textarea name="address"><?=htmlspecialchars($row['address'])?></textarea>
      <button type="submit">Save</button>
    </form>
    <?php
}
