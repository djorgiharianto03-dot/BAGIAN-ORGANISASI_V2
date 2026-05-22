/**
 * Modal detail KPI dashboard beranda.
 */
(function () {
    'use strict';

    var dataEl = document.getElementById('gov-kpi-details-data');
    var modalEl = document.getElementById('govKpiDetailModal');
    if (!dataEl || !modalEl) {
        return;
    }

    var store = {};
    try {
        store = JSON.parse(dataEl.textContent || '{}');
    } catch (e) {
        store = {};
    }

    var titleEl = document.getElementById('govKpiDetailModalTitle');
    var listSelesai = document.getElementById('govKpiDetailListSelesai');
    var listProses = document.getElementById('govKpiDetailListProses');
    var listBelum = document.getElementById('govKpiDetailListBelum');
    var countSelesai = document.getElementById('govKpiDetailCountSelesai');
    var countProses = document.getElementById('govKpiDetailCountProses');
    var countBelum = document.getElementById('govKpiDetailCountBelum');
    var emptyEl = document.getElementById('govKpiDetailEmpty');

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function renderList(ul, items, showAlasan) {
        if (!ul) {
            return;
        }
        ul.innerHTML = '';
        if (!items.length) {
            var li = document.createElement('li');
            li.className = 'gov-kpi-modal__list-empty';
            li.textContent = '— Tidak ada data —';
            ul.appendChild(li);
            return;
        }
        items.forEach(function (item) {
            var li = document.createElement('li');
            li.className = 'gov-kpi-modal__list-item';
            var html = '<span class="gov-kpi-modal__opd-name">' + esc(item.nama_opd || '') + '</span>';
            if (showAlasan && item.alasan) {
                html += '<span class="gov-kpi-modal__alasan">' + esc(item.alasan) + '</span>';
            }
            li.innerHTML = html;
            ul.appendChild(li);
        });
    }

    document.querySelectorAll('[data-gov-kpi-detail]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-gov-kpi-detail');
            var pack = store[id] || { judul: '', selesai: [], dalam_pengerjaan: [], belum: [] };
            if (titleEl) {
                titleEl.textContent = pack.judul || 'Detail Indikator';
            }
            var selesai = pack.selesai || pack.sudah || [];
            var proses = pack.dalam_pengerjaan || [];
            var belum = pack.belum || [];
            if (countSelesai) {
                countSelesai.textContent = String(selesai.length);
            }
            if (countProses) {
                countProses.textContent = String(proses.length);
            }
            if (countBelum) {
                countBelum.textContent = String(belum.length);
            }
            renderList(listSelesai, selesai, false);
            renderList(listProses, proses, true);
            renderList(listBelum, belum, true);
            if (emptyEl) {
                emptyEl.classList.toggle('d-none', selesai.length + proses.length + belum.length > 0);
            }
        });
    });
})();
