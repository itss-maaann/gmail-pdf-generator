<?php

namespace App\Services\Gmail;

use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class EmailContentService
{
    protected string $sourcePath;

    public function __construct()
    {
        $this->sourcePath = storage_path('app/content.pdf');
    }

    public function extractAllConversations(): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($this->sourcePath);
        $text = $pdf->getText();

        $text = strip_tags($text);
        $text = preg_replace('/[^\P{C}\x00-\x7F]+/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        $sentences = preg_split('/(?<=[.?!])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count($sentences);

        $requiredChunks = 70;
        $sentencesPerEmail = 5;

        $repeatedSentences = [];
        while (count($repeatedSentences) < ($requiredChunks * $sentencesPerEmail)) {
            $repeatedSentences = array_merge($repeatedSentences, $sentences);
        }

        $conversationChunks = array_chunk(array_slice($repeatedSentences, 0, $requiredChunks * $sentencesPerEmail), $sentencesPerEmail);

        $conversations = [];

        for ($i = 0; $i < 35; $i++) {
            $msgChunk = $conversationChunks[$i * 2];
            $replyChunk = $conversationChunks[$i * 2 + 1];

            $message = implode(PHP_EOL . PHP_EOL, $msgChunk);
            $reply = implode(PHP_EOL . PHP_EOL, $replyChunk);

            $message = Str::repeat($message . PHP_EOL . PHP_EOL, 1400);
            $reply = Str::repeat($reply . PHP_EOL . PHP_EOL, 1400);

            $conversations[$i + 1] = [e($message), e($reply)];
        }

        return $conversations;
    }
}
