<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    // ส่งข้อความ WhatsApp แบบ hardcode (ทดสอบ)
    public function send()
    {
        // Hardcode เบอร์ผู้รับ
        $phone = '66970950072';

        // WhatsApp API config
        $phoneNumberId = '919057927954152';
        $accessToken = 'EAAQq7FdFCWQBQP2JDreZAcqChZBRqGZANdlnJJT1ZAajcNvEoVOhlWKfjVWjCgAc6SYUhAJO9dBUy5GHTs8gAHqZAhkjVqk5O67BUyijzKwIBSyZBFFAOhJr1fWUiIfpDMkzQmumVoditR0AC1OZCv0x65mx6gr1Ff5ZB6KwaJpnbN5LQgr6BWeyynxlvdyp8y3NASaT8EIFmUKYT0vjZC9E7A9D5IZBkCDfjhvTZB7fmxDecAKBX18DZA2j1g0DaFXb1T8YMZA4onnPZCx9PumlqkzXpBwX8gBvZAaT8syVAZDZD';

        // ส่งข้อความ template
        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v22.0/{$phoneNumberId}/messages", [
                "messaging_product" => "whatsapp",
                "to" => $phone,
                "type" => "template",
                "template" => [
                    "name" => "jaspers_market_plain_text_v1",
                    "language" => ["code" => "en_US"],
                ],
            ]);

        // แสดงผล response JSON
        return $response->json();
    }
}
