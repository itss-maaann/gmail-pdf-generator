<?php

namespace App\Services\Pdf;

class HtmlConversationBuilder
{
    public function build(string $from, string $to, array $emails, string $conversationId): string
    {
        $startTime = microtime(true);
        info("🛠️ HTML conversation build started for Conversation ID: {$conversationId}");

        $html = "<h1 style='text-align:center;'>Gmail Conversation between {$from} and {$to}</h1><hr>";

        foreach ($emails as $email) {
            $subject = e($email['subject'] ?? 'No Subject');
            $timestamp = date('Y-m-d H:i:s', $email['timestamp'] ?? time());
            $body = nl2br(e($email['body'] ?? 'No Body'));

            $html .= <<<HTML
                <div style="padding: 20px; margin-bottom: 30px; border: 1px solid #ccc; background: #fdfdfd;">
                    <h3><strong>Subject:</strong> {$subject}</h3>
                    <p><strong>Date:</strong> {$timestamp}</p>
                    <div style="margin-top:10px; font-size:14px; line-height:1.7; background:#f9f9f9; padding:15px;">
                        {$body}
                    </div>
                </div>
            HTML;
        }

        logExecutionStats("✅ HTML conversation build completed for Conversation ID: {$conversationId}", $startTime);

        return $html;
    }
}
