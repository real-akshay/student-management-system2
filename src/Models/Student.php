<?php

class Student {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all students with optional pagination
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAll($page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM students ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Search for students by name or email with pagination
     * @param string $term
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function search($term, $page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $searchTerm = "%{$term}%";
        $stmt = $this->db->prepare("SELECT * FROM students WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get the total number of students for a search term
     * @param string $term
     * @return int
     */
    public function getSearchTotalCount($term) {
        $searchTerm = "%{$term}%";
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM students WHERE name LIKE ? OR email LIKE ?");
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    }


    /**
     * Get the total number of students
     * @return int
     */
    public function getTotalCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM students");
        return $result->fetch_assoc()['count'];
    }

    /**
     * Find a student by their ID
     * @param int $id
     * @return array|null
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Create a new student
     * @param array $data
     * @return string|false The new student ID or false on failure
     */
    public function create($data) {
        $id = uniqid(); // Or use a UUID library
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO students (id, name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $id, $data['name'], $data['email'], $data['phone'], $password_hash);
        if ($stmt->execute()) {
            return $id;
        }
        return false;
    }

    /**
     * Update a student's details
     * @param string $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE students SET name = ?, email = ?, phone = ?";
        $params = [$data['name'], $data['email'], $data['phone']];
        $types = "sss";

        if (!empty($data['password'])) {
            $sql .= ", password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            $types .= "s";
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;
        $types .= "s";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    /**
     * Delete a student by their ID
     * @param string $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }
}
