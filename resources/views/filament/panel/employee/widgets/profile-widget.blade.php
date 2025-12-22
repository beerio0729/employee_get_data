@php
$span = 'span';
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="employee-header">
            <img
                src="{{ asset('storage/'.$image) }}"
                alt="Profile Image"
                class="employee-avatar">

            <div class="employee-info">
                <div class="employee-name">
                    {{ $name }} {{ $last_name ?? 'ไม่มีช้อมูลชื่อ - นามสกุล'}}
                </div>
                @if ($employee->exists())
                <div class="employee-position">
                    แผนก
                </div>
                <div class="status">ตำแหน่ง :
                    <span class="status-container status-success">Network Engineer</span>
                </div>
                @else
                <div class="employee-position">
                    ผู้สมัครงาน
                </div>
                <div class="status">สถานะ :
                    <{!! $span !!} class="status-container
                        @if($applicant->first()->status === "passed") status-success"
                        @elseif ($applicant->first()->status === "rejected") status-danger"
                        @else status-warning"
                        @endif
                    >
                        @php
                        $status = $applicant->first()->status;
                        @endphp
                        {{ config("iconf.applicant_status.$status") }}
                    </{!! $span !!}>
                </div>
                @endif
            </div>
        </div>
    </x-filament::section>

    <style>
        .employee-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .employee-avatar {
            width: 65px;
            height: 65px;
            border-radius: 10px;
            object-fit: cover;
            background: #F3F4F6;
        }

        .employee-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .employee-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-900);
            /*line-height: 1.1;*/
        }

        .employee-position {
            font-size: 14px;
            font-weight: 500;
            color: var(--primary-700);
        }

        .status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            color: var(--primary-600);
        }

        .status-container {
            padding: 1px 5px;
            border-radius: 5px;
            font-size: 10px;
        }

        .status-success {
            background-color: var(--success-100);
            color: var(--success-800);
        }

        .status-warning {
            background-color: var(--warning-100);
            color: var(--warning-800);
        }
        
        .status-danger {
            background-color: var(--danger-100);
            color: var(--danger-800);
        }
    </style>
</x-filament-widgets::widget>