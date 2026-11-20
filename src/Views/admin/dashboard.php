<?php
/** @var \App\Core\ReportPeriod $period */ /** @var array $summary */ /** @var array $chart */ /** @var array $topSelling */
/** @var array $attention */ /** @var array $recentOrders */
$todo = [
    [$attention['pending_orders'], 'Đơn chờ xử lý', '/admin/orders?status=pending', 'bi-hourglass-split'],
    [$attention['shipping_orders'], 'Đơn đang giao', '/admin/orders?status=shipped', 'bi-truck'],
    [$attention['restock'], 'Sản phẩm cần nhập thêm', '/admin/products?view=restock', 'bi-box-seam'],
    [$attention['unanswered_reviews'], 'Đánh giá chưa phản hồi', '/admin/reviews?reply=none', 'bi-chat-left-text'],
];
$kpis = [
    ['Doanh thu', format_vnd($summary['revenue'])],
    ['Số đơn', number_format($summary['orders'], 0, ',', '.')],
    ['Giá trị TB / đơn', format_vnd($summary['average'])],
    ['Sản phẩm đã bán', number_format($summary['items_sold'], 0, ',', '.')],
];
?>
<div class="adm-head">
    <nav class="adm-period" aria-label="Kỳ báo cáo">
        <?php foreach (\App\Core\ReportPeriod::OPTIONS as $key => $label) : ?>
            <a href="/admin?range=<?= e($key) ?>" class="<?= $period->key === $key ? 'is-active' : '' ?>"<?= $period->key === $key ? ' aria-current="true"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
    <a class="bh-btn bh-btn--outline bh-btn--sm" href="/admin/report?range=<?= e($period->key) ?>" target="_blank" rel="noopener"><i class="bi bi-printer"></i> In báo cáo</a>
</div>

<div class="adm-todo">
    <?php foreach ($todo as [$count, $label, $href, $icon]) : ?>
        <a href="<?= e($href) ?>" class="<?= $count > 0 ? 'is-hot' : '' ?>">
            <span class="adm-todo__n"><?= (int) $count ?></span><span class="adm-todo__t"><?= e($label) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<div class="adm-kpis">
    <?php foreach ($kpis as [$label, $value]) : ?>
        <div class="adm-kpi"><div class="adm-kpi__label"><?= e($label) ?></div><div class="adm-kpi__value"><?= e($value) ?></div></div>
    <?php endforeach; ?>
</div>
<p class="small bh-muted mb-3">Doanh thu và số liệu bán hàng tính trên các đơn đã hoàn tất, theo ngày đặt — <?= e($period->label()) ?>.</p>

<div class="adm-grid">
    <section class="adm-card">
        <div class="adm-card__head"><h2>Doanh thu · <?= e($period->label()) ?></h2></div>
        <div class="adm-chart"><canvas id="chartRevenue" aria-label="Biểu đồ doanh thu" role="img"></canvas></div>
    </section>
    <section class="adm-card">
        <div class="adm-card__head"><h2>Sản phẩm bán chạy</h2></div>
        <?php if ($topSelling === []) : ?><p class="bh-muted mb-0">Chưa có đơn hoàn tất trong kỳ này.</p>
        <?php else : ?>
            <ol class="adm-rank">
                <?php foreach ($topSelling as $i => $row) : ?>
                    <li><span class="adm-rank__n"><?= $i + 1 ?></span><span class="adm-rank__name"><?= e($row['product_name']) ?></span>
                        <span class="adm-rank__val"><strong><?= (int) $row['qty'] ?> đã bán</strong><?= e(format_vnd((int) $row['revenue'])) ?></span></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>
</div>

<section class="adm-card adm-card--flush">
    <div class="adm-card__head px-3 pt-3 mb-0"><h2>Đơn hàng gần đây</h2><a href="/admin/orders">Xem tất cả</a></div>
    <table class="table adm-table mt-2 d-none d-md-table">
        <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th class="adm-num">Tổng đơn</th><th>Trạng thái</th></tr></thead>
        <tbody>
        <?php foreach ($recentOrders as $order) : ?>
            <tr>
                <td><a class="adm-name" href="/admin/orders/<?= (int) $order['id'] ?>"><?= e($order['order_code']) ?></a></td>
                <td><?= e($order['recipient_name']) ?></td>
                <td><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                <td class="adm-num"><?= e(format_vnd((int) $order['total_amount'])) ?></td>
                <td><?= status_badge($order['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <ul class="adm-olist d-md-none">
        <?php foreach ($recentOrders as $order) : ?>
            <li><a href="/admin/orders/<?= (int) $order['id'] ?>">
                <span><strong><?= e($order['order_code']) ?></strong> · <?= e($order['recipient_name']) ?></span><?= status_badge($order['status']) ?>
                <small><?= e(date('d/m H:i', strtotime($order['created_at']))) ?></small><strong><?= e(format_vnd((int) $order['total_amount'])) ?></strong>
            </a></li>
        <?php endforeach; ?>
    </ul>
    <?php if ($recentOrders === []) : ?><div class="adm-empty">Chưa có đơn hàng.</div><?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
  var data = <?= json_encode($chart['series'], JSON_UNESCAPED_UNICODE) ?>;
  var byDay = <?= json_encode($chart['bucket'] === 'day') ?>;
  var css = getComputedStyle(document.documentElement);
  var fmt = function (v) { return new Intl.NumberFormat('vi-VN').format(v) + ' ₫'; };
  var label = function (k) { var p = k.split('-'); return byDay ? p[2] + '/' + p[1] : p[1] + '/' + p[0]; };
  new Chart(document.getElementById('chartRevenue'), {
    type: 'bar',
    data: { labels: Object.keys(data).map(label), datasets: [{ data: Object.values(data), backgroundColor: css.getPropertyValue('--bh-color-caramel').trim(), borderRadius: 4, maxBarThickness: 28 }] },
    options: {
      maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return fmt(c.parsed.y); } } } },
      scales: {
        x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } },
        y: { beginAtZero: true, ticks: { callback: function (v) { return v >= 1e6 ? (v / 1e6) + ' tr' : (v >= 1e3 ? (v / 1e3) + 'k' : v); } } }
      }
    }
  });
})();
</script>
