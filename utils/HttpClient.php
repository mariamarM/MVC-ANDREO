<?php

class Comment extends Model
{
    private $http;

    private function sendCommentCreatedWebhook($postId, $text)
    {
        $url = getenv('N8N_WEBHOOK_COMMENT_CREATED');
        $token = getenv('N8N_SHARED_TOKEN');
        $this->http->post($url, [
            'headers' => ['X-Shared-Token' => $token],
            'json' => [
                'post_id' => $postId,
                'text' => $text,
                'user_id' => $_SESSION['user_id']
            ]
        ]);
    }
    public function create($postId, $text): mixed
    {
        $sql = "INSERT INTO comments (post_id, user_id, text) 
                VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$postId, $_SESSION['user_id'], $text]);
        if ($result) {
            $this->sendCommentCreatedWebhook($postId, $text);
        }
        return $result;
    }
    private function sendCommentDeletedWebhook($commentId)
    {
        $url = getenv('N8N_WEBHOOK_COMMENT_DELETED');
        $token = getenv('N8N_SHARED_TOKEN');
        $this->http->post($url, [
            'headers' => ['X-Shared-Token' => $token],
            'json' => ['comment_id' => $commentId]
        ]);
    }
}
?>