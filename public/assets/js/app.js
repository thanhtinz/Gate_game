/* Gate Game portal JS */
(function () {
  'use strict';

  var BASE = document.querySelector('script[src*="app.js"]').getAttribute('src').replace(/\/assets\/js\/app\.js.*$/, '');

  function api(path) {
    return fetch(BASE + path, { credentials: 'same-origin' }).then(function (r) { return r.json(); });
  }

  /* ---------- Banner slider ---------- */
  var slider = document.getElementById('bannerSlider');
  if (slider) {
    var slides = slider.querySelectorAll('.banner-slide');
    var dots = slider.querySelectorAll('.dot');
    var cur = 0;
    function show(i) {
      cur = (i + slides.length) % slides.length;
      slides.forEach ? null : null;
      for (var k = 0; k < slides.length; k++) {
        slides[k].classList.toggle('active', k === cur);
        if (dots[k]) dots[k].classList.toggle('active', k === cur);
      }
    }
    for (var d = 0; d < dots.length; d++) {
      (function (i) { dots[i].addEventListener('click', function () { show(i); }); })(d);
    }
    if (slides.length > 1) setInterval(function () { show(cur + 1); }, 5000);
  }

  /* ---------- Cascade: game -> server -> nhân vật ---------- */
  var selGame = document.getElementById('selGame');
  var selServer = document.getElementById('selServer');
  var selChar = document.getElementById('selChar');

  function resetSelect(sel, placeholder) {
    if (!sel) return;
    sel.innerHTML = '<option value="">' + placeholder + '</option>';
    sel.disabled = true;
  }

  if (selGame && selServer) {
    selGame.addEventListener('change', function () {
      resetSelect(selServer, '— Đang tải... —');
      resetSelect(selChar, '— Chọn server trước —');
      renderPackages();
      hideGiftPreview();
      if (!selGame.value) { resetSelect(selServer, '— Chọn game trước —'); return; }
      api('/api/servers?game_id=' + encodeURIComponent(selGame.value)).then(function (res) {
        resetSelect(selServer, '— Chọn server —');
        if (res.success && res.servers.length) {
          res.servers.forEach(function (s) {
            var o = document.createElement('option');
            o.value = s.id;
            o.textContent = s.name + (s.note ? ' — ' + s.note : '');
            selServer.appendChild(o);
          });
          selServer.disabled = false;
        } else {
          resetSelect(selServer, '— Game chưa có server —');
        }
      }).catch(function () { resetSelect(selServer, '— Lỗi tải server —'); });
    });
  }

  if (selServer && selChar) {
    selServer.addEventListener('change', function () {
      resetSelect(selChar, '— Đang tải... —');
      if (!selServer.value) { resetSelect(selChar, '— Chọn server trước —'); return; }
      api('/api/characters?server_id=' + encodeURIComponent(selServer.value)).then(function (res) {
        resetSelect(selChar, '— Chọn nhân vật —');
        if (res.success && res.characters.length) {
          res.characters.forEach(function (c) {
            var o = document.createElement('option');
            o.value = c.id;
            o.textContent = c.name + (c.info ? ' (' + c.info + ')' : '');
            selChar.appendChild(o);
          });
          selChar.disabled = false;
        } else if (res.success) {
          resetSelect(selChar, '— Tài khoản chưa có nhân vật (vào game tạo trước) —');
        } else {
          resetSelect(selChar, '— ' + (res.message || 'Lỗi') + ' —');
        }
      }).catch(function () { resetSelect(selChar, '— Lỗi tải nhân vật —'); });
    });
  }

  /* ---------- Gói quy đổi theo game ---------- */
  var pkgArea = document.getElementById('packageArea');
  var pkgWrap = document.getElementById('exchangePackages');

  function renderPackages() {
    if (!pkgWrap || !window.EXCHANGE_PACKAGES) return;
    var gid = selGame ? selGame.value : '';
    var list = window.EXCHANGE_PACKAGES[gid] || [];
    var labels = (window.CURRENCY_LABELS || {})[gid] || {};
    pkgWrap.innerHTML = '';
    if (!gid || !list.length) {
      if (pkgArea) pkgArea.classList.add('hidden');
      return;
    }
    list.forEach(function (p) {
      var label = labels[p.currency_key] || p.currency_key;
      var el = document.createElement('label');
      el.className = 'package-card selectable';
      el.innerHTML =
        '<input type="radio" name="package_id" value="' + p.id + '" required>' +
        '<h3></h3>' +
        '<div class="package-xu"></div>' +
        (parseInt(p.bonus_amount, 10) > 0 ? '<div class="package-bonus"></div>' : '') +
        '<div class="package-price"></div>';
      el.querySelector('h3').textContent = p.name;
      el.querySelector('.package-xu').textContent = Number(p.currency_amount).toLocaleString('vi-VN') + ' ' + label;
      if (parseInt(p.bonus_amount, 10) > 0) {
        el.querySelector('.package-bonus').textContent = '+ ' + Number(p.bonus_amount).toLocaleString('vi-VN') + ' ' + label + ' tặng';
      }
      el.querySelector('.package-price').textContent = Number(p.xu_cost).toLocaleString('vi-VN') + ' xu';
      pkgWrap.appendChild(el);
    });
    if (pkgArea) pkgArea.classList.remove('hidden');
  }

  /* ---------- Giftcode preview: hiện quà ngay dưới ô nhập ---------- */
  var giftInput = document.getElementById('giftcodeInput');
  var giftPreview = document.getElementById('giftcodePreview');
  var giftTimer = null;

  function hideGiftPreview() {
    if (giftPreview) { giftPreview.classList.add('hidden'); giftPreview.innerHTML = ''; }
  }

  function esc(s) {
    var div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  if (giftInput && giftPreview) {
    giftInput.addEventListener('input', function () {
      clearTimeout(giftTimer);
      var code = giftInput.value.trim();
      var gid = selGame ? selGame.value : '';
      if (!code || !gid) { hideGiftPreview(); return; }
      giftTimer = setTimeout(function () {
        api('/api/giftcode-info?game_id=' + encodeURIComponent(gid) + '&code=' + encodeURIComponent(code)).then(function (res) {
          if (!res.success) {
            giftPreview.innerHTML = '<div class="gift-msg ' + (res.used ? 'warn' : 'err') + '">' + esc(res.message) + '</div>';
            giftPreview.classList.remove('hidden');
            return;
          }
          var okIcon = '<svg class="ic" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>';
          var giftIcon = '<svg class="ic" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8"/><path d="M12 8v13"/><path d="M12 8S10.5 4 8.5 4A2.5 2.5 0 0 0 8.5 9H12Z"/><path d="M12 8s1.5-4 3.5-4a2.5 2.5 0 0 1 0 5H12Z"/></svg>';
          var html = '<div class="gift-msg ok">' + okIcon + ' Giftcode hợp lệ' + (res.description ? ' — ' + esc(res.description) : '') + '</div>';
          if (res.items && res.items.length) {
            html += '<div class="gift-items">';
            res.items.forEach(function (it) {
              html += '<div class="gift-item">' +
                (it.icon ? '<img src="' + esc(it.icon) + '" alt="">' : '<span class="gift-item-emoji">' + giftIcon + '</span>') +
                '<span class="gift-item-name">' + esc(it.name || '') + '</span>' +
                (it.qty ? '<span class="gift-item-qty">x' + esc(it.qty) + '</span>' : '') +
                '</div>';
            });
            html += '</div>';
          }
          giftPreview.innerHTML = html;
          giftPreview.classList.remove('hidden');
        }).catch(hideGiftPreview);
      }, 400);
    });
  }

  /* ---------- Polling trạng thái đơn nạp ---------- */
  var orderBox = document.querySelector('[data-order-code]');
  if (orderBox && orderBox.getAttribute('data-order-status') === 'pending') {
    var code = orderBox.getAttribute('data-order-code');
    var poll = setInterval(function () {
      api('/api/order-status?code=' + encodeURIComponent(code)).then(function (res) {
        if (res.success && res.status === 'completed') {
          clearInterval(poll);
          location.reload();
        }
      }).catch(function () {});
    }, 5000);
  }
})();

/* Toggle hiện/ẩn mật khẩu (đổi icon mắt <-> mắt gạch) */
var EYE = '<svg class="ic" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
var EYE_OFF = '<svg class="ic" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 5.2A10.6 10.6 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.3 4.1M6.3 6.3A17 17 0 0 0 2 12s3.5 7 10 7a10.6 10.6 0 0 0 4-.8"/><path d="M9.5 9.5a3 3 0 0 0 4.2 4.2"/><path d="m3 3 18 18"/></svg>';
document.addEventListener('click', function (e) {
  var btn = e.target.closest('.pw-toggle');
  if (!btn) return;
  var input = document.getElementById(btn.getAttribute('data-target'));
  if (!input) return;
  input.type = input.type === 'password' ? 'text' : 'password';
  btn.innerHTML = input.type === 'password' ? EYE : EYE_OFF;
});

/* Chuyển theme sáng/tối */
(function () {
  var ICON_MOON = '<svg class="ic" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5Z"/></svg>';
  var ICON_SUN = '<svg class="ic" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"/></svg>';
  var btn = document.getElementById('themeToggle');
  if (!btn) return;
  function current() {
    return document.documentElement.getAttribute('data-theme') || 'dark';
  }
  function render() {
    // Hiện icon của theme sẽ chuyển sang
    btn.innerHTML = current() === 'light' ? ICON_MOON : ICON_SUN;
  }
  render();
  btn.addEventListener('click', function () {
    var next = current() === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', next);
    try { localStorage.setItem('theme', next); } catch (e) {}
    render();
  });
})();
