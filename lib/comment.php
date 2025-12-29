<?php
class Comment {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * 添加评论
     */
    public function add($post_id, $user_id, $content) {
        $post_id = (int)$post_id;
        $user_id = (int)$user_id;
        $content = mysqli_real_escape_string($this->conn, $content);
        
        $sql = "INSERT INTO comments (post_id, user_id, content, created_at) 
                VALUES ($post_id, $user_id, '$content', NOW())";
        
        if (mysqli_query($this->conn, $sql)) {
            // 更新微博评论数
            $sql_update = "UPDATE posts SET comments_count = comments_count + 1 WHERE id = $post_id";
            mysqli_query($this->conn, $sql_update);
            
            return [
                'success' => true,
                'comment_id' => mysqli_insert_id($this->conn)
            ];
        }
        return ['success' => false, 'message' => '评论失败'];
    }
    
    /**
     * 获取微博评论
     */
    public function getByPostId($post_id, $page = 1, $limit = 20) {
        $post_id = (int)$post_id;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT c.*, u.username, u.avatar 
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.post_id = $post_id 
                ORDER BY c.created_at ASC 
                LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($this->conn, $sql);
        $comments = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $comments[] = $row;
            }
        }
        return $comments;
    }
    
    /**
     * 删除评论
     */
    public function delete($comment_id, $user_id = null) {
        $comment_id = (int)$comment_id;
        
        // 先获取评论信息（用于更新微博评论数）
        $sql_info = "SELECT post_id FROM comments WHERE id = $comment_id";
        $result = mysqli_query($this->conn, $sql_info);
        $comment = mysqli_fetch_assoc($result);
        
        if (!$comment) {
            return false;
        }
        
        $post_id = $comment['post_id'];
        $where = "id = $comment_id";
        
        if ($user_id) {
            $user_id = (int)$user_id;
            $where .= " AND user_id = $user_id";
        }
        
        $sql = "DELETE FROM comments WHERE $where";
        $success = mysqli_query($this->conn, $sql);
        
        if ($success) {
            // 更新微博评论数
            $sql_update = "UPDATE posts SET comments_count = comments_count - 1 WHERE id = $post_id";
            mysqli_query($this->conn, $sql_update);
        }
        
        return $success;
    }
    
    /**
     * 获取评论总数
     */
    public function getCount($post_id = null) {
        $where = $post_id ? "WHERE post_id = $post_id" : "";
        $sql = "SELECT COUNT(*) as count FROM comments $where";
        
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['count'];
        }
        return 0;
    }
    
    /**
     * 获取单条评论
     */
    public function getById($comment_id) {
        $comment_id = (int)$comment_id;
        $sql = "SELECT c.*, u.username, u.avatar 
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.id = $comment_id";
        
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
}
?>