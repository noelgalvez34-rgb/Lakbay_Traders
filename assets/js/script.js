// assets/js/script.js
(function () {
    // ---------- Sidebar (mobile) ----------
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebar-backdrop');
    var toggle = document.getElementById('sidebar-toggle');

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (backdrop) backdrop.classList.remove('show');
    }
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            backdrop.classList.toggle('show');
        });
    }
    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    // ---------- Order form (dynamic product rows) ----------
    var wrap = document.getElementById('items');
    if (!wrap) return;

    var tpl = document.getElementById('item-template');
    var addBtn = document.getElementById('add-item');

    function peso(n) {
        return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalc() {
        var total = 0;
        var rows = wrap.querySelectorAll('.item-row');
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var sel = row.querySelector('.product-select');
            var qtyInput = row.querySelector('.qty');
            var opt = sel.options[sel.selectedIndex];
            var price = opt && opt.getAttribute('data-price') ? parseFloat(opt.getAttribute('data-price')) : 0;
            var stock = opt && opt.getAttribute('data-stock') ? parseInt(opt.getAttribute('data-stock'), 10) : 0;
            var qty = parseInt(qtyInput.value, 10) || 0;

            if (sel.value) { qtyInput.max = stock; }
            var sub = price * qty;
            row.querySelector('.price').textContent = peso(price);
            row.querySelector('.stock').textContent = sel.value ? stock : '-';
            row.querySelector('.subtotal').textContent = peso(sub);
            total += sub;
        }
        document.getElementById('grand-total').textContent = peso(total);
    }

    wrap.addEventListener('change', recalc);
    wrap.addEventListener('input', recalc);
    wrap.addEventListener('click', function (ev) {
        var btn = ev.target.closest('.remove-item');
        if (!btn) return;
        if (wrap.querySelectorAll('.item-row').length > 1) {
            btn.closest('.item-row').remove();
            recalc();
        }
    });
    if (addBtn && tpl) {
        addBtn.addEventListener('click', function () {
            wrap.insertAdjacentHTML('beforeend', tpl.innerHTML);
            recalc();
        });
    }
    recalc();
})();
