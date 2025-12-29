<?php
class Post {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * 创建微博
     */
    public function create($user_id, $content) {
        $content = mysqli_real_escape_string($this->conn, $content);
        $sql = "INSERT INTO posts (user_id, content, created_at) 
                VALUES ($user_id, '$content', NOW())";
        
        if (mysqli_query($this->conn, $sql)) {
            return [
                'success' => true,
                'post_id' => mysqli_insert_id($this->conn)
            ];
        }
        return ['success' => false, 'message' => '发布失败'];
    }
    
    /**
     * 获取单条微博
     */
    public function getById($post_id) {
        $post_id = (int)$post_id;
        $sql = "SELECT p.*, u.username, u.avatar 
                FROM posts p 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.id = $post_id";
        
        $result = mysqli_query($this->conn, $sql);
        if ($result && $post = mysqli_fetch_assoc($result)) {
            return $post;
        }
        return null;
    }
    
    /**
     * 获取微博列表
     */
    public function getList($page = 1, $limit = 10, $user_id = null) {
        $offset = ($page - 1) * $limit;
        $where = $user_id ? "WHERE p.user_id = $user_id" : "";
        
        $sql = "SELECT p.*, u.username, u.avatar 
                FROM posts p 
                LEFT JOIN users u ON p.user_id = u.id 
                $where
                ORDER BY p.created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($this->conn, $sql);
        $posts = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
    
    /**
     * 删除微博
     */
    public function delete($post_id, $user_id = null) {
        $post_id = (int)$post_id;
        $where = "id = $post_id";
        
        if ($user_id) {
            $user_id = (int)$user_id;
            $where .= " AND user_id = $user_id";
        }
        
        $sql = "DELETE FROM posts WHERE $where";
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * 搜索微博
     */
    public function search($keyword, $page = 1, $limit = 10) {
        $keyword = mysqli_real_escape_string($this->conn, $keyword);
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT p.*, u.username, u.avatar 
                FROM posts p 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.content LIKE '%$keyword%' 
                ORDER BY p.created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($this->conn, $sql);
        $posts = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
    
    /**
     * 获取微博总数
     */
    public function getCount($user_id = null) {
        $where = $user_id ? "WHERE user_id = $user_id" : "";
        $sql = "SELECT COUNT(*) as count FROM posts $where";
        
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['count'];
        }
        return 0;
    }
    
    /**
     * 更新微博
     */
    public function update($post_id, $content, $user_id = null) {
        $post_id = (int)$post_id;
        $content = mysqli_real_escape_string($this->conn, $content);
        $where = "id = $post_id";
        
        if ($user_id) {
            $user_id = (int)$user_id;
            $where .= " AND user_id = $user_id";
        }
        
        $sql = "UPDATE posts SET content = '$content', updated_at = NOW() WHERE $where";
        return mysqli_query($this->conn, $sql);
    }
}
?>