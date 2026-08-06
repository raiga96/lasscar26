/**
 * Spin Wheel LASSCAR 2028 - Engine Render Canvas & Logic Animasi
 * ChatGPT UI/UX Specialist & Claude Backend Integration
 */

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('wheelCanvas');
    const ctx = canvas.getContext('2d');
    const btnSpin = document.getElementById('btnSpin');
    const statusText = document.getElementById('statusText');
    const statusBadge = document.getElementById('statusBadge');
    
    // Element Admin Control
    const adminToggleBtn = document.getElementById('adminToggleBtn');
    const adminBar = document.getElementById('adminBar');
    const selectPemenang = document.getElementById('selectPemenang');
    const btnLockWinner = document.getElementById('btnLockWinner');
    const btnResetWheel = document.getElementById('btnResetWheel');

    let items = [];
    let currentRotation = 0; // dalam darjah
    let isSpinning = false;
    let loadedImages = {};
    let drawState = null;

    // Skala Canvas mengikut saiz paparan sebenar
    function setupCanvasSize() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = 1000;
        canvas.height = 1000;
    }

    // Skim warna teras kontras & elegan untuk 13 bahagian
    const colorPalette = [
        '#0f172a', '#1e3a8a', '#1e293b', '#1e1b4b', '#0f766e',
        '#312e81', '#172554', '#1e293b', '#0284c7', '#0369a1',
        '#334155', '#1e3a8a', '#0f172a'
    ];

    // Preload logo imej
    function preloadImages(itemList, callback) {
        let loadedCount = 0;
        const total = itemList.length;

        if (total === 0) {
            callback();
            return;
        }

        itemList.forEach(item => {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = () => {
                loadedImages[item.id] = img;
                loadedCount++;
                if (loadedCount === total) callback();
            };
            img.onerror = () => {
                loadedImages[item.id] = null;
                loadedCount++;
                if (loadedCount === total) callback();
            };
            img.src = item.logo_url;
        });
    }

    // Ambil data bahagian dari API
    function fetchWheelData() {
        fetch('actions/get_bahagian_wheel.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    items = data.items;
                    drawState = data.draw;

                    // Update UI Dropdown Admin
                    updateAdminDropdown(items, drawState);
                    updateStatusUI(drawState);

                    // Preload logo & render wheel
                    preloadImages(items, () => {
                        drawWheel();
                    });
                }
            })
            .catch(err => {
                console.error('Ralat fetching data wheel:', err);
                statusText.innerText = 'Ralat memuatkan data bahagian.';
            });
    }

    // Render Canvas Roda Spin
    function drawWheel() {
        setupCanvasSize();
        const numSegments = items.length;
        if (numSegments === 0) return;

        const width = canvas.width;
        const height = canvas.height;
        const centerX = width / 2;
        const centerY = height / 2;
        const radius = width / 2 - 20;

        ctx.clearRect(0, 0, width, height);

        const arcSize = (2 * Math.PI) / numSegments;

        for (let i = 0; i < numSegments; i++) {
            const angle = i * arcSize;
            const item = items[i];

            // 1. Lukis Segment Sektor
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, angle, angle + arcSize);
            ctx.closePath();

            // Warna selang-seli bergradien
            const bgColor = colorPalette[i % colorPalette.length];
            ctx.fillStyle = bgColor;
            ctx.fill();

            // Garisan sempadan jejari bertema emas
            ctx.strokeStyle = 'rgba(255, 193, 7, 0.4)';
            ctx.lineWidth = 4;
            ctx.stroke();

            // 2. Lukis Teks & Logo Bahagian
            ctx.save();
            ctx.translate(centerX, centerY);
            ctx.rotate(angle + arcSize / 2);

            // Lukis Logo Bahagian
            const img = loadedImages[item.id];
            const imgRadius = radius * 0.72;
            if (img && img.complete && img.naturalWidth !== 0) {
                ctx.drawImage(img, imgRadius - 25, -25, 50, 50);
            }

            // Lukis Teks Nama/Singkatan
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 24px "Plus Jakarta Sans", sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            
            // Bayangan teks untuk keterlihatan
            ctx.shadowColor = 'rgba(0, 0, 0, 0.9)';
            ctx.shadowBlur = 6;
            ctx.shadowOffsetX = 2;
            ctx.shadowOffsetY = 2;

            const textRadius = radius * 0.52;
            ctx.fillText(item.singkatan, textRadius, 0);

            ctx.restore();
        }

        // Bulatan Bingkai Dalaman Roda
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.strokeStyle = '#ffc107';
        ctx.lineWidth = 12;
        ctx.stroke();
    }

    // Kemaskini Status UI
    function updateStatusUI(draw) {
        // Sentiasa benarkan spin tanpa perlu pengesahan berasingan
        statusBadge.className = 'badge bg-success text-white px-3 py-2 rounded-pill';
        statusBadge.innerText = 'REDA & SEDIA';
        statusText.innerText = 'Roda telah bersedia untuk diputar!';
        btnSpin.disabled = false;
    }

    // Kemaskini Dropdown Admin
    function updateAdminDropdown(itemList, draw) {
        selectPemenang.innerHTML = '<option value="">-- Pilih Bahagian Penganjur --</option>';
        itemList.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.nama_bahagian;
            if (draw && draw.id_bahagian_menang == item.id) {
                opt.selected = true;
            }
            selectPemenang.appendChild(opt);
        });
    }

    // Kawalan Butang SPIN
    btnSpin.addEventListener('click', () => {
        if (isSpinning) return;

        isSpinning = true;
        btnSpin.disabled = true;
        statusText.innerText = 'Roda sedang berputar... Semoga berjaya!';

        fetch('actions/spin_wheel.php', {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const targetDegrees = data.final_rotation_degrees;
                const duration = data.duration_ms;

                // Animasi CSS transform rotation
                canvas.style.transition = `transform ${duration}ms cubic-bezier(0.15, 0.85, 0.15, 1)`;
                canvas.style.transform = `rotate(${targetDegrees}deg)`;

                // Tunggu animasi tamat untuk trigger reveal
                setTimeout(() => {
                    isSpinning = false;
                    fetchRevealResult();
                }, duration + 300);

            } else {
                isSpinning = false;
                btnSpin.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat Spin',
                    text: data.message || 'Gagal memulakan animasi spin.'
                });
            }
        })
        .catch(err => {
            isSpinning = false;
            btnSpin.disabled = false;
            console.error('Ralat spin API:', err);
        });
    });

    // Panggil Reveal Modal & Confetti
    function fetchRevealResult() {
        fetch('actions/get_hasil_draw.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const p = data.pemenang;

                    // Trigger Confetti
                    if (typeof confetti === 'function') {
                        confetti({
                            particleCount: 150,
                            spread: 90,
                            origin: { y: 0.6 }
                        });
                    }

                    // SweetAlert2 Reveal Modal
                    Swal.fire({
                        title: '<span style="color:#ffc107; font-weight:800; font-size:1.6rem;">TAHNIAH! PENGANJUR LASSCAR 2028</span>',
                        html: `
                            <div class="text-center py-3">
                                <div class="mb-3">
                                    <img src="${p.logo_url}" style="max-width: 140px; max-height: 140px; object-fit: contain; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.3));">
                                </div>
                                <h2 class="fw-bold text-dark mb-1" style="font-size: 1.8rem;">${p.nama_bahagian}</h2>
                                <p class="text-muted small">${p.keterangan}</p>
                                <span class="badge bg-gold text-dark fs-6 px-4 py-2 rounded-pill fw-bold">PENGANJUR RASMI LASSCAR 2028</span>
                            </div>
                        `,
                        confirmButtonText: '🎉 ALHAMDULILLAH / TAHNIAH!',
                        confirmButtonColor: '#ffc107',
                        customClass: {
                            confirmButton: 'text-dark fw-bold px-4 py-2 fs-6 rounded-pill'
                        },
                        allowOutsideClick: false
                    }).then(() => {
                        fetchWheelData();
                    });
                }
            });
    }

    // Actions Admin Lock Winner
    btnLockWinner.addEventListener('click', () => {
        const selectedId = selectPemenang.value;
        if (!selectedId) {
            Swal.fire('Perhatian', 'Sila pilih bahagian pemenang dari dropdown dahulu.', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('id_bahagian', selectedId);
        formData.append('action', 'set');

        fetch('actions/set_pemenang.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Pemenang Dikunci!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                fetchWheelData();
            } else {
                Swal.fire('Ralat', data.message, 'error');
            }
        });
    });

    // Actions Admin Reset Wheel
    btnResetWheel.addEventListener('click', () => {
        Swal.fire({
            title: 'Set Semula Roda Spin?',
            text: 'Ini akan mengosongkan status kunci dan membenarkan tetapan pemenang baharu.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Reset!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'reset');

                fetch('actions/set_pemenang.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Reset rotation visual
                        canvas.style.transition = 'none';
                        canvas.style.transform = 'rotate(0deg)';
                        fetchWheelData();
                    }
                });
            }
        });
    });

    // Toggle Admin Panel Visibility
    adminToggleBtn.addEventListener('click', () => {
        if (adminBar.classList.contains('d-none')) {
            adminBar.classList.remove('d-none');
            adminToggleBtn.innerText = '⚙️ Sembunyikan Panel Setup';
        } else {
            adminBar.classList.add('d-none');
            adminToggleBtn.innerText = '⚙️ Panel Setup (Admin)';
        }
    });

    // Inisialisasi awal
    fetchWheelData();
});
