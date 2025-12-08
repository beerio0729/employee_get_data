<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LineSendMessageService
{
    public static function send($id, array $messages): void
    {
        $full_message = self::getMessages($messages);
        $response = Http::withToken(env('LINE_CHANNEL_ACCESS_TOKEN'))
            ->post('https://api.line.me/v2/bot/message/push', [
                "to" => $id,
                "messages" => $full_message,
            ]);
    }

    public static function getMessages($messages)
    {
        $full_message = [];
        foreach ($messages as $msg) {
            $full_message[] = [
                "type" => 'text',
                "text" => $msg
            ];
        }
        return $full_message;
    }
}
