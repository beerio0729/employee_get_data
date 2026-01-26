<?php

namespace App\Jobs;

use Illuminate\Support\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\PreEmployment\InterviewService;

class BulkInterviewJob implements ShouldQueue
{
    use Queueable;

    protected $data;
    protected $records;
    protected $action;

    public function __construct(Collection $records, array $data = [], string $action = '')
    {
        $this->data = $data;
        $this->records = $records;
        $this->action = $action;
    }

    public function handle(): void
    {
        if (blank($this->action)) {
            $this->fail();
        }

        if ($this->action === "create") {
            $this->createInterview();
        }
        
        if ($this->action === "update") {
            $this->updateInterview();
        }

        if ($this->action === "delete") {
            $this->deleteInterview();
        }
    }

    public function createInterview()
    {
        foreach ($this->records as $index => $record) {
            $data_interview = $this->data['multiform_interview'][$index];

            $interviewService = new InterviewService();
            $interviewService->create($record, $data_interview);
        }
    }
    
    public function updateInterview()
    {
        foreach ($this->records as $index => $record) {
            $data_interview = $this->data['multiform_interview'][$index];

            $interviewService = new InterviewService();
            $interviewService->update($record, $data_interview);
        }
    }

    public function deleteInterview()
    {
        foreach ($this->records as $record) {
            $interviewService = new InterviewService();
            $interviewService->delete($record);
        }
    }
}
