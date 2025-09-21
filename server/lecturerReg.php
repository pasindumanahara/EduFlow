<?php
    // lecturerReg.php
    require_once 'db.php';
    require_once 'helpers.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }

    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $contact = trim($_POST['contactInfo'] ?? '');
    $nic = trim($_POST['nicNumber'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = $_POST['gender_select'] ?? '';
    $username = trim($_POST['username'] ?? '');  // if you include username/password in lecturer form
    $password = $_POST['password'] ?? '';

    if (!$fname || !$lname || !$contact || !$nic || !$gender) {
        exit('Required fields missing.');
    }

    // If your lecturer form doesn't include login creation, you can create users later
    try {
        $pdo->beginTransaction();

        // if username/password provided -> create user account
        if ($username && $password) {
            $pwHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:u, :p, 'lecturer')");
            $stmt->execute([':u' => $username, ':p' => $pwHash]);
            $userId = (int)$pdo->lastInsertId();
        } else {
            // create a user row with randomly generated username? Or set to NULL and require admin to create account
            throw new Exception("Lecturer registration requires username and password (recommended).");
        }

        // map gender
        $stmt = $pdo->prepare("SELECT gender_id FROM genders WHERE gender_name = :g");
        $stmt->execute([':g' => $gender]);
        $g = $stmt->fetchColumn();
        if (!$g) throw new Exception("Invalid gender selected.");

        // generate lec code
        $lecCode = generateNextCode($pdo, 'lecturers', 'lec_id', 'LEC', 4);

        // insert lecturer
        $stmt = $pdo->prepare("INSERT INTO lecturers (lec_id, user_id, fname, lname, contact, nic, email, address, gender_id)
            VALUES (:lec, :uid, :fn, :ln, :ct, :nic, :em, :addr, :gid)");
        $stmt->execute([
            ':lec' => $lecCode,
            ':uid' => $userId,
            ':fn' => $fname,
            ':ln' => $lname,
            ':ct' => $contact,
            ':nic' => $nic,
            ':em' => $email,
            ':addr' => $address,
            ':gid' => $g
        ]);

        $pdo->commit();
        header('Location: login.php?registered=1');
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        exit('Lecturer registration failed: ' . htmlspecialchars($e->getMessage()));
    }
?>

