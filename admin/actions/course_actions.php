<?php
require_once '../../config.php';

if (!is_admin_logged_in()) {
    redirect('../../login.php?role=admin');
    exit;
}

$course_model = new Course();

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_crud'])) {
    $data = [
        'course_name' => sanitize_input($_POST['course_name']),
        'course_code' => sanitize_input($_POST['course_code']),
        'description' => sanitize_input($_POST['description']),
        'duration' => sanitize_input($_POST['duration']),
    ];

    if (empty($data['course_name']) || empty($data['course_code'])) {
        $_SESSION['error'] = 'Course name and code are required';
    } else {
        $course_id = isset($_POST['course_id']) ? $_POST['course_id'] : '';

        if ($course_id) {
            // Update
            if ($course_model->update($course_id, $data)) {
                $_SESSION['success'] = 'Course updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update course';
            }
        } else {
            // Create
            if ($course_model->create($data)) {
                $_SESSION['success'] = 'Course added successfully';
            } else {
                $_SESSION['error'] = 'Failed to add course';
            }
        }
    }
    redirect('../manage_courses.php');
    exit;
}

// Handle delete
if (isset($_POST['delete_course'])) {
    $course_id = $_POST['delete_course'];
    if ($course_model->delete($course_id)) {
        $_SESSION['success'] = 'Course deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete course';
    }
    redirect('../manage_courses.php');
    exit;
}

// Redirect back if accessed directly
redirect('../manage_courses.php');
