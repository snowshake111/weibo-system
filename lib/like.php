<?php
class Like {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * 点赞/取消点赞
     */
    public function toggle($post_id, $user_id) {
        $post_id = (int)$post_id;
        $user_id = (int)$user_id;
        
        // 检查是否已点赞
        $is_liked = $this->isLiked($post_id, $user_id);
        
        if ($is_liked) {
            // 取消点赞
            return $this->unlike($post_id, $user_id);
        } else {
            // 点赞
            return $this->like($post_id, $user_id);
        }
    }
    
    /**
     * 点赞
     */
    public function like($post_id, $user_id) {
        $post_id = (int)$post_id;
        $user_id = (int)$user_id;
        
        $sql = "INSERT INTO likes (post_id, user_id, created_at) 
                VALUES ($post_id, $user_id, NOW())";
        
        if (mysqli_query($this->conn, $sql)) {
            // 更新微博点赞数
            $this->updateLikesCount($post_id, 1);
            
            return [
                'success' => true,
                'action' => 'like',
                'likes_count' => $this->getLikesCount($post_id)
            ];
        }
        
        return ['success' => false, 'message' => '点赞失败'];
    }
    
    /**
     * 取消点赞
     */
    public function unlike($post_id, $user_id) {
        $post_id = (int)$post_id;
        $user_id = (int)$user_id;
        
        $sql = "DELETE FROM likes WHERE post_id = $post_id AND user_id = $user_id";
        
        if (mysqli_query($this->conn, $sql)) {
            // 更新微博点赞数
            $this->updateLikesCount($post_id, -1);
            
            return [
                'success' => true,
                'action' => 'unlike',
                'likes_count' => $this->getLikesCount($post_id)
            ];
        }
        
        return ['success' => false, 'message' => '取消点赞失败'];
    }
    
    /**
     * 检查是否已点赞
     */
    public function isLiked($post_id, $user_id) {
        $post_id = (int)$post_id;
        $user_id = (int)$user_id;
        
        $sql = "SELECT id FROM likes WHERE post_id = $post_id AND user_id = $user_id";
        $result = mysqli_query($this->conn, $sql);
        
        return mysqli_num_rows($result) > 0;
    }
    
    /**
     * 获取微博点赞数
     */
    public function getLikesCount($post_id) {
        $post_id = (int)$post_id;
        $sql = "SELECT COUNT(*) as count FROM likes WHERE post_id = $post_id";
        
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['count'];
        }
        return 0;
    }
    
    /**
     * 获取点赞用户列表
     */
    public function getLikes($post_id, $limit = 20) {
        $post_id = (int)$post_id;
        $sql = "SELECT l.*, u.username, u.avatar 
                FROM likes l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.post_id = $post_id 
                ORDER BY l.created_at DESC 
                LIMIT $limit";
        
        $result = mysqli_query($this->conn, $sql);
        $likes = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $likes[] = $row;
            }
        }
        return $likes;
    }
    
    /**
     * 更新微博点赞数
     */
    private function updateLikesCount($post_id, $change) {
        $post_id = (int)$post_id;
        
        if ($change > 0) {
            $sql = "UPDATE posts SET likes_count = likes_count + 1 WHERE id = $post_id";
        } else {
            $sql = "UPDATE posts SET likes_count = likes_count - 1 WHERE id = $post_id";
        }
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * 获取用户点赞的微博
     */
    public function getUserLikes($user_id, $page = 1, $limit = 10) {
        $user_id = (int)$user_id;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT p.*, u.username, u.avatar 
                FROM likes l 
                LEFT JOIN posts p ON l.post_id = p.id 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE l.user_id = $user_id 
                ORDER BY l.created_at DESC 
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
}
?>