<?php
/**
 * Profanity Filter Class
 * Detects and handles profane words in user submissions
 */
class ProfanityFilter {
    private $badWords = [
        // Indonesian profanity words
        'anjing', 'bangsat', 'babi', 'jancuk', 'kontol', 'memek', 'ngentot', 'ngewe', 'perek', 'setan',
        'sialan', 'tolol', 'goblok', 'brengsek', 'kampret', 'asu', 'jembut', 'kimak', 'pecun', 'bencong',
        'bajingan', 'fuck', 'shit', 'damn', 'bitch', 'asshole', 'bastard', 'cunt', 'dick', 'pussy',
        // Add more words as needed
    ];

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Check if text contains profane words
     * @param string $text Text to check
     * @return array Array with 'has_profanity' boolean and 'found_words' array
     */
    public function checkProfanity($text) {
        $text = strtolower($text);
        $foundWords = [];

        foreach ($this->badWords as $word) {
            if (strpos($text, $word) !== false) {
                $foundWords[] = $word;
            }
        }

        return [
            'has_profanity' => !empty($foundWords),
            'found_words' => $foundWords
        ];
    }

    /**
     * Censor profane words with asterisks (per-word censoring)
     * Only censors the profane words, leaving normal words intact
     * @param string $text Text to censor
     * @return string Censored text
     */
    public function censorText($text) {
        $result = $text;

        foreach ($this->badWords as $word) {
            // Use word boundaries to match whole words only, case-insensitive
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            $censored = str_repeat('*', strlen($word));
            $result = preg_replace($pattern, $censored, $result);
        }

        return $result;
    }

    /**
     * Log profanity detection for admin review
     * @param string $originalText Original text
     * @param array $foundWords Found profane words
     * @param int $userId User ID (0 for anonymous)
     * @param string $type Type of content ('post' or 'comment')
     */
    public function logProfanity($originalText, $foundWords, $userId, $type) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO profanity_logs (user_id, content_type, original_text, found_words, detected_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $type,
                $originalText,
                implode(', ', $foundWords)
            ]);

            // Update global profanity count (you can add a separate table for this if needed)
            // For now, we'll track per post
        } catch (Exception $e) {
            // Log to file if database insert fails
            error_log("Profanity log failed: " . $e->getMessage());
        }
    }

    /**
     * Get display text based on user role and post ownership
     * @param string $originalText Original text
     * @param string $censoredText Censored text
     * @param int $postUserId Post owner's user ID
     * @param int $currentUserId Current user's ID
     * @param string $currentUserRole Current user's role
     * @return string Text to display
     */
    public function getDisplayText($originalText, $censoredText, $postUserId, $currentUserId, $currentUserRole) {
        // Admin sees original text
        if ($currentUserRole === 'admin') {
            return $originalText;
        }

        // Post owner sees censored text (can't see their own profanity)
        if ($currentUserId && $postUserId == $currentUserId) {
            return $censoredText ?: $originalText;
        }

        // Other users see censored text
        return $censoredText ?: $originalText;
    }

    /**
     * Get profanity logs for admin dashboard
     * @param int $limit Number of logs to retrieve
     * @return array Profanity logs
     */
    public function getProfanityLogs($limit = 50) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM profanity_logs
                ORDER BY detected_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
