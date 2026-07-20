/**
 * Fail JavaScript Utama (Premium UX) - SukanJTS Sarawak
 * Menguruskan Modal Lightbox Galeri, Validasi Borang, dan Kemas Kini Keputusan Interaktif.
 */

document.addEventListener("DOMContentLoaded", function() {
    // 1. Validasi Borang Bootstrap 5 secara global
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // 2. Lightbox Galeri (Imej & Video) berasaskan Modal Bootstrap 5
    setupGalleryLightbox();
});

/**
 * Persediaan Lightbox untuk Galeri Media
 */
function setupGalleryLightbox() {
    const galleryItems = document.querySelectorAll('.gallery-grid-item');
    if (galleryItems.length === 0) return;

    // Cipta elemen Modal Lightbox secara dinamik jika tiada dalam HTML
    let lightboxModal = document.getElementById('lightboxModal');
    if (!lightboxModal) {
        const modalHTML = `
            <div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(2, 6, 17, 0.95);">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 bg-transparent">
                        <div class="modal-header border-0 p-0 position-relative">
                            <button type="button" class="btn-close btn-close-white position-absolute end-0 top-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body p-0 text-center">
                            <div id="lightboxMediaContainer"></div>
                            <h5 id="lightboxTitle" class="text-white mt-3 fw-semibold"></h5>
                            <p id="lightboxAlbum" class="text-muted small"></p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        lightboxModal = document.getElementById('lightboxModal');
    }

    const bootstrapModal = new bootstrap.Modal(lightboxModal);
    const mediaContainer = document.getElementById('lightboxMediaContainer');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxAlbum = document.getElementById('lightboxAlbum');

    // Tambah event listener pada setiap item galeri
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            const mediaType = this.getAttribute('data-type');
            const mediaUrl = this.getAttribute('data-url');
            const title = this.getAttribute('data-title') || 'Media Kejohanan';
            const album = this.getAttribute('data-album') || 'Umum';

            mediaContainer.innerHTML = ''; // Bersihkan kontena media lama

            if (mediaType === 'imej') {
                const img = document.createElement('img');
                img.src = mediaUrl;
                img.className = 'img-fluid rounded-3 shadow-lg max-h-70vh';
                img.style.maxHeight = '70vh';
                mediaContainer.appendChild(img);
            } else if (mediaType === 'video') {
                const video = document.createElement('video');
                video.src = mediaUrl;
                video.className = 'w-100 rounded-3 shadow-lg';
                video.controls = true;
                video.autoplay = true;
                video.style.maxHeight = '70vh';
                mediaContainer.appendChild(video);
            }

            lightboxTitle.textContent = title;
            lightboxAlbum.textContent = 'Album: ' + album;
            
            bootstrapModal.show();
        });
    });

    // Hentikan tayangan video apabila modal ditutup
    lightboxModal.addEventListener('hidden.bs.modal', function() {
        const video = mediaContainer.querySelector('video');
        if (video) {
            video.pause();
            video.src = "";
        }
        mediaContainer.innerHTML = '';
    });
}
