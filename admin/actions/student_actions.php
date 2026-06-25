<?php
require_once '../../config.php';

if (!is_admin_logged_in()) {
    redirect('../../login.php?role=admin');
    exit;
}

$student_model = new Student();

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_student_crud'])) {
    $data = [
        'name' => sanitize_input($_POST['name']),
        'email' => sanitize_input($_POST['email']),
        'phone' => sanitize_input($_POST['phone']),
        'address' => sanitize_input($_POST['address']),
    ];

    if (empty($data['name']) || empty($data['email'])) {
        $_SESSION['error'] = 'Name and email are required';
    } else {
        $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : '';

        if ($student_id) {
            // Update
            if ($student_model->update($student_id, $data)) {
                $_SESSION['success'] = 'Student updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update student';
            }
        } else {
            // Create
            $data['password'] = 'student123'; // Default password
            if ($student_model->create($data)) {
                $_SESSION['success'] = 'Student added successfully with default password "student123"';
            } else {
                $_SESSION['error'] = 'Failed to add student';
            }
        }
    }
    redirect('../manage_students.php');
    exit;
}

// Handle delete
if (isset($_POST['delete_student'])) {
    $student_id = $_POST['delete_student'];
    if ($student_model->delete($student_id)) {
        $_SESSION['success'] = 'Student deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete student';
    }
    redirect('../manage_students.php');
    exit;
}

// Redirect back if accessed directly without a POST request
redirect('../manage_students.php');
