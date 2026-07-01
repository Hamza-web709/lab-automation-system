<?php
require_once __DIR__ . '/../config/app.php';

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $path = trim($path, '/');
    $base = APP_BASE_PATH ?: '';

    return $path === '' ? ($base . '/') : ($base . '/' . $path);
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['_csrf_token'] ?? '', $token);
}

function require_csrf(): void
{
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        set_flash('error', 'Your session token expired. Please try again.');
        redirect('auth/login.php');
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);

    return $messages;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'tester',
        'status' => $_SESSION['user_status'] ?? 'active',
    ];
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function require_role(array $roles): void
{
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        set_flash('error', 'You do not have permission to access that page.');
        redirect('admin/dashboard.php');
    }
}

function formatDate(?string $date, string $format = 'd M Y'): string
{
    if (!$date) {
        return '-';
    }

    return date($format, strtotime($date));
}

function humanize(?string $value): string
{
    return ucwords(str_replace('_', ' ', (string) $value));
}

function statusBadge(?string $status): string
{
    $status = $status ?: 'pending';
    $classMap = [
        'active' => 'success',
        'inactive' => 'neutral',
        'manufactured' => 'info',
        'under_testing' => 'warning',
        'passed_internal' => 'success',
        'failed_internal' => 'danger',
        'sent_to_cpri' => 'primary',
        'approved' => 'success',
        'sent_for_remaking' => 'danger',
        'pending' => 'warning',
        'in_progress' => 'info',
        'completed' => 'success',
        'sent_to_next_department' => 'primary',
    ];
    $class = $classMap[$status] ?? 'neutral';

    return '<span class="badge badge-' . e($class) . '">' . e(humanize($status)) . '</span>';
}

function resultBadge(?string $result): string
{
    $result = $result ?: 'pending';
    $class = ['pass' => 'success', 'fail' => 'danger', 'pending' => 'warning'][$result] ?? 'neutral';

    return '<span class="badge badge-' . e($class) . '">' . e(humanize($result)) . '</span>';
}

function optionSelected(mixed $actual, mixed $expected): string
{
    return (string) $actual === (string) $expected ? 'selected' : '';
}

function optionChecked(mixed $actual, mixed $expected): string
{
    return (string) $actual === (string) $expected ? 'checked' : '';
}

function normalizeCodePart(string $value, int $maxLength): string
{
    $clean = preg_replace('/[^A-Z0-9]/', '', strtoupper($value));

    return substr($clean ?: '0', 0, $maxLength);
}

function normalizeUsername(string $username): string
{
    return trim($username);
}

function isValidUsername(string $username): bool
{
    return (bool) preg_match('/^[a-zA-Z0-9_.]{3,30}$/', $username);
}

function usernameValidationMessage(): string
{
    return 'Username can only contain letters, numbers, underscore, and dot. Length must be 3 to 30 characters.';
}

function valueExists(PDO $pdo, string $table, string $column, string $value, ?int $ignoreId = null): bool
{
    $allowed = [
        'products' => ['product_id'],
        'testing_records' => ['test_id'],
        'users' => ['email', 'username'],
        'product_types' => ['code'],
        'testing_departments' => ['code'],
    ];

    if (!isset($allowed[$table]) || !in_array($column, $allowed[$table], true)) {
        throw new InvalidArgumentException('Unsafe uniqueness lookup.');
    }

    $sql = "SELECT id FROM {$table} WHERE {$column} = :value";
    $params = ['value' => $value];

    if ($ignoreId) {
        $sql .= ' AND id <> :id';
        $params['id'] = $ignoreId;
    }

    $stmt = $pdo->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);

    return (bool) $stmt->fetchColumn();
}

function generateProductId(PDO $pdo, string $productTypeCode, string $reviseNo, string $manufacturingNo): string
{
    // Product IDs are normalized into a fixed 10-character code derived from type, revision, and manufacturing number.
    $base = normalizeCodePart($productTypeCode, 3)
        . normalizeCodePart($reviseNo, 2)
        . normalizeCodePart($manufacturingNo, 5);
    $base = str_pad(substr($base, 0, 10), 10, '0');
    $candidate = $base;
    $sequence = 1;

    // If the natural code already exists, reserve the suffix for a safe sequence.
    while (valueExists($pdo, 'products', 'product_id', $candidate)) {
        $suffix = str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
        $candidate = substr($base, 0, 10 - strlen($suffix)) . $suffix;
        $sequence++;
    }

    return $candidate;
}

