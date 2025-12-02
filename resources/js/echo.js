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
        updateStatusModal(e.message, e.modal_status, e.slug, e.success);
    });

function updateStatusModal(message, modal_status, slug, success) {

    const modalId = 'simple-status-modal';

    const spinnerSvg = `
        <svg id="status-spinner" class="animate-spin" style="margin: 0 auto 1rem; width: 6rem; height: 6rem; stroke: #fff;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
    const warningSvg = `
        <svg id="status-warning" style="margin: 0 auto 1rem; width: 6rem; height: 6rem;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="red" stroke-width="2" fill="rgba(255, 0, 0, 0.1)" />
            <path d="M12 8v4" stroke="red" stroke-width="2" stroke-linecap="round"/>
            <circle cx="12" cy="16" r="1" fill="red" />
        </svg>
    `;
    const successSvg = `
    <svg id="status-success" style="margin: 0 auto 1rem; width: 6rem; height: 6rem;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="10" stroke="green" stroke-width="2" fill="rgba(0, 128, 0, 0.1)" />
        <path d="M8 12.5l3 3 5-6" stroke="green" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
`;

    
    let massagefull =
        `<p id="modal-message" style="font-weight: 300; font-size: 2.25rem; ">
            <style>
                @media (max-width: 768px) {
                    #modal-message {
                        font-size: 1.4rem !important;
                    }
                }
            </style>
            ${message}
        </p>`;
    // ** 1. ตรวจสอบเงื่อนไขการเสร็จสิ้นก่อนเริ่มกระบวนการ Modal **
    console.log(massagefull)
    if (modal_status === 'close') {
        closeModal(massagefull, modalId, slug, warningSvg, successSvg, success);
        return; // ออกจากฟังก์ชันทันที ไม่ต้องสร้าง Modal
    }
    let modal = document.getElementById(modalId);

    
    let svgToUse = success ? spinnerSvg : warningSvg;
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
            background-color: rgba(0, 0, 0, 0.9); /* สีดำทึบ */
            display: flex;
            align-items: center; 
            justify-content: center; 
            z-index: 1050;
        `;

        modal.innerHTML = `
            <div style="padding: 2rem; color: white; text-align: center;">
                <div id="svg-status">${svgToUse}</div>
                ${massagefull}
            </div>
        `;

        document.body.appendChild(modal);
    } else {
        document.getElementById('modal-message').innerHTML = massagefull;
    }

    if (modal_status === 'popup') {
        popUpModal(modal);

        return; // ออกจากฟังก์ชันทันที ไม่ต้องสร้าง Modal
    }
}

function closeModal(massagefull, modalId, slug, warningSvg, successSvg, success) { //สำหรับปิด modal
    
    document.getElementById('modal-message').innerHTML = massagefull;
    document.getElementById('svg-status').innerHTML = success ? successSvg : warningSvg;

    const modalToRemove = document.getElementById(modalId);
    setTimeout(() => {
        modalToRemove.remove();
        Livewire.dispatch('openActionModal', { id: slug });
    }, 4000);
}

function popUpModal(modal) { //สำหรับเปิด popup ทั่วไปเพื่อแจ้งเตือน
    setTimeout(() => {
        modal.remove();
    }, 4000);
}
// alert('test');
// console.log('test')





