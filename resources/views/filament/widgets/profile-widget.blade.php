<x-filament-widgets::widget>
    <x-filament::section>
        <div class="employee-header">
            <img
                src="{{ asset('storage/'.$image) }}"
                alt="Profile Image"
                class="employee-avatar">

            <div class="employee-info">
                <div class="employee-name">
                    {{ $name }} {{ $last_name }}
                </div>

                <div class="employee-position">
                    {{ $position }}
                </div>

                @if($position === 'ผู้สมัครงาน')
                <div class="status">สถานะ : 
                <span class="status-container status-warning">กำลังพิจารณา</span>
                </div>
                @else
                <div class="status">ตำแหน่ง : 
                <span class="status-container status-success">Network Engineer</span>
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
            color: var(--mycolor-500);
            /*line-height: 1.1;*/
        }

        .employee-position {
            font-size: 14px;
            font-weight: 500;
            color: var(--mycolor-500);
        }
        
        .status {
            display: flex;
            align-items: center;    
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            color: var(--mycolor-500);
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
    </style>
</x-filament-widgets::widget>