<?php
class Follow {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * 关注/取消关注
     */
    public function toggle($follower_id, $following_id) {
        if ($follower_id == $following_id) {
            return ['success' => false, 'message' => '不能关注自己'];
        }
        
        // 检查是否已关注
        $is_following = $this->isFollowing($follower_id, $following_id);
        
        if ($is_following) {
            // 取消关注
            return $this->unfollow($follower_id, $following_id);
        } else {
            // 关注
            return $this->follow($follower_id, $following_id);
        }
    }
    
    /**
     * 关注用户
     */
    public function follow($follower_id, $following_id) {
        $follower_id = (int)$follower_id;
        $following_id = (int)$following_id;
        
        // 检查是否已关注
        if ($this->isFollowing($follower_id, $following_id)) {
            return ['success' => false, 'message' => '已关注该用户'];
        }
        
        $sql = "INSERT INTO follows (follower_id, following_id, created_at) 
                VALUES ($follower_id, $following_id, NOW())";
        
        if (mysqli_query($this->conn, $sql)) {
            return [
                'success' => true,
                'action' => 'follow',
                'message' => '关注成功'
            ];
        }
        
        return ['success' => false, 'message' => '关注失败'];
    }
    
    /**
     * 取消关注
     */
    public function unfollow($follower_id, $following_id) {
        $follower_id = (int)$follower_id;
        $following_id = (int)$following_id;
        
        $sql = "DELETE FROM follows 
                WHERE follower_id = $follower_id AND following_id = $following_id";
        
        if (mysqli_query($this->conn, $sql)) {
            return [
                'success' => true,
                'action' => 'unfollow',
                'message' => '已取消关注'
            ];
        }
        
        return ['success' => false, 'message' => '取消关注失败'];
    }
    
    /**
     * 检查是否已关注
     */
    public function isFollowing($follower_id, $following_id) {
        $follower_id = (int)$follower_id;
        $following_id = (int)$following_id;
        
        $sql = "SELECT id FROM follows 
                WHERE follower_id = $follower_id AND following_id = $following_id";
        
        $result = mysqli_query($this->conn, $sql);
        return mysqli_num_rows($result) > 0;
    }
    
    /**
     * 获取关注列表
     */
    public function getFollowing($user_id, $page = 1, $limit = 20) {
        $user_id = (int)$user_id;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT f.*, u.username, u.avatar, u.bio 
                FROM follows f 
                LEFT JOIN users u ON f.following_id = u.id 
                WHERE f.follower_id = $user_id 
                ORDER BY f.created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($this->conn, $sql);
        $following = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $following[] = $row;
            }
        }
        return $following;
    }
    
    /**
     * 获取粉丝列表
     */
    public function getFollowers($user_id, $page = 1, $limit = 20) {
        $user_id = (int)$user_id;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT f.*, u.username, u.avatar, u.bio 
                FROM follows f 
                LEFT JOIN users u ON f.follower_id = u.id 
                WHERE f.following_id = $user_id 
                ORDER BY f.created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($this->conn, $sql);
        $followers = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $followers[] = $row;
            }
        }
        return $followers;
    }
    
    /**
     * 获取关注数量
     */
    public function getFollowingCount($user_id) {
        $user_id = (int)$user_id;
        $sql = "SELECT COUNT(*) as count FROM follows WHERE follower_id = $user_id";
        
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['count'];
        }
        return 0;
    }
    
    /**
     * 获取粉丝数量
     */
    public function getFollowersCount($user_id) {
        $user_id = (int)$user_id;
        $sql = "SELECT COUNT(*) as count FROM follows WHERE following_id = $user_id";
        
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return $row['count'];
        }
        return 0;
    }
}
?>