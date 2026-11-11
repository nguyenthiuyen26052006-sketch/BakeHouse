(function () {
  'use strict';

  var csrfMeta = document.querySelector('meta[name="csrf-token"]');
  var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

  // ---------- Toasts ----------
  function toast(message, type) {
    var container = document.getElementById('bhToasts');
    if (!container || !window.bootstrap) { return; }
    var el = document.createElement('div');
    el.className = 'toast align-items-center border-0 text-bg-' + (type || 'success');
    el.setAttribute('role', 'alert');
    el.innerHTML = '<div class="d-flex"><div class="toast-body"></div>' +
      '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Đóng"></button></div>';
    el.querySelector('.toast-body').textContent = message;
    container.appendChild(el);
    el.addEventListener('hidden.bs.toast', function () { el.remove(); });
    new bootstrap.Toast(el, { delay: 4500 }).show();
  }
  window.BH = { toast: toast, csrf: csrfToken };

  document.querySelectorAll('[data-flash]').forEach(function (node) {
    toast(node.getAttribute('data-message'), node.getAttribute('data-flash'));
  });

  // ---------- fetch helper ----------
  function post(url, data) {
    var body = new FormData();
    Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
    return fetch(url, {
      method: 'POST',
      body: body,
      headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      credentials: 'same-origin'
    }).then(function (res) {
      return res.json().catch(function () { return { ok: false, message: 'Có lỗi xảy ra.' }; })
        .then(function (json) { json.status = res.status; return json; });
    });
  }

  function updateCartBadge(count) {
    document.querySelectorAll('.bh-cart-badge').forEach(function (b) {
      b.textContent = count;
      b.style.display = count > 0 ? 'flex' : 'none';
    });
  }

  // ---------- Add to cart ----------
  document.addEventListener('click', function (event) {
    var btn = event.target.closest('[data-add-to-cart]');
    if (!btn) { return; }
    event.preventDefault();
    var qtyInput = btn.getAttribute('data-qty-input') ? document.querySelector(btn.getAttribute('data-qty-input')) : null;
    var quantity = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;
    btn.disabled = true;
    post('/api/cart/add', { product_id: btn.getAttribute('data-add-to-cart'), quantity: quantity }).then(function (res) {
      btn.disabled = false;
      if (res.status === 401) { window.location.href = res.login_url || '/login'; return; }
      toast(res.message, res.ok ? 'success' : 'danger');
      if (typeof res.count !== 'undefined') { updateCartBadge(res.count); }
    }).catch(function () { btn.disabled = false; toast('Không thể kết nối máy chủ.', 'danger'); });
  });

  // ---------- Cart page ----------
  function applyCartResponse(res) {
    toast(res.message, res.ok ? 'success' : 'danger');
    if (typeof res.count !== 'undefined') { updateCartBadge(res.count); }
    if (!res.ok) { return false; }
    document.querySelectorAll('[data-cart-subtotal]').forEach(function (n) { n.textContent = res.subtotal; });
    var fee = parseInt(document.body.getAttribute('data-shipping-fee') || '0', 10);
    document.querySelectorAll('[data-cart-total]').forEach(function (n) {
      n.textContent = new Intl.NumberFormat('vi-VN').format(res.subtotal_raw + (res.count > 0 ? fee : 0)) + ' ₫';
    });
    return true;
  }

  document.querySelectorAll('[data-cart-qty]').forEach(function (input) {
    var row = input.closest('[data-cart-row]');
    var productId = row.getAttribute('data-cart-row');
    function send(value) {
      post('/api/cart/update', { product_id: productId, quantity: value }).then(function (res) {
        if (!applyCartResponse(res)) { window.location.reload(); return; }
        if (value < 1 || !res.lines[productId]) { row.remove(); if (res.count === 0) { window.location.reload(); } return; }
        input.value = res.lines[productId].quantity;
        row.querySelector('[data-line-total]').textContent = res.lines[productId].line_total;
      });
    }
    input.addEventListener('change', function () { send(parseInt(input.value, 10) || 1); });
    row.querySelectorAll('[data-qty-step]').forEach(function (b) {
      b.addEventListener('click', function () {
        send((parseInt(input.value, 10) || 1) + parseInt(b.getAttribute('data-qty-step'), 10));
      });
    });
  });

  document.querySelectorAll('[data-cart-remove]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var row = btn.closest('[data-cart-row]');
      post('/api/cart/remove', { product_id: row.getAttribute('data-cart-row') }).then(function (res) {
        if (applyCartResponse(res)) { row.remove(); if (res.count === 0) { window.location.reload(); } }
      });
    });
  });

  // ---------- Quantity stepper (product detail) ----------
  document.querySelectorAll('.bh-qty').forEach(function (box) {
    var input = box.querySelector('input');
    box.querySelectorAll('[data-step]').forEach(function (b) {
      b.addEventListener('click', function () {
        var max = parseInt(input.getAttribute('max') || '999', 10);
        var next = (parseInt(input.value, 10) || 1) + parseInt(b.getAttribute('data-step'), 10);
        input.value = Math.min(Math.max(1, next), max);
      });
    });
  });

  // ---------- Product gallery ----------
  document.querySelectorAll('[data-gallery-thumb]').forEach(function (thumb) {
    thumb.addEventListener('click', function () {
      document.getElementById('bhGalleryMain').src = thumb.getAttribute('data-gallery-thumb');
      document.querySelectorAll('[data-gallery-thumb]').forEach(function (t) { t.classList.remove('is-active'); });
      thumb.classList.add('is-active');
    });
  });

  // ---------- Price range label ----------
  var range = document.getElementById('bhPriceRange');
  if (range) {
    var out = document.getElementById('bhPriceOutput');
    var update = function () { out.textContent = new Intl.NumberFormat('vi-VN').format(range.value) + ' ₫'; };
    range.addEventListener('input', update);
    update();
  }

  // ---------- Auto-submit selects ----------
  document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
    el.addEventListener('change', function () { el.form.submit(); });
  });

  // ---------- Confirm modal ----------
  var confirmModalEl = document.getElementById('bhConfirmModal');
  if (confirmModalEl && window.bootstrap) {
    var confirmModal = new bootstrap.Modal(confirmModalEl);
    var pendingForm = null;
    document.addEventListener('submit', function (event) {
      var form = event.target;
      if (!form.hasAttribute('data-confirm') || form.dataset.confirmed === '1') { return; }
      event.preventDefault();
      pendingForm = form;
      confirmModalEl.querySelector('[data-confirm-message]').textContent = form.getAttribute('data-confirm');
      confirmModal.show();
    });
    confirmModalEl.querySelector('[data-confirm-ok]').addEventListener('click', function () {
      if (pendingForm) {
        pendingForm.dataset.confirmed = '1';
        confirmModal.hide();
        HTMLFormElement.prototype.submit.call(pendingForm);
      }
    });
  }

  // ---------- Image preview (admin) ----------
  document.querySelectorAll('input[type=file][data-preview]').forEach(function (input) {
    var target = document.querySelector(input.getAttribute('data-preview'));
    input.addEventListener('change', function () {
      if (!target) { return; }
      target.innerHTML = '';
      Array.prototype.forEach.call(input.files, function (file) {
        var img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = 'Xem trước';
        img.className = 'bh-thumb me-2 mb-2';
        img.style.width = '84px';
        img.style.height = '84px';
        target.appendChild(img);
      });
    });
  });

  // ---------- Chatbot ----------
  var chat = document.getElementById('bhChat');
  if (chat) {
    var body = chat.querySelector('.bh-chat__body');
    document.getElementById('bhChatToggle').addEventListener('click', function () { chat.classList.toggle('is-open'); });
    chat.querySelector('[data-chat-close]').addEventListener('click', function () { chat.classList.remove('is-open'); });

    var addMsg = function (text, who) {
      var div = document.createElement('div');
      div.className = 'bh-chat__msg bh-chat__msg--' + who;
      div.textContent = text;
      body.appendChild(div);
      body.scrollTop = body.scrollHeight;
      return div;
    };

    chat.querySelector('form').addEventListener('submit', function (event) {
      event.preventDefault();
      var form = event.target;
      var dish = form.dish.value.trim();
      if (!dish) { return; }
      addMsg('Mình muốn làm ' + dish + (form.budget.value ? ', ngân sách ' + form.budget.value + '₫' : ''), 'user');
      var typing = addMsg('Đang tìm nguyên liệu…', 'bot');
      post('/api/chatbot/suggest', { dish: dish, budget: form.budget.value }).then(function (res) {
        typing.textContent = res.message;
        (res.products || []).forEach(function (p) {
          var item = document.createElement('div');
          item.className = 'bh-chat__item';
          item.innerHTML = '<img alt=""><div class="flex-grow-1"><a class="fw-semibold d-block small"></a>' +
            '<span class="bh-price small"></span></div>' +
            '<button type="button" class="bh-btn bh-btn--primary bh-btn--sm" title="Thêm vào giỏ" aria-label="Thêm vào giỏ"><i class="bi bi-cart-plus"></i></button>';
          item.querySelector('img').src = p.image;
          var link = item.querySelector('a');
          link.textContent = p.name;
          link.href = '/products/' + p.slug;
          item.querySelector('.bh-price').textContent = p.price + ' / ' + p.unit;
          item.querySelector('button').setAttribute('data-add-to-cart', p.id);
          body.appendChild(item);
        });
        if (res.products && res.products.length) { addMsg('Tổng ước tính: ' + res.total, 'bot'); }
        body.scrollTop = body.scrollHeight;
      }).catch(function () { typing.textContent = 'Xin lỗi, mình đang gặp sự cố. Bạn thử lại sau nhé.'; });
      form.dish.value = '';
    });
  }
})();
