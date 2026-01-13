@php

$span = 'span';
$a = 'a';
$pre_emp = $work_status->first()?->workStatusHasonePreEmp;
$work_status_def_detail = $work_status->first()?->workStatusBelongToWorkStatusDefDetail; //ไปตารางสถานะย่อย
$work_status_def = $work_status_def_detail?->workStatusDefDetailBelongsToWorkStatusDef; //ไปตารางสถานะหลัก
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="profile-header">
            <img
                src="{{Storage::disk('public')->url($image)}}"
                alt="Profile Image"
                class="profile-avatar">

            <div class="con_flex">
                <div class="profile-name">
                    {{ $name }} {{ $last_name ?? 'ไม่มีช้อมูลชื่อ - นามสกุล'}}
                </div>
                @if ($isPostEmp)
                <div class="profile-position">
                    แผนก
                </div>
                <div class="status">ตำแหน่ง :
                    <span class="status-container status-primary">Network Engineer</span>
                </div>
                @else
                <div class="profile-position">
                    {{$work_status_def?->name_th}}
                </div>
                <div class="status">สถานะ :
                    <{!! $span !!} class="status-container {{$work_status_def_detail?->color}}">
                        {{$work_status_def_detail?->name_th}}
                    </{!! $span !!}>
                </div>
                @endif
            </div>
            @if(filled($meeting_link))
            <div class="con_flex interview">
                <{!! $a !!} href="{{$meeting_link}}" target="_blank">
                    <svg fill="none" width="50" xmlns="http://www.w3.org/2000/svg" viewBox="-13.1265 -18 113.763 108">
                        <path fill="#00832d" d="M49.5 36l8.53 9.75 11.47 7.33 2-17.02-2-16.64-11.69 6.44z" />
                        <path fill="#0066da" d="M0 51.5V66c0 3.315 2.685 6 6 6h14.5l3-10.96-3-9.54-9.95-3z" />
                        <path fill="#e94235" d="M20.5 0L0 20.5l10.55 3 9.95-3 2.95-9.41z" />
                        <path fill="#2684fc" d="M20.5 20.5H0v31h20.5z" />
                        <path fill="#00ac47" d="M82.6 8.68L69.5 19.42v33.66l13.16 10.79c1.97 1.54 4.85.135 4.85-2.37V11c0-2.535-2.945-3.925-4.91-2.32zM49.5 36v15.5h-29V72h43c3.315 0 6-2.685 6-6V53.08z" />
                        <path fill="#ffba00" d="M63.5 0h-43v20.5h29V36l20-16.57V6c0-3.315-2.685-6-6-6z" />
                    </svg>
                </{!! $a !!}>
            </div>
            @endif
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

        .con_flex {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .interview {
            flex: 1;
            /* กินพื้นที่ที่เหลือทั้งหมด */
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
            align-items: center;
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