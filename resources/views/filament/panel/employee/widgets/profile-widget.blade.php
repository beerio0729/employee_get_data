@php
$span = 'span';
$pre_status_detail = $pre_employment->first()->preEmploymentBelongToWorkStatusDefinationDetail; //ไปตารางสถานะย่อย
$pre_state = $pre_status_detail->workStatusDefinationDetailBelongsToWorkStatusDefination; //ไปตารางสถานะหลัก
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="profile-header">
            <img
                src="{{Storage::disk('public')->url($image)}}"
                alt="Profile Image"
                class="profile-avatar">

            <div class="profile-info">
                <div class="profile-name">
                    {{ $name }} {{ $last_name ?? 'ไม่มีช้อมูลชื่อ - นามสกุล'}}
                </div>
                @if ($post_employment->exists())
                <div class="profile-position">
                    แผนก
                </div>
                <div class="status">ตำแหน่ง :
                    <span class="status-container status-primary">Network Engineer</span>
                </div>
                @else
                <div class="profile-position">
                    {{$pre_state->name_th}}
                </div>
                <div class="status">สถานะ :
                    <{!! $span !!} class="status-container {{$pre_status_detail->color}}">
                        {{$pre_status_detail->name_th}}
                    </{!! $span !!}>
                </div>
                @endif
            </div>
        </div>
    </x-filament::section>

    <style>
        .profile-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-avatar {
            width: 65px;
            height: 65px;
            border-radius: 10px;
            object-fit: cover;
            background: #F3F4F6;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .profile-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-900);
            /*line-height: 1.1;*/
        }

        .profile-position {
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
        
        .primary {
            background-color: var(--primary-100);
            color: var(--primary-800);
        }
        
        .success {
            background-color: var(--success-100);
            color: var(--success-800);
        }

        .warning {
            background-color: var(--warning-100);
            color: var(--warning-800);
        }
        
        .danger {
            background-color: var(--danger-100);
            color: var(--danger-800);
        }
    </style>
</x-filament-widgets::widget>