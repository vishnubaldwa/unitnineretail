<?php
/**
 * Admin API Endpoint (AJAX handler)
 */
define('UNR_ADMIN', true);
session_start();

header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/cpanel_api.php';

$cpanel = new CpanelAPI(
    $config['cpanel_host'],
    $config['cpanel_port'],
    $config['cpanel_user'],
    $config['cpanel_token']
);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Helper to respond with JSON
function json_resp($status, $data = null, $error = null) {
    echo json_encode([
        'success' => (bool)$status,
        'data'    => $data,
        'error'   => $error
    ]);
    exit;
}

// 1. Login Action
if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $configuredUser = $config['admin_user'] ?? 'admin';
    $configuredPass = $config['admin_pass'] ?? 'UnitNine@2026';

    $passMatch = false;
    if (password_verify($password, $configuredPass)) {
        $passMatch = true;
    } elseif ($password === $configuredPass) {
        $passMatch = true;
    }

    if ($username === $configuredUser && $passMatch) {
        $_SESSION['unr_admin_logged'] = true;
        $_SESSION['unr_admin_time'] = time();
        json_resp(true, ['message' => 'Login successful']);
    } else {
        json_resp(false, null, 'Invalid username or password');
    }
}

// 2. Logout Action
if ($action === 'logout') {
    $_SESSION['unr_admin_logged'] = false;
    session_destroy();
    json_resp(true, ['message' => 'Logged out successfully']);
}

// Ensure user is logged in for all subsequent actions
if (empty($_SESSION['unr_admin_logged'])) {
    http_response_code(401);
    json_resp(false, null, 'Unauthorized. Please log in.');
}

// 3. List Accounts
if ($action === 'list') {
    $res = $cpanel->listAccounts();
    if ($res['status'] == 1) {
        $accounts = [];
        $domain = $config['domain'];
        $empFile = __DIR__ . '/employees.json';
        $employees = file_exists($empFile) ? json_decode(file_get_contents($empFile), true) : [];
        if (!is_array($employees)) {
            $employees = [];
        }

        foreach (($res['data'] ?? []) as $row) {
            $email = $row['email'];
            $accounts[] = [
                'email'           => $email,
                'name'            => $employees[$email]['name'] ?? '',
                'login'           => $row['login'],
                'domain'          => $row['domain'],
                'diskused'        => $row['diskused'],
                'diskusedpercent' => $row['diskusedpercent'],
                'diskquota'       => $row['diskquota'],
                '_diskquota'      => $row['_diskquota'],
                'suspended_in'    => !empty($row['suspended_incoming']),
                'suspended_out'   => !empty($row['suspended_outgoing']),
            ];
        }
        json_resp(true, $accounts);
    } else {
        $err = !empty($res['errors']) ? implode(', ', $res['errors']) : 'Failed to fetch accounts from cPanel';
        json_resp(false, null, $err);
    }
}

// 4. Create Account
if ($action === 'create') {
    $fullName    = trim($_POST['name'] ?? '');
    $emailPrefix = strtolower(trim($_POST['email'] ?? ''));
    $password    = $_POST['password'] ?? '';
    $quota       = intval($_POST['quota'] ?? 1024); // default 1024 MB
    $domain      = $config['domain'];

    // Sanitization: remove @unitnineretail.com if typed
    if (strpos($emailPrefix, '@') !== false) {
        $parts = explode('@', $emailPrefix);
        $emailPrefix = $parts[0];
    }

    if (!preg_match('/^[a-z0-9._-]+$/', $emailPrefix)) {
        json_resp(false, null, 'Email can only contain lowercase letters, numbers, dots, and hyphens.');
    }

    if (strlen($password) < 8) {
        json_resp(false, null, 'Password must be at least 8 characters long.');
    }

    $res = $cpanel->createAccount($emailPrefix, $password, $quota, $domain);
    if ($res['status'] == 1) {
        $fullEmail = "{$emailPrefix}@{$domain}";

        // Store employee metadata
        $empFile = __DIR__ . '/employees.json';
        $employees = file_exists($empFile) ? json_decode(file_get_contents($empFile), true) : [];
        if (!is_array($employees)) {
            $employees = [];
        }
        $employees[$fullEmail] = [
            'name'       => $fullName,
            'created_at' => date('Y-m-d H:i:s')
        ];
        file_put_contents($empFile, json_encode($employees, JSON_PRETTY_PRINT));

        json_resp(true, [
            'name'     => $fullName,
            'email'    => $fullEmail,
            'password' => $password,
            'quota'    => $quota,
            'webmail'  => "https://{$domain}/"
        ]);
    } else {
        $err = !empty($res['errors']) ? implode(', ', $res['errors']) : 'Failed to create email account.';
        json_resp(false, null, $err);
    }
}

