

$(document).ready(function () {

    
    setTimeout(() => $('.alert-flash').fadeOut('slow'), 4000);


    
    var $nav = $('#mainNav');
    var isHeroPage = $('section.hero-section').length > 0;

    function updateNav() {
        var scrollY = $(window).scrollTop();
        if (isHeroPage) {
            if (scrollY < 40) {
                $nav.addClass('nav-transparent').removeClass('nav-scrolled');
            } else {
                $nav.removeClass('nav-transparent').addClass('nav-scrolled');
            }
        } else {
            $nav.removeClass('nav-transparent');
            if (scrollY > 60) $nav.addClass('nav-scrolled');
            else $nav.removeClass('nav-scrolled');
        }
    }

    function updateScrollSpy() {
        if (!isHeroPage) return;
        var scrollPos = $(window).scrollTop() + 120; 

        var $links = $('.nav-links-pill .nav-link');
        var $kosList = $('#kos-list');
        var $caraPesan = $('#cara-pesan');

        var activeId = 'beranda';

        if ($caraPesan.length && scrollPos >= $caraPesan.offset().top) {
            activeId = 'cara-pesan';
        } else if ($kosList.length && scrollPos >= $kosList.offset().top) {
            activeId = 'kos-list';
        }

        $links.removeClass('active');
        if (activeId === 'cara-pesan') {
            $links.filter('[href*="#cara-pesan"]').addClass('active');
        } else if (activeId === 'kos-list') {
            $links.filter('[href*="#kos-list"]').addClass('active');
        } else {
            $links.filter('[href$="index.php"]').addClass('active');
        }
    }

    $(window).on('scroll', function () {
        updateNav();
        updateScrollSpy();
    });
    updateNav();
    updateScrollSpy();


    
    const $slides = $('.hero-slide');
    if ($slides.length > 0) {
        let currentSlide = 0;
        let slideInterval = setInterval(nextSlide, 7000); 

        function showSlide(index) {
            $slides.removeClass('active');
            currentSlide = (index + $slides.length) % $slides.length;
            const $activeSlide = $slides.eq(currentSlide);
            $activeSlide.addClass('active');
            
            const theme = $activeSlide.attr('data-theme') || 'warm';
            $('.hero-section').attr('data-theme', theme);
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function prevSlide() {
            showSlide(currentSlide - 1);
        }

        $('.hero-arrow-btn.btn-next').on('click', function () {
            clearInterval(slideInterval);
            nextSlide();
            slideInterval = setInterval(nextSlide, 7000);
        });

        $('.hero-arrow-btn.btn-prev').on('click', function () {
            clearInterval(slideInterval);
            prevSlide();
            slideInterval = setInterval(nextSlide, 7000);
        });
    }


    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, idx) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), idx * 80);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('.kos-card-anim').forEach(card => observer.observe(card));


    
    let activeTipe = 'semua';
    let searchKeyword = '';
    let searchTimeout = null;

    function filterKosGrid(tipe, searchKeyword) {
        const $grid = $('#kosGrid');
        if (!$grid.length) return;

        let showCount = 0;
        let popIdx = 0;

        
        $grid.find('.kos-card-anim').removeClass('pop-in').css('animation-delay', '');

        $grid.find('.kos-card-anim').each(function () {
            const cardTipe = $(this).data('tipe') || '';
            const cardNama = ($(this).data('nama') || '').toLowerCase();

            const matchesTipe = (tipe === 'semua' || cardTipe.includes(tipe));
            const matchesSearch = (!searchKeyword || cardNama.includes(searchKeyword));

            if (matchesTipe && matchesSearch) {
                const $card = $(this);
                
                $card.hide();
                setTimeout(function () {
                    $card.css('animation-delay', (popIdx * 45) + 'ms').show().addClass('pop-in');
                    popIdx++;
                }, 20);
                showCount++;
            } else {
                $(this).hide();
            }
        });

        
        const $emptyState = $('#kosListEmptyState');
        if (showCount === 0) {
            if (!$emptyState.length) {
                $grid.after(`
                    <div id="kosListEmptyState" class="text-center py-5 text-muted w-100">
                        <i class="bi bi-house-slash" style="font-size:3.5rem;opacity:.35"></i>
                        <p class="mt-3 fw-semibold">Tidak ada kos ditemukan.</p>
                    </div>
                `);
            } else {
                $emptyState.show();
            }
        } else {
            $emptyState.hide();
        }
    }

    
    


    
    $('#navSearchInput').on('input', function () {
        const val = $(this).val();
        if ($('#searchKos').length) {
            $('#searchKos').val(val);
            searchKeyword = val.toLowerCase();

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                filterKosGrid(activeTipe, searchKeyword);
            }, 150); 

            
            if (val.length > 0) {
                $('html,body').stop().animate({ scrollTop: $('#kos-list').offset().top - 80 }, 350);
            }
        }
    });

    
    $('#navSearchForm').on('submit', function (e) {
        
    });


    
    


    
    $(document).on('click', '.btn-hapus', function (e) {
        e.preventDefault();
        const url  = $(this).attr('href');
        const nama = $(this).data('nama') || 'data ini';
        if (confirm(`Yakin ingin menghapus "${nama}"?\nData tidak dapat dikembalikan.`)) {
            window.location.href = url;
        }
    });


    
    $('#formReservasi').on('submit', function (e) {
        const tanggalVal = $('#tanggal_masuk').val();
        const hari       = new Date(); hari.setHours(0, 0, 0, 0);
        const durasi     = parseInt($('#durasi').val());

        if (!tanggalVal) {
            e.preventDefault();
            alert('Pilih tanggal masuk terlebih dahulu!');
            document.getElementById('customCalendar')?.scrollIntoView({behavior:'smooth',block:'center'});
            return;
        }
        const tanggal = new Date(tanggalVal + 'T00:00:00');
        if (tanggal < hari) {
            e.preventDefault();
            alert('Tanggal masuk tidak boleh sebelum hari ini!');
            return;
        }
        if (isNaN(durasi) || durasi < 1) {
            e.preventDefault();
            alert('Durasi sewa minimal 1 bulan!');
            $('#durasi').focus();
        }
    });


    
    function initCustomCalendar(config) {
        const MONTHS_ID = [
            'Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember'
        ];
        const DAYS_ID = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

        const today   = new Date();
        today.setHours(0, 0, 0, 0);

        let curYear  = today.getFullYear();
        let curMonth = today.getMonth();
        let selectedDate = null;

        const $grid    = $(config.gridSelector);
        const $headers = $(config.headersSelector);
        const $label   = $(config.labelSelector);
        const $display = $(config.displaySelector);
        const $selText = $(config.selTextSelector);
        const $hidden  = $(config.hiddenSelector);

        if (!$grid.length) return; 

        
        $headers.empty();
        DAYS_ID.forEach(d => {
            $headers.append(`<div style="
                text-align:center;font-size:.85rem;font-weight:700;
                color:#94a3b8;padding:8px 0;text-transform:uppercase;
                letter-spacing:.04em">${d}</div>`);
        });

        function pad(n){ return String(n).padStart(2,'0'); }

        function toYMD(y,m,d){
            return `${y}-${pad(m+1)}-${pad(d)}`;
        }

        function renderCalendar() {
            $label.text(`${MONTHS_ID[curMonth]} ${curYear}`);
            $grid.empty();

            const firstDay = new Date(curYear, curMonth, 1).getDay(); 
            const daysInMonth = new Date(curYear, curMonth + 1, 0).getDate();
            const todayYMD = toYMD(today.getFullYear(), today.getMonth(), today.getDate());
            const selYMD   = selectedDate ? toYMD(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate()) : null;

            
            for (let i = 0; i < firstDay; i++) {
                $grid.append('<div></div>');
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const ymd      = toYMD(curYear, curMonth, d);
                const cellDate = new Date(curYear, curMonth, d);
                const isPast   = cellDate < today;
                const isToday  = ymd === todayYMD;
                const isSel    = ymd === selYMD;

                let cellClasses = 'kk-cal-cell';
                if (isPast) {
                    cellClasses += ' kk-cal-cell-past';
                } else {
                    cellClasses += ' kk-cal-cell-active';
                }
                if (isToday) {
                    cellClasses += ' kk-cal-cell-today';
                }
                if (isSel) {
                    cellClasses += ' kk-cal-cell-selected';
                }

                const $cell = $(`<div data-ymd="${ymd}" data-past="${isPast ? 1 : 0}" class="${cellClasses}">${d}</div>`);

                if (!isPast) {
                    $cell.on('click', function(){
                        const ymd = $(this).data('ymd');
                        selectedDate = new Date(ymd + 'T00:00:00');
                        $hidden.val(ymd);
                        
                        const opts = { weekday:'long', day:'2-digit', month:'long', year:'numeric' };
                        $selText.text(selectedDate.toLocaleDateString('id-ID', opts));
                        $display.css('display','flex');
                        renderCalendar(); 
                        if (config.onChange) {
                            config.onChange();
                        }
                    });
                }

                $grid.append($cell);
            }
        }

        $(config.prevSelector).off('click').on('click', function(){
            curMonth--;
            if (curMonth < 0) { curMonth = 11; curYear--; }
            
            if (curYear < today.getFullYear() ||
               (curYear === today.getFullYear() && curMonth < today.getMonth())) {
                curMonth = today.getMonth();
                curYear  = today.getFullYear();
            }
            renderCalendar();
        });

        $(config.nextSelector).off('click').on('click', function(){
            curMonth++;
            if (curMonth > 11) { curMonth = 0; curYear++; }
            renderCalendar();
        });

        renderCalendar();
    }

    
    initCustomCalendar({
        gridSelector: '#calGrid',
        headersSelector: '#calDayHeaders',
        labelSelector: '#calMonthLabel',
        displaySelector: '#calSelectedDisplay',
        selTextSelector: '#calSelectedText',
        hiddenSelector: '#tanggal_masuk',
        prevSelector: '#calPrev',
        nextSelector: '#calNext',
        onChange: function () {
            hitungEstimasi();
        }
    });


    
    function hitungEstimasi() {
        const harga  = parseInt($('#hargaPerBulan').data('harga')) || 0;
        const durasi = parseInt($('#durasi').val()) || 0;
        const total  = harga * durasi;
        $('#estimasiTotal').text(
            total > 0 ? 'Estimasi total: Rp ' + total.toLocaleString('id-ID') : ''
        );
    }
    $('#durasi').on('input', hitungEstimasi);
    hitungEstimasi();


    
    $(document).on('click', '.open-kos-modal', function (e) {
        e.preventDefault();
        const kosId = $(this).data('kos-id');
        const $modal = $('#kosModal');
        const $modalBody = $('#kosModalBody');

        
        $modalBody.html(`
            <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5 text-white preloader-spinner">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.3em;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="fw-bold mb-0 text-white-50 fs-5">Memuat Detail Kos...</p>
            </div>
        `);

        
        const bootstrapModal = new bootstrap.Modal($modal[0]);
        bootstrapModal.show();

        
        $.ajax({
            url: 'ajax_get_kos.php',
            method: 'GET',
            data: { id: kosId },
            success: function (html) {
                $modalBody.html(html);
                initModalBookingLogic();
            },
            error: function () {
                $modalBody.html(`
                    <div class="alert text-danger bg-danger-subtle p-4 border-danger-subtle rounded-3 text-center my-4">
                        <i class="bi bi-exclamation-triangle-fill fs-2 d-block mb-2"></i>
                        Gagal memuat detail kos. Silakan coba lagi.
                    </div>
                `);
            }
        });
    });

    function initModalBookingLogic() {
        
        $('.btn-pesan-kamar').on('click', function () {
            const idKamar = $(this).data('id-kamar');
            const nomorKamar = $(this).data('nomor-kamar');
            const tipeKamar = $(this).data('tipe-kamar');
            const harga = parseInt($(this).data('harga')) || 0;

            
            $('#modal_id_kamar').val(idKamar);
            $('#selRoomName').text('Kamar ' + nomorKamar);
            $('#selRoomTipe').text(tipeKamar);
            $('#selRoomHarga').text('Rp ' + harga.toLocaleString('id-ID') + '/bln').attr('data-raw-harga', harga);

            
            $('#modal_tanggal_masuk').val('');
            $('#mCalSelectedDisplay').hide();
            $('#modal_durasi').val('');
            $('#mEstimasiTotal').text('');

            
            $('#modalOverviewPanel').addClass('fade-out');
            setTimeout(() => {
                $('#modalOverviewPanel').addClass('d-none').removeClass('fade-out');
                $('#modalBookingPanel').removeClass('d-none').addClass('fade-in');
                
                
                initCustomCalendar({
                    gridSelector: '#mCalGrid',
                    headersSelector: '#mCalDayHeaders',
                    labelSelector: '#mCalMonthLabel',
                    displaySelector: '#mCalSelectedDisplay',
                    selTextSelector: '#mCalSelectedText',
                    hiddenSelector: '#modal_tanggal_masuk',
                    prevSelector: '#mCalPrev',
                    nextSelector: '#mCalNext',
                    onChange: function () {
                        hitungEstimasiModal();
                    }
                });
            }, 150);
        });

        
        $('#btnBackToOverview').on('click', function () {
            $('#modalBookingPanel').addClass('fade-out');
            setTimeout(() => {
                $('#modalBookingPanel').addClass('d-none').removeClass('fade-out');
                $('#modalOverviewPanel').removeClass('d-none').addClass('fade-in');
            }, 150);
        });

        
        function hitungEstimasiModal() {
            const harga = parseInt($('#selRoomHarga').attr('data-raw-harga')) || 0;
            const durasi = parseInt($('#modal_durasi').val()) || 0;
            const total = harga * durasi;
            $('#mEstimasiTotal').text(
                total > 0 ? 'Estimasi total: Rp ' + total.toLocaleString('id-ID') : ''
            );
        }
        $('#modal_durasi').on('input', hitungEstimasiModal);

        
        $('#modalFormReservasi').on('submit', function (e) {
            e.preventDefault();

            const tanggalVal = $('#modal_tanggal_masuk').val();
            const durasi = parseInt($('#modal_durasi').val());
            const hari = new Date();
            hari.setHours(0,0,0,0);

            if (!tanggalVal) {
                alert('Pilih tanggal masuk terlebih dahulu!');
                return;
            }
            const tanggal = new Date(tanggalVal + 'T00:00:00');
            if (tanggal < hari) {
                alert('Tanggal masuk tidak boleh sebelum hari ini!');
                return;
            }
            if (isNaN(durasi) || durasi < 1) {
                alert('Durasi sewa minimal 1 bulan!');
                $('#modal_durasi').focus();
                return;
            }

            const formData = $(this).serialize();

            
            $.ajax({
                url: 'ajax_submit_reservasi.php',
                method: 'POST',
                data: formData,
                success: function (res) {
                    if (res.success) {
                        
                        $('#kosModalBody').html(`
                            <div class="success-checkmark-wrapper">
                                <div class="success-checkmark-circle">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <h3 class="text-dark fw-bold mb-3">Reservasi Berhasil!</h3>
                                <p class="text-muted mb-4 px-md-5" style="font-size: 1.05rem;">
                                    ${res.message}
                                </p>
                                <button type="button" class="btn btn-primary px-5 py-2.5 fw-bold text-white rounded-3 shadow" data-bs-dismiss="modal">
                                    Selesai
                                </button>
                            </div>
                        `);
                    } else {
                        alert(res.message || 'Gagal mengirim reservasi.');
                    }
                },
                error: function () {
                    alert('Gagal mengirim reservasi. Silakan periksa koneksi internet Anda.');
                }
            });
        });
    }


    
    $('#inputFoto').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2 MB.');
            $(this).val(''); return;
        }
        const reader = new FileReader();
        reader.onload = e => $('#previewFoto').attr('src', e.target.result).show();
        reader.readAsDataURL(file);
    });

}); 