function generateTestId(PDO $pdo, string $productCode, string $reviseNo, string $testingCode, string $rollNo): string
{
    // Test IDs combine product code, revision, testing code, and roll number into a unique 12-character code.
    $base = normalizeCodePart($productCode, 3)
        . normalizeCodePart($reviseNo, 2)
        . normalizeCodePart($testingCode, 4)
        . normalizeCodePart($rollNo, 3);
    $base = str_pad(substr($base, 0, 12), 12, '0');
    $candidate = $base;
    $sequence = 1;

    while (valueExists($pdo, 'testing_records', 'test_id', $candidate)) {
        $suffix = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
        $candidate = substr($base, 0, 12 - strlen($suffix)) . $suffix;
        $sequence++;
    }

    return $candidate;
}

function syncProductStatusAfterTest(PDO $pdo, int $productId, string $result, string $status, string $nextAction): void
{
    // Product workflow state follows the latest test decision so dashboard counts stay meaningful.
    $productStatus = 'under_testing';

    if ($status === 'sent_to_cpri' || $nextAction === 'send_to_cpri') {
        $productStatus = 'sent_to_cpri';
    } elseif ($status === 'sent_for_remaking' || $nextAction === 'send_for_remaking') {
        $productStatus = 'sent_for_remaking';
    } elseif ($result === 'pass' && $status === 'completed') {
        $productStatus = 'passed_internal';
    } elseif ($result === 'fail') {
        $productStatus = 'failed_internal';
    }

    $stmt = $pdo->prepare('UPDATE products SET current_status = :status WHERE id = :id');
    $stmt->execute(['status' => $productStatus, 'id' => $productId]);
}

function logActivity(PDO $pdo, ?int $userId, string $action, string $description = ''): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO activity_logs (user_id, action, description) VALUES (:user_id, :action, :description)'
    );
    $stmt->execute([
        'user_id' => $userId,
        'action' => $action,
        'description' => $description,
    ]);
}

function tableCount(PDO $pdo, string $table, string $where = '1=1', array $params = []): int
{
    $allowed = ['products', 'testing_records', 'users'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Unsafe count table.');
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$where}");
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}

function fetchTestRecordDetails(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT tr.*, p.product_id, p.product_name, p.product_code, p.revise_no, p.manufacturing_no,
                p.batch_no, p.manufacturing_date, p.description AS product_description,
                pt.name AS product_type_name, td.name AS department_name, tt.name AS testing_type_name,
                u.name AS created_by_name, assigned.name AS assigned_tester_name
         FROM testing_records tr
         INNER JOIN products p ON p.id = tr.product_id_ref
         INNER JOIN product_types pt ON pt.id = p.product_type_id
         INNER JOIN testing_departments td ON td.id = tr.department_id
         INNER JOIN testing_types tt ON tt.id = tr.testing_type_id
         LEFT JOIN users u ON u.id = tr.created_by
         LEFT JOIN users assigned ON assigned.id = tr.assigned_tester_id
         WHERE tr.id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $record = $stmt->fetch();

    return $record ?: null;
}

function fetchTestPersons(PDO $pdo, int $testingRecordId): array
{
    $stmt = $pdo->prepare('SELECT * FROM test_persons WHERE testing_record_id = :id ORDER BY id');
    $stmt->execute(['id' => $testingRecordId]);

    return $stmt->fetchAll();
}

function saveTestPersons(PDO $pdo, int $testingRecordId, array $names, array $designations, array $remarks): void
{
    // Tester person rows are stored separately so one testing record can keep multiple named contributors.
    $stmt = $pdo->prepare(
        'INSERT INTO test_persons (testing_record_id, person_name, designation, remarks)
         VALUES (:testing_record_id, :person_name, :designation, :remarks)'
    );

    foreach ($names as $index => $name) {
        $personName = trim((string) $name);
        if ($personName === '') {
            continue;
        }

        $stmt->execute([
            'testing_record_id' => $testingRecordId,
            'person_name' => $personName,
            'designation' => trim((string) ($designations[$index] ?? '')),
            'remarks' => trim((string) ($remarks[$index] ?? '')),
        ]);
    }
}
