import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
//window.Echo.channel('test-channel')
//
//window.Echo.private('user.${id}')
window.Echo.private('user.' + window.App.userId)
    .listen('.ProcessEmpDocEvent', (e) => {
        //alert(e.message);
        console.log(e.modal_status)
        console.log(e.message)
        updateStatusModal(e.message, e.modal_status, e.slug, e.success);
    });

function updateStatusModal(message, modal_status, slug, success) {
    const modalId = 'simple-status-modal';

    if (modal_status === 'edit') {
        Livewire.dispatch('document-upload-error', { actionId: slug });

        return; // ออกจากฟังก์ชันทันที ไม่ต้องสร้าง Modal
    }

    // ** 1. ตรวจสอบเงื่อนไขการเสร็จสิ้นก่อนเริ่มกระบวนการ Modal **
    if (modal_status === 'close') {
        closeModal(message, modalId, slug, success);

        return; // ออกจากฟังก์ชันทันที ไม่ต้องสร้าง Modal
    }

    // 2. ถ้าข้อความไม่ใช่ 'กระบวนการเสร็จสิ้น' ให้ทำงานต่อ
    let modal = document.getElementById(modalId);

    // โค้ด SVG Spinner อย่างง่าย
    const spinnerSvg = `
        <svg id="status-spinner" class="animate-spin" style="margin: 0 auto 1rem; width: 3rem; height: 3rem; stroke: #fff;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <style>
                /* Animation Keyframes: ต้องกำหนดในโค้ด */
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
                .animate-spin {
                    animation: spin 1s linear infinite;
                }
            </style>
            <circle cx="12" cy="12" r="10" stroke-width="2" stroke="rgba(255, 255, 255, 0.2)" />
            <path d="M12 2C6.477 2 2 6.477 2 12" stroke-width="2" stroke-linecap="round" />
        </svg>
    `;

    // 3. Logic สร้าง/อัปเดต Modal (เฉพาะเมื่อยังไม่เสร็จสิ้น)
    if (!modal) {
        modal = document.createElement('div');
        modal.id = modalId;

        // ** ใช้ Inline CSS แทน Class Name ทั้งหมด **
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7); /* สีดำทึบ */
            display: flex;
            align-items: center; 
            justify-content: center; 
            z-index: 1050;
            padding: 1rem;
        `;

        modal.innerHTML = `
            <div style="padding: 2rem; color: white; text-align: center;">
                <!-- 1. Spinner ที่ถูกแทรก -->
                ${spinnerSvg}
                <!-- 2. ข้อความสถานะจาก Event (ตัวอักษรใหญ่) -->
                <p id="modal-message" style="font-size: 2.25rem; font-weight: 300;">${message}</p>
            </div>
        `;

        document.body.appendChild(modal);
    } else {

        document.getElementById('modal-message').textContent = message;

    }
}

function closeModal(message, modalId, slug, success) { //สำหรับปิด modal
    document.getElementById('modal-message').textContent = message;
    const modalToRemove = document.getElementById(modalId);

    // หน่วงเวลา 2 วินาที ก่อนปิด Modal และรีเฟรชหน้าจอ
    setTimeout(() => {
        modalToRemove.remove();
        Livewire.dispatch('openActionModal', { id: slug });
    }, 3000);
}
// alert('test');
// console.log('test')





