<?php
session_start();
require_once 'config.php';

function redirectWithError($type, $message, $activeForm){
    $_SESSION[$type . '_error'] = $message;
    $_SESSION['active_form']    = $activeForm;
    header('Location: signup.php');
    exit;
}

if (isset($_POST['register'])) {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $role === '') {
        redirectWithError('register', 'Please fill in all fields.', 'register');
    }

    // check email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        redirectWithError('register', 'Email is already registered.', 'register');
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hash, $role);

    if ($stmt->execute()) {
        $_SESSION['user'] = [
            'id'    => $stmt->insert_id,
            'name'  => $name,
            'email' => $email,
            'role'  => $role
        ];
        $stmt->close();

        if ($role === 'admin') {
            header('Location: admin_page.php');
        } else {
            header('Location: user_page.php');
        }
        exit;
    } else {
        $stmt->close();
        redirectWithError('register', 'Registration failed. Try again.', 'register');
    }

} elseif (isset($_POST['login'])) {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        redirectWithError('login', 'Please enter email and password.', 'login');
    }

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {

            $_SESSION['user'] = [
                'id'    => $row['id'],
                'name'  => $row['name'],
                'email' => $row['email'],
                'role'  => $row['role']
            ];

            $stmt->close();

            if ($row['role'] === 'admin') {
                header('Location: admin_page.php');
            } else {
                header('Location: user_page.php');
            }
            exit;
        } else {
            $stmt->close();
            redirectWithError('login', 'Wrong password.', 'login');
        }
    } else {
        $stmt->close();
        redirectWithError('login', 'Account not found.', 'login');
    }

} else {
    header('Location: signup.php');
    exit;
}
