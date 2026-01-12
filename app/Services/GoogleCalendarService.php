<?php

namespace App\Services;

use Exception;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Carbon; // เปลี่ยนมาใช้ตัวนี้ใน Laravel

class GoogleCalendarService
{
    protected $service;
    protected $calendar_id;

    public function __construct()
    {
        $client = new Client();
        // ตรวจสอบให้แน่ใจว่าไฟล์อยู่ใน storage/app/private/ จริงๆ
        $client->setAuthConfig(storage_path('app/private/service_account_calendar.json'));
        $client->addScope(Calendar::CALENDAR);

        $this->service = new Calendar($client);
        $this->calendar_id = 'iconbeerio@gmail.com';
    }

    // 1. CREATE
    public function createEvent($data)
    {
        try {
            $duration = $data['duration'] ?? 30; // ถ้าไม่ส่งมา ให้ Default ที่ 30 นาที
            $start = Carbon::parse($data['start_time'])->format('Y-m-d\TH:i:s');
            $end = Carbon::parse($data['start_time'])->addMinutes($duration)->format('Y-m-d\TH:i:s');

            $attendees = [];
            if (filled($data['email'])) {
                if (is_array($data['email'])) {
                    foreach ($data['email'] as $email) {
                        if (filled($email)) {
                            $attendees[] = ['email' => $email];
                        }
                    }
                } else {
                    $attendees[] = ['email' => $data['email']];
                }
            }

            // 3. สร้าง Event Object
            $event = new Event([
                'summary'     => $data['title'],
                'description' => $data['description'] ?? '',
                'start'       => [
                    'dateTime' => $start,
                    'timeZone' => 'Asia/Bangkok',
                ],
                'end'         => [
                    'dateTime' => $end,
                    'timeZone' => 'Asia/Bangkok',
                ],
                //'attendees'   => $attendees,
                // 'conferenceData' => [
                //     'createRequest' => [
                //         'requestId' => uniqid(), // สร้าง ID ไม่ซ้ำสำหรับคำขอนี้
                //         'conferenceSolutionKey' => [
                //             'type' => 'hangoutsMeet' // ระบุประเภทการประชุม
                //         ]
                //     ]
                // ],
                "reminders" => [
                    'useDefault' => false,
                    'overrides' => [
                        ["method" => "popup", "minutes" => 10],
                    ],

                ],
            ]);

            // 4. ส่งคำสั่ง Insert ไปที่ Google
            return $this->service->events->insert($this->calendar_id, $event, [
                'conferenceDataVersion' => 1,
            ]);
        } catch (Exception $e) {
            //throw new Exception("Google Calendar Error: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    // 2. READ
    public function getEvent($eventId = null)
    {
        if (blank($eventId)) {
            return;
        }
        return $this->service->events->get($this->calendar_id, $eventId);
    }

    // 3. UPDATE
    public function updateEvent($eventId = null, $data)
    {
        if (blank($eventId)) {
            return;
        }
        
        $duration = $data['duration'] ?? 30; // ถ้าไม่ส่งมา ให้ Default ที่ 30 นาที
        $start = Carbon::parse($data['start_time'])->format('Y-m-d\TH:i:s');
        $end = Carbon::parse($data['start_time'])->addMinutes($duration)->format('Y-m-d\TH:i:s');
        // แก้ไข: ส่งแค่ $eventId ตามโครงสร้างฟังก์ชัน getEvent ด้านบน
        $event = $this->getEvent($eventId);

        $event->setSummary($data['title']);
        $event->setDescription($data['description'] ?? '');

        // แนะนำให้อัปเดตเวลาด้วย หากมีการเปลี่ยนแปลง
        $event_time = new EventDateTime();
        $event_time->setDateTime($start);
        $event_time->setTimeZone('Asia/Bangkok');
        $event->setStart($event_time);

        $event_time->setDateTime($end);
        $event_time->setTimeZone('Asia/Bangkok');
        $event->setEnd($event_time);

        return $this->service->events->update($this->calendar_id, $eventId, $event);
    }

    // 4. DELETE
    public function deleteEvent($eventId = null)
    {
        if (blank($eventId)) {
            return;
        }
        return $this->service->events->delete($this->calendar_id, $eventId);
    }
}
