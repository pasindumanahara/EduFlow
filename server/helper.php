<?php
// helpers.php
function generateNextCode(PDO $pdo, string $table, string $codeColumn, string $prefix, int $width = 4) {
    // example: prefix 'STU', width 4 -> STU0001
    $sql = "SELECT $codeColumn FROM $table WHERE $codeColumn LIKE :pattern ORDER BY $codeColumn DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pattern' => $prefix . '%']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $next = 1;
    } else {
        $lastCode = $row[$codeColumn];
        // strip prefix
        $numPart = substr($lastCode, strlen($prefix));
        $numPart = intval($numPart);
        $next = $numPart + 1;
    }
    $number = str_pad((string)$next, $width, '0', STR_PAD_LEFT);
    return $prefix . $number;
}
