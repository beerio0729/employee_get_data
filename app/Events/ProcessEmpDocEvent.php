<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ProcessEmpDocEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public string $message;
    public $user;
    public $modal_status;
    public $file_name;
    public $success;

    /**
     * Create a new event instance.
     *
     * @param string $message ข้อความที่ต้องการส่งไปให้ผู้ใช้
     * @param mixed $user อ็อบเจกต์ผู้ใช้
     * @param string|null $modal_status รหัสข้อผิดพลาด (Optional)
     * @return void
     */
    public function __construct(
        ?string $message = null, 
        $user, 
        ?string $modal_status = "open",
        ?string $file_name = null,
        ?bool $success = null
        )
    {
        $this->message = $message;
        $this->user = $user;
        $this->modal_status = $modal_status;
        $this->file_name = $file_name;
        $this->success = $success;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel
    {
        //dump($this->user->id);
        //dump($this->message);
        return new PrivateChannel('user.' . $this->user->id);
        //return ['test-channel'];
    }

    public function broadcastWith(): array
    {   //dump($this->modal_status);
        return [
            'message' => $this->message,
            'modal_status' => $this->modal_status,
            'slug' => $this->file_name,
            'success' => $this->success,
        ];
    }

    public function broadcastAs(): string
    {   //dump('เรียกbroadcastAs');
        return 'ProcessEmpDocEvent';
    }
}
