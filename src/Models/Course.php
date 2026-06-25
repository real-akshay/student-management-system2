<?php

class Course {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all courses with optional pagination
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAll($page = 1, $limit = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM courses LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get the total number of courses
     * @return int
     */
    public function getTotalCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM courses");
        return $result->fetch_assoc()['count'];
    }

    /**
     * Find a course by its ID
     * @param string $id
     * @return array|null
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Create a new course
     * @param array $data
     * @return string|false The new course ID or false on failure
     */
    public function create($data) {
        $id = uniqid(); // Or use a UUID library
        $stmt = $this->db->prepare("INSERT INTO courses (id, course_name, course_code, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $id, $data['course_name'], $data['course_code'], $data['description']);
        if ($stmt->execute()) {
            return $id;
        }
        return false;
    }

    /**
     * Update a course's details
     * @param string $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE courses SET course_name = ?, course_code = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssss", $data['course_name'], $data['course_code'], $data['description'], $id);
        return $stmt->execute();
    }

    /**
     * Delete a course by its ID
     * @param string $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }
}
