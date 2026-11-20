/* BakeHouse chatbot: streaming UI (SSE over fetch), safe markdown, product cards, persistence. */
(function () {
  'use strict';

  var root = document.getElementById('bhChat');
  var fab = document.getElementById('bhChatToggle');
  if (!root || !fab) { return; }

  var BH = window.BH || {};
  var STORE_KEY = 'bh_chat_v1';
  var MAX_HISTORY = 10;
  var HINT = '';
  var SUGGESTIONS = [
    { icon: 'bi-cake2', text: 'Mình muốn làm bánh bông lan, ngân sách dưới 300k' },
    { icon: 'bi-cookie', text: 'Mới tập làm cookie thì cần mua gì?' },
    { icon: 'bi-truck', text: 'Phí ship và các hình thức thanh toán?' },
    { icon: 'bi-receipt', text: 'Đơn hàng của mình đến đâu rồi?' }
  ];

  var scroller = document.getElementById('bhChatScroll');
  var list = document.getElementById('bhChatList');
  var jump = document.getElementById('bhChatJump');
  var form = document.getElementById('bhChatForm');
  var input = document.getElementById('bhChatInput');
  var sendBtn = document.getElementById('bhChatSend');
  var statusText = root.querySelector('[data-chat-status]');
  var hintEl = root.querySelector('.bh-chat__hint');
  var expandBtn = root.querySelector('[data-chat-expand]');

  var state = { open: false, expanded: false, messages: [] };
  var busy = false;
  var follow = true;
  var controller = null;
  var coarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
  var mobileMq = window.matchMedia ? window.matchMedia('(max-width: 575.98px)') : { matches: false };
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------------------------------------------------------------- utils */
  function el(tag, cls, html) {
    var n = document.createElement(tag);
    if (cls) { n.className = cls; }
    if (html !== undefined) { n.innerHTML = html; }
    return n;
  }

  function esc(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function save() {
    try {
      sessionStorage.setItem(STORE_KEY, JSON.stringify({
        open: state.open,
        expanded: state.expanded,
        messages: state.messages.slice(-40)
      }));
    } catch (e) { /* storage unavailable: chat still works, just not persisted */ }
  }

  function load() {
    try {
      var raw = sessionStorage.getItem(STORE_KEY);
      if (!raw) { return; }
      var data = JSON.parse(raw);
      if (data && Array.isArray(data.messages)) {
        state.open = !!data.open;
        state.expanded = !!data.expanded;
        state.messages = data.messages.filter(function (m) {
          return m && (m.role === 'user' || m.role === 'assistant') && typeof m.content === 'string';
        });
      }
    } catch (e) { /* ignore corrupt state */ }
  }

  /* ------------------------------------------------- safe markdown (subset) */
  function inline(text) {
    var s = esc(text);
    s = s.replace(/`([^`\n]+)`/g, '<code>$1</code>');
    s = s.replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>');
    s = s.replace(/(^|[\s(])\*([^*\s][^*\n]*?)\*(?=[\s).,!?:;]|$)/g, '$1<em>$2</em>');
    return s;
  }

  function markdown(src) {
    var lines = String(src).replace(/\r/g, '').split('\n');
    var out = [];
    var para = [];
    var listType = null;
    var items = [];

    function flushPara() {
      if (para.length) { out.push('<p>' + para.map(inline).join('<br>') + '</p>'); para = []; }
    }
    function flushList() {
      if (listType) {
        out.push('<' + listType + '>' + items.map(function (i) { return '<li>' + inline(i) + '</li>'; }).join('') + '</' + listType + '>');
        listType = null; items = [];
      }
    }

    lines.forEach(function (line) {
      var bullet = line.match(/^\s*(?:[-*•])\s+(.*)$/);
      var number = line.match(/^\s*\d+[.)]\s+(.*)$/);
      var heading = line.match(/^\s*#{1,6}\s+(.*)$/);
      if (bullet || number) {
        flushPara();
        var type = bullet ? 'ul' : 'ol';
        if (listType && listType !== type) { flushList(); }
        listType = type;
        items.push((bullet || number)[1]);
      } else if (/^\s*$/.test(line)) {
        flushPara(); flushList();
      } else if (heading) {
        flushPara(); flushList();
        out.push('<p><strong>' + inline(heading[1]) + '</strong></p>');
      } else {
        flushList();
        para.push(line);
      }
    });
    flushPara(); flushList();
    return out.join('');
  }

  /* ------------------------------------------------------------ scrolling */
  function atBottom() {
    return scroller.scrollHeight - scroller.scrollTop - scroller.clientHeight < 72;
  }

  function scrollToBottom(smooth) {
    follow = true;
    jump.hidden = true;
    scroller.scrollTo({ top: scroller.scrollHeight, behavior: smooth && !reduceMotion ? 'smooth' : 'auto' });
  }

  function afterGrow() {
    if (follow) { scroller.scrollTop = scroller.scrollHeight; } else if (busy) { jump.hidden = false; }
  }

  scroller.addEventListener('scroll', function () {
    follow = atBottom();
    if (follow) { jump.hidden = true; }
  }, { passive: true });
  jump.addEventListener('click', function () { scrollToBottom(true); });

  /* ------------------------------------------------------------ rendering */
  function clearWelcome() {
    var w = list.querySelector('.bh-welcome');
    if (w) { w.remove(); }
  }

  function renderWelcome() {
    var w = el('div', 'bh-welcome');
    w.innerHTML = '<div class="bh-welcome__logo" aria-hidden="true"><i class="bi bi-stars"></i></div>' +
      '<h3 class="bh-welcome__title">Xin chào! 👋</h3>' +
      '<p class="bh-welcome__sub">Mình là trợ lý BakeHouse. Hãy cho mình biết món bạn muốn làm, mình sẽ gợi ý nguyên liệu và dụng cụ có sẵn trong shop.</p>';
    var chips = el('div', 'bh-chips');
    SUGGESTIONS.forEach(function (s) {
      var b = el('button', 'bh-chip');
      b.type = 'button';
      b.innerHTML = '<i class="bi ' + s.icon + '" aria-hidden="true"></i><span></span>';
      b.querySelector('span').textContent = s.text;
      b.addEventListener('click', function () { send(s.text); });
      chips.appendChild(b);
    });
    w.appendChild(chips);
    list.appendChild(w);
  }

  function addUser(text) {
    clearWelcome();
    var row = el('div', 'bh-msg bh-msg--user');
    var bubble = el('div', 'bh-msg__bubble');
    bubble.textContent = text;
    row.appendChild(bubble);
    list.appendChild(row);
    return row;
  }

  function createAssistant() {
    clearWelcome();
    var row = el('div', 'bh-msg bh-msg--bot');
    row.innerHTML = '<div class="bh-msg__avatar" aria-hidden="true"><i class="bi bi-stars"></i></div>' +
      '<div class="bh-msg__col"><div class="bh-msg__status"></div><div class="bh-msg__text"></div></div>';
    list.appendChild(row);
    return {
      row: row,
      col: row.querySelector('.bh-msg__col'),
      status: row.querySelector('.bh-msg__status'),
      text: row.querySelector('.bh-msg__text')
    };
  }

  function setStatus(view, message) {
    if (!message) { view.status.innerHTML = ''; return; }
    view.status.innerHTML = '<span class="bh-dots" aria-hidden="true"><i></i><i></i><i></i></span><span class="bh-shimmer"></span>';
    view.status.querySelector('.bh-shimmer').textContent = message;
  }

  function copyText(text, btn) {
    function done() {
      btn.innerHTML = '<i class="bi bi-check2"></i> Đã chép';
      setTimeout(function () { btn.innerHTML = '<i class="bi bi-clipboard"></i> Chép'; }, 1600);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(done, function () { /* clipboard blocked */ });
      return;
    }
    var ta = el('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;opacity:0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); done(); } catch (e) { /* unsupported */ }
    ta.remove();
  }

  function addActions(view, text) {
    var bar = el('div', 'bh-msg__actions');
    var copy = el('button', 'bh-msg__action', '<i class="bi bi-clipboard"></i> Chép');
    copy.type = 'button';
    copy.setAttribute('aria-label', 'Sao chép câu trả lời');
    copy.addEventListener('click', function () { copyText(text, copy); });
    bar.appendChild(copy);
    view.col.appendChild(bar);
  }

  /* ----------------------------------------------------------- cart actions */
  function postCart(productId) {
    var body = new FormData();
    body.append('product_id', productId);
    body.append('quantity', 1);
    return fetch('/api/cart/add', {
      method: 'POST',
      body: body,
      credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': BH.csrf || '', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    }).then(function (res) {
      return res.json().catch(function () { return { ok: false, message: 'Có lỗi xảy ra.' }; })
        .then(function (json) { json.status = res.status; return json; });
    });
  }

  function markAdded(btn) {
    btn.classList.add('is-done');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-check-lg"></i><span class="bh-add__label">Đã thêm</span>';
  }

  function addOne(btn, productId) {
    if (btn.disabled) { return Promise.resolve(false); }
    btn.disabled = true;
    btn.innerHTML = '<span class="bh-spin"></span>';
    return postCart(productId).then(function (res) {
      if (res.status === 401) { window.location.href = res.login_url || '/login'; return false; }
      if (!res.ok) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cart-plus"></i><span class="bh-add__label">Thêm</span>';
        if (BH.toast) { BH.toast(res.message || 'Không thể thêm vào giỏ.', 'danger'); }
        return false;
      }
      markAdded(btn);
      if (BH.updateCartBadge && typeof res.count !== 'undefined') { BH.updateCartBadge(res.count); }
      return true;
    }).catch(function () {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-cart-plus"></i><span class="bh-add__label">Thêm</span>';
      if (BH.toast) { BH.toast('Không thể kết nối máy chủ.', 'danger'); }
      return false;
    });
  }

  function renderProducts(view, products, total) {
    if (!products || !products.length) { return; }
    var wrap = el('div', 'bh-products');
    var buttons = [];

    products.forEach(function (p) {
      var card = el('div', 'bh-product');
      card.innerHTML = '<img class="bh-product__img" alt="" loading="lazy">' +
        '<div class="bh-product__info"><a class="bh-product__name"></a><div class="bh-product__price"></div></div>' +
        '<button type="button" class="bh-add"><i class="bi bi-cart-plus"></i><span class="bh-add__label">Thêm</span></button>';
      var img = card.querySelector('img');
      img.src = p.image;
      img.addEventListener('error', function () { img.src = '/assets/img/product-placeholder.svg'; }, { once: true });
      var link = card.querySelector('a');
      link.textContent = p.name;
      link.href = '/products/' + encodeURIComponent(p.slug);
      var price = card.querySelector('.bh-product__price');
      price.textContent = p.price;
      var unit = el('span');
      unit.textContent = ' / ' + p.unit;
      price.appendChild(unit);
      var btn = card.querySelector('button');
      btn.setAttribute('aria-label', 'Thêm ' + p.name + ' vào giỏ hàng');
      btn.addEventListener('click', function () { addOne(btn, p.id); });
      buttons.push({ btn: btn, id: p.id });
      wrap.appendChild(card);
    });

    if (products.length > 1) {
      var sum = el('div', 'bh-products__sum');
      var label = el('span');
      label.innerHTML = products.length + ' sản phẩm · <strong></strong>';
      label.querySelector('strong').textContent = total;
      var all = el('button', 'bh-addall', '<i class="bi bi-bag-plus"></i> Thêm tất cả');
      all.type = 'button';
      all.addEventListener('click', function () {
        all.disabled = true;
        var added = 0;
        buttons.reduce(function (chain, b) {
          return chain.then(function () {
            if (b.btn.disabled) { return null; }
            return addOne(b.btn, b.id).then(function (ok) { if (ok) { added++; } });
          });
        }, Promise.resolve()).then(function () {
          all.disabled = false;
          if (added && BH.toast) { BH.toast('Đã thêm ' + added + ' sản phẩm vào giỏ hàng.', 'success'); }
        });
      });
      sum.appendChild(label);
      sum.appendChild(all);
      wrap.appendChild(sum);
    }
    view.col.appendChild(wrap);
  }

  function renderStoredAssistant(m) {
    var view = createAssistant();
    view.text.innerHTML = markdown(m.content);
    renderProducts(view, m.products, m.total);
    if (m.content) { addActions(view, m.content); }
  }

  function renderAll() {
    list.innerHTML = '';
    if (!state.messages.length) { renderWelcome(); return; }
    state.messages.forEach(function (m) {
      if (m.role === 'user') { addUser(m.content); } else { renderStoredAssistant(m); }
    });
    list.querySelectorAll('.bh-msg').forEach(function (n) { n.style.animation = 'none'; });
  }

  /* ------------------------------------------------------------- streaming */
  function parseEvent(block) {
    var event = 'message';
    var data = [];
    block.split('\n').forEach(function (line) {
      if (line.indexOf('event:') === 0) { event = line.slice(6).trim(); }
      else if (line.indexOf('data:') === 0) { data.push(line.slice(5).replace(/^ /, '')); }
    });
    if (!data.length) { return null; }
    try { return { event: event, data: JSON.parse(data.join('\n')) }; } catch (e) { return null; }
  }

  function requestBody(history) {
    var body = new FormData();
    body.append('messages', JSON.stringify(history));
    return body;
  }

  function headers(accept) {
    return { 'X-CSRF-TOKEN': BH.csrf || '', 'X-Requested-With': 'XMLHttpRequest', 'Accept': accept };
  }

  /** Reads the SSE stream; resolves when it ends. Throws {http,message} for plain JSON errors, {fallback:true} if unsupported. */
  function streamChat(history, onEvent, signal) {
    if (!window.fetch || !window.ReadableStream || !window.TextDecoder) { return Promise.reject({ fallback: true }); }
    return fetch('/api/chatbot/stream', {
      method: 'POST', body: requestBody(history), headers: headers('text/event-stream'), credentials: 'same-origin', signal: signal
    }).then(function (res) {
      var type = res.headers.get('Content-Type') || '';
      if (!res.ok || type.indexOf('text/event-stream') === -1) {
        return res.json().catch(function () { return {}; }).then(function (json) {
          throw { http: res.status, message: json.message };
        });
      }
      if (!res.body || !res.body.getReader) { throw { fallback: true }; }
      var reader = res.body.getReader();
      var decoder = new TextDecoder('utf-8');
      var buffer = '';
      function pump() {
        return reader.read().then(function (chunk) {
          if (chunk.done) { return; }
          buffer += decoder.decode(chunk.value, { stream: true });
          var parts = buffer.split('\n\n');
          buffer = parts.pop();
          parts.forEach(function (part) {
            var evt = parseEvent(part);
            if (evt) { onEvent(evt.event, evt.data); }
          });
          return pump();
        });
      }
      return pump();
    });
  }

  function plainChat(history, signal) {
    return fetch('/api/chatbot/chat', {
      method: 'POST', body: requestBody(history), headers: headers('application/json'), credentials: 'same-origin', signal: signal
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (json) {
        if (!res.ok || !json.ok) { throw { http: res.status, message: json.message }; }
        return json;
      });
    });
  }

  /* Smooth "typewriter" output: tokens arrive in bursts, so release them over animation frames. */
  function Typer(view) {
    this.view = view;
    this.target = '';
    this.shown = 0;
    this.raf = 0;
    this.onDrained = null;
  }
  Typer.prototype.push = function (text) { this.target += text; this.kick(); };
  Typer.prototype.reset = function () { this.target = ''; this.shown = 0; this.paint(); };
  Typer.prototype.replace = function (text) {
    if (text.indexOf(this.target.slice(0, this.shown)) !== 0) { this.shown = text.length; }
    this.target = text;
    this.kick();
  };
  Typer.prototype.kick = function () {
    var self = this;
    if (self.raf) { return; }
    self.raf = requestAnimationFrame(function tick() {
      self.raf = 0;
      var backlog = self.target.length - self.shown;
      if (backlog > 0) {
        var step = reduceMotion ? backlog : Math.max(1, Math.ceil(backlog / 9));
        var next = self.shown + step;
        var code = self.target.charCodeAt(next - 1);
        if (code >= 0xD800 && code <= 0xDBFF) { next++; }
        self.shown = Math.min(next, self.target.length);
        self.paint();
      }
      if (self.target.length > self.shown) { self.raf = requestAnimationFrame(tick); }
      else if (self.onDrained) { var cb = self.onDrained; self.onDrained = null; cb(); }
    });
  };
  Typer.prototype.paint = function () {
    var text = this.target.slice(0, this.shown);
    this.view.text.innerHTML = text ? markdown(text) : '';
    afterGrow();
  };
  Typer.prototype.finish = function (cb) {
    if (this.target.length <= this.shown && !this.raf) { cb(); return; }
    this.onDrained = cb;
    this.kick();
  };
  Typer.prototype.stop = function () {
    if (this.raf) { cancelAnimationFrame(this.raf); this.raf = 0; }
    this.shown = this.target.length;
    this.paint();
  };

  function setBusy(on) {
    busy = on;
    root.classList.toggle('is-busy', on);
    list.setAttribute('aria-busy', on ? 'true' : 'false');
    statusText.textContent = on ? 'Đang trả lời…' : 'Trực tuyến';
    sendBtn.setAttribute('data-state', on ? 'stop' : 'send');
    sendBtn.setAttribute('aria-label', on ? 'Dừng trả lời' : 'Gửi câu hỏi');
    refreshSend();
  }

  function historyForServer() {
    return state.messages.slice(-MAX_HISTORY).map(function (m) {
      var item = { role: m.role, content: m.content };
      if (m.sig) { item.sig = m.sig; }
      return item;
    });
  }

  function showError(view, message, retryText) {
    view.row.classList.remove('is-streaming');
    setStatus(view, '');
    view.text.innerHTML = '';
    var box = el('div', 'bh-msg__error', '<i class="bi bi-exclamation-circle" aria-hidden="true"></i><span></span>');
    box.querySelector('span').textContent = message;
    if (retryText) {
      var retry = el('button', 'bh-msg__retry', 'Thử lại');
      retry.type = 'button';
      retry.addEventListener('click', function () {
        view.row.remove();
        state.messages.pop(); // drop the failed user turn; send() re-adds it
        list.lastElementChild && list.lastElementChild.classList.contains('bh-msg--user') && list.lastElementChild.remove();
        send(retryText);
      });
      box.appendChild(retry);
    }
    view.col.appendChild(box);
    afterGrow();
  }

  function send(text) {
    text = String(text || '').trim();
    if (!text || busy) { return; }

    state.messages.push({ role: 'user', content: text });
    addUser(text);
    input.value = '';
    autosize();
    scrollToBottom(false);
    save();

    var view = createAssistant();
    view.row.classList.add('is-streaming');
    setStatus(view, 'Đang suy nghĩ…');
    var typer = new Typer(view);
    var started = false;
    var finalPayload = null;
    var failed = false;
    controller = window.AbortController ? new AbortController() : null;
    setBusy(true);
    scrollToBottom(false);

    function onEvent(event, data) {
      if (event === 'status') { setStatus(view, data.text); }
      else if (event === 'delta') {
        if (!started) { started = true; setStatus(view, ''); }
        typer.push(data.text);
      }
      else if (event === 'reset') { started = false; typer.reset(); setStatus(view, 'Đang suy nghĩ…'); }
      else if (event === 'final') { finalPayload = data; }
      else if (event === 'error') { failed = true; showError(view, data.message || 'Có lỗi xảy ra.', text); }
    }

    function complete() {
      setBusy(false);
      controller = null;
      view.row.classList.remove('is-streaming');
      setStatus(view, '');
      if (failed || !finalPayload) { return; }
      var p = finalPayload;
      view.text.innerHTML = markdown(p.message);
      renderProducts(view, p.products, p.total);
      addActions(view, p.message);
      state.messages.push({ role: 'assistant', content: p.message, sig: p.sig, products: p.products, total: p.total });
      save();
      afterGrow();
      if (p.products && p.products.length && follow) { scrollToBottom(true); }
    }

    function finishFlow() {
      if (failed) { setBusy(false); controller = null; return; }
      if (finalPayload) {
        typer.replace(finalPayload.message);
        typer.finish(complete);
      } else {
        // Stream ended without a final event (connection dropped): keep what arrived.
        typer.stop();
        if (typer.target) {
          state.messages.push({ role: 'assistant', content: typer.target });
          addActions(view, typer.target);
          save();
        } else {
          showError(view, 'Kết nối bị gián đoạn. Bạn thử lại nhé.', text);
        }
        setBusy(false);
        controller = null;
        view.row.classList.remove('is-streaming');
      }
    }

    function onFail(err) {
      if (err && err.name === 'AbortError') {
        typer.stop();
        view.row.classList.remove('is-streaming');
        setStatus(view, '');
        if (typer.target) {
          state.messages.push({ role: 'assistant', content: typer.target });
          addActions(view, typer.target);
        } else {
          view.row.remove();
          state.messages.pop();
          list.lastElementChild && list.lastElementChild.classList.contains('bh-msg--user') && list.lastElementChild.remove();
          input.value = text;
          autosize();
        }
        save();
        setBusy(false);
        controller = null;
        return;
      }
      typer.stop();
      var msg = (err && err.message) || 'Không thể kết nối máy chủ. Bạn thử lại nhé.';
      showError(view, msg, err && err.http === 429 ? null : text);
      setBusy(false);
      controller = null;
    }

    var signal = controller ? controller.signal : undefined;
    streamChat(historyForServer(), onEvent, signal).then(finishFlow).catch(function (err) {
      if (err && err.fallback) {
        // Browser cannot read a stream: fall back to the plain JSON endpoint, animated locally.
        plainChat(historyForServer(), signal).then(function (json) {
          finalPayload = json;
          started = true;
          setStatus(view, '');
          typer.push(json.message);
          finishFlow();
        }).catch(onFail);
        return;
      }
      onFail(err);
    });
  }

  /* --------------------------------------------------------------- composer */
  function autosize() {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 128) + 'px';
    var len = input.value.length;
    hintEl.textContent = len >= 420 ? len + '/500 ký tự' : HINT;
    hintEl.hidden = len < 420;
  }

  function refreshSend() {
    sendBtn.disabled = !busy && input.value.trim() === '';
  }

  input.addEventListener('input', function () { autosize(); refreshSend(); });
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey && !e.isComposing && e.keyCode !== 229 && !coarse) {
      e.preventDefault();
      if (!busy) { send(input.value); }
    }
  });
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (busy) { if (controller) { controller.abort(); } return; }
    send(input.value);
  });

  /* ------------------------------------------------------- open/close/chrome */
  function syncViewport() {
    if (!mobileMq.matches || !window.visualViewport) { root.style.removeProperty('--bh-chat-vh'); root.style.top = ''; return; }
    var vv = window.visualViewport;
    root.style.setProperty('--bh-chat-vh', vv.height + 'px');
    root.style.top = vv.offsetTop + 'px';
    if (follow) { scroller.scrollTop = scroller.scrollHeight; }
  }

  function setOpen(open, opts) {
    state.open = open;
    root.classList.toggle('is-open', open);
    fab.setAttribute('aria-expanded', open ? 'true' : 'false');
    fab.setAttribute('aria-label', open ? 'Đóng trợ lý BakeHouse' : 'Mở trợ lý BakeHouse');
    document.documentElement.classList.toggle('bh-chat-lock', open && mobileMq.matches);
    if (open) {
      root.removeAttribute('inert');
      fab.classList.add('is-seen');
      try { sessionStorage.setItem('bh_chat_seen', '1'); } catch (e) { /* ignore */ }
      syncViewport();
      if (state.messages.length) { scrollToBottom(false); } else { scroller.scrollTop = 0; }
      if (!(opts && opts.silent) && !coarse) { setTimeout(function () { input.focus(); }, 60); }
    } else {
      var hadFocus = root.contains(document.activeElement);
      root.setAttribute('inert', '');
      if (hadFocus) { fab.focus(); }
    }
    if (!(opts && opts.silent)) { save(); }
  }

  fab.addEventListener('click', function () { setOpen(!state.open); });
  root.querySelector('[data-chat-close]').addEventListener('click', function () { setOpen(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape' || !state.open) { return; }
    // Leave Escape to open Bootstrap overlays (modal, dropdown, offcanvas) first.
    if (document.querySelector('.modal.show, .offcanvas.show, .dropdown-menu.show')) { return; }
    setOpen(false);
  });

  expandBtn.addEventListener('click', function () {
    state.expanded = !state.expanded;
    root.classList.toggle('is-expanded', state.expanded);
    expandBtn.setAttribute('aria-pressed', state.expanded ? 'true' : 'false');
    expandBtn.innerHTML = '<i class="bi ' + (state.expanded ? 'bi-arrows-angle-contract' : 'bi-arrows-angle-expand') + '"></i>';
    expandBtn.setAttribute('aria-label', state.expanded ? 'Thu nhỏ cửa sổ chat' : 'Mở rộng cửa sổ chat');
    save();
  });

  root.querySelector('[data-chat-new]').addEventListener('click', function () {
    if (controller) { controller.abort(); }
    state.messages = [];
    save();
    renderAll();
    setBusy(false);
    input.focus();
  });

  if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', syncViewport);
    window.visualViewport.addEventListener('scroll', syncViewport);
  }
  if (mobileMq.addEventListener) {
    mobileMq.addEventListener('change', function () {
      document.documentElement.classList.toggle('bh-chat-lock', state.open && mobileMq.matches);
      syncViewport();
    });
  }

  /* ------------------------------------------------------------------ boot */
  load();
  try { if (sessionStorage.getItem('bh_chat_seen')) { fab.classList.add('is-seen'); } } catch (e) { /* ignore */ }
  root.classList.toggle('is-expanded', state.expanded);
  if (state.expanded) {
    expandBtn.setAttribute('aria-pressed', 'true');
    expandBtn.innerHTML = '<i class="bi bi-arrows-angle-contract"></i>';
  }
  renderAll();
  setBusy(false);
  setOpen(state.open, { silent: true });
  if (state.messages.length) { scroller.scrollTop = scroller.scrollHeight; }
})();
