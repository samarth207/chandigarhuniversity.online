<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['Status' => 'Error', 'Message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/db-config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['Status' => 'Error', 'Message' => 'Invalid JSON input']);
    exit;
}

// Sanitize and validate
$name = trim($input['StudentName'] ?? $input['FullName'] ?? '');
$email = trim($input['StudentEmail'] ?? $input['Email'] ?? '');
$mobile = trim($input['StudentMobile'] ?? $input['Mobile'] ?? '');
$program = trim($input['StudentProgram'] ?? $input['ProgramInterested'] ?? '');
$source = trim($input['StudentSource'] ?? $input['Source'] ?? 'Website');
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$countryCode = trim($input['StudentCountryCode'] ?? '+91');
$city = trim($input['CityName'] ?? '');
$param1 = trim($input['mx_Param1'] ?? '');
$param2 = trim($input['mx_Param2'] ?? '');
$param3 = trim($input['mx_Param3'] ?? '');
$page = trim($input['Page'] ?? $_SERVER['HTTP_REFERER'] ?? '');

// Validation
if (empty($name) || strlen($name) < 2 || strlen($name) > 100) {
    http_response_code(400);
    echo json_encode(['Status' => 'Error', 'Message' => 'Valid name is required']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['Status' => 'Error', 'Message' => 'Valid email is required']);
    exit;
}

if (empty($mobile) || !preg_match('/^[0-9]{7,15}$/', $mobile)) {
    http_response_code(400);
    echo json_encode(['Status' => 'Error', 'Message' => 'Valid mobile number is required']);
    exit;
}

if (empty($program)) {
    http_response_code(400);
    echo json_encode(['Status' => 'Error', 'Message' => 'Program selection is required']);
    exit;
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare('INSERT INTO leads (student_name, student_email, student_mobile, student_program, student_source, student_ip, country_code, city, param1, param2, param3, page, created_at) VALUES (:name, :email, :mobile, :program, :source, :ip, :country_code, :city, :param1, :param2, :param3, :page, NOW())');

    $stmt->execute([
        ':name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        ':email' => $email,
        ':mobile' => $mobile,
        ':program' => htmlspecialchars($program, ENT_QUOTES, 'UTF-8'),
        ':source' => htmlspecialchars($source, ENT_QUOTES, 'UTF-8'),
        ':ip' => $ip,
        ':country_code' => htmlspecialchars($countryCode, ENT_QUOTES, 'UTF-8'),
        ':city' => htmlspecialchars($city, ENT_QUOTES, 'UTF-8'),
        ':param1' => htmlspecialchars($param1, ENT_QUOTES, 'UTF-8'),
        ':param2' => htmlspecialchars($param2, ENT_QUOTES, 'UTF-8'),
        ':param3' => htmlspecialchars($param3, ENT_QUOTES, 'UTF-8'),
        ':page' => htmlspecialchars($page, ENT_QUOTES, 'UTF-8')
    ]);

    echo json_encode(['Status' => 'Success', 'Message' => 'Registration successful']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['Status' => 'Error', 'Message' => 'Failed to save data']);
}