// 5. Update Employee Details (Name)
if ($action === 'update_employee') {
    $email = trim($_POST['email'] ?? '');
    $name  = trim($_POST['name'] ?? '');

    $empFile = __DIR__ . '/employees.json';
    $employees = file_exists($empFile) ? json_decode(file_get_contents($empFile), true) : [];
    if (!is_array($employees)) {
        $employees = [];
    }

    if (!isset($employees[$email])) {
        $employees[$email] = ['created_at' => date('Y-m-d H:i:s')];
    }
    $employees[$email]['name'] = $name;
    file_put_contents($empFile, json_encode($employees, JSON_PRETTY_PRINT));

    json_resp(true, ['message' => 'Employee details updated successfully']);
}

// 6. Delete Account
if ($action === 'delete') {
    $email = trim($_POST['email'] ?? '');
    $parts = explode('@', $email);
    $user = $parts[0];
    $domain = $parts[1] ?? $config['domain'];

    $res = $cpanel->deleteAccount($user, $domain);
    if ($res['status'] == 1) {
        $empFile = __DIR__ . '/employees.json';
        if (file_exists($empFile)) {
            $employees = json_decode(file_get_contents($empFile), true) ?: [];
            if (isset($employees[$email])) {
                unset($employees[$email]);
                file_put_contents($empFile, json_encode($employees, JSON_PRETTY_PRINT));
            }
        }
        json_resp(true, ['message' => "Account {$email} deleted successfully"]);
    } else {
        $err = !empty($res['errors']) ? implode(', ', $res['errors']) : 'Failed to delete account.';
        json_resp(false, null, $err);
    }
}

// 6. Change Password
if ($action === 'change_password') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($password) < 8) {
        json_resp(false, null, 'Password must be at least 8 characters long.');
    }

    $parts = explode('@', $email);
    $user = $parts[0];
    $domain = $parts[1] ?? $config['domain'];

    $res = $cpanel->changePassword($user, $password, $domain);
    if ($res['status'] == 1) {
        json_resp(true, ['message' => "Password changed successfully for {$email}"]);
    } else {
        $err = !empty($res['errors']) ? implode(', ', $res['errors']) : 'Failed to change password.';
        json_resp(false, null, $err);
    }
}

// 7. Change Quota
if ($action === 'change_quota') {
    $email = trim($_POST['email'] ?? '');
    $quota = intval($_POST['quota'] ?? 1024);

    $parts = explode('@', $email);
    $user = $parts[0];
    $domain = $parts[1] ?? $config['domain'];

    $res = $cpanel->changeQuota($user, $quota, $domain);
    if ($res['status'] == 1) {
        json_resp(true, ['message' => "Storage quota updated to {$quota} MB"]);
    } else {
        $err = !empty($res['errors']) ? implode(', ', $res['errors']) : 'Failed to change quota.';
        json_resp(false, null, $err);
    }
}

// 8. Suspend / Unsuspend
if ($action === 'toggle_suspend') {
    $email   = trim($_POST['email'] ?? '');
    $suspend = !empty($_POST['suspend']);

    if ($suspend) {
        $res = $cpanel->suspendAccount($email);
    } else {
        $res = $cpanel->unsuspendAccount($email);
    }

    if ($res['status'] == 1) {
        json_resp(true, ['message' => $suspend ? "Account suspended" : "Account reactivated"]);
    } else {
        json_resp(false, null, 'Failed to update account status.');
    }
}

// 9. Update Admin Settings (Change Admin Password)
if ($action === 'update_admin_pass') {
    $currentPass = $_POST['current_pass'] ?? '';
    $newPass     = $_POST['new_pass'] ?? '';

    $configuredPass = $config['admin_pass'] ?? 'UnitNine@2026';
    $passMatch = (password_verify($currentPass, $configuredPass) || $currentPass === $configuredPass);

    if (!$passMatch) {
        json_resp(false, null, 'Current password is incorrect.');
    }

    if (strlen($newPass) < 8) {
        json_resp(false, null, 'New password must be at least 8 characters long.');
    }

    $config['admin_pass'] = password_hash($newPass, PASSWORD_DEFAULT);
    file_put_contents(__DIR__ . '/config.json', json_encode($config, JSON_PRETTY_PRINT));

    json_resp(true, ['message' => 'Admin password updated successfully!']);
}

json_resp(false, null, 'Unknown action');
