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
                    <span class="position">{{ $position }}</span>
                    @if($position === 'ผู้สมัครงาน')
                    <span class="status status-warning">สถานะ : กำลังพิจารณา</span>
                    @else
                    <span class="status status-success">ตำแหน่ง : Network Engineer</span>
                    @endif
                </div>
            </div>
        </div>
    </x-filament::section>

    <style>
        .employee-header {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .employee-avatar {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
            background: #F3F4F6;
        }

        .employee-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .employee-name {
            font-size: 20px;
            font-weight: 600;
            color: var(--mycolor-900);
        }

        .employee-position {
            font-size: 16px;
            font-weight: 500;
        }

        .position {
            color: var(--mycolor-500);
        }
        .status {
            margin-left: 5px;
            padding: 1px 10px;
            border-radius: 8px;
        }
        
        .status-success {
            font-size: 12px;
            background-color: var(--success-100);
            color: var(--success-800);
        }

        .status-warning {
            font-size: 12px;
            background-color: var(--warning-100);
            color: var(--warning-800);
        }
    </style>
</x-filament-widgets::widget>