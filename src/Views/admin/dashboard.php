<?php
/** @var array $summary */ /** @var array $recentOrders */ /** @var array $activities */ /** @var array $monthly */
/** @var array $daily */ /** @var array $topSelling */ /** @var array $topReviewed */
$kpis = [
    ['Tổng đơn hàng', number_format($summary['total_orders'], 0, ',', '.'), 'bi-receipt', '/admin/orders'],
    ['Đơn hoàn tất', number_format($summary['completed_orders'], 0, ',', '.'), 'bi-bag-check', '/admin/orders?status=delivered'],
    ['Đơn chưa hoàn tất', number_format($summary['in_progress_orders'], 0, ',', '.'), 'bi-hourglass-split', '/admin/orders?status=in_progress'],
    ['Nguyên vật liệu đã bán', number_format($summary['items_sold'], 0, ',', '.'), 'bi-box-seam', '/admin/products'],
    ['Doanh thu', format_vnd($summary['revenue']), 'bi-cash-coin', '/admin/orders?status=delivered'],
    ['Doanh thu trung bình / đơn', format_vnd($summary['average_revenue']), 'bi-graph-up-arrow', '/admin/orders?status=delivered'],
];
?>
<div class="row g-3 mb-4 row-cols-1 row-cols-sm-2 row-cols-xl-3">
    <?php foreach ($kpis as [$label, $value, $icon, $href]) : ?>
        <div class="col"><a class="bh-kpi bh-kpi--link d-flex align-items-center justify-content-between gap-3" href="<?= e($href) ?>">
            <div><div class="bh-kpi__label"><?= e($label) ?></div><div class="bh-kpi__value"><?= e($value) ?></div></div>
            <div class="bh-kpi__icon"><i class="bi <?= e($icon) ?>"></i></div>
        </a></div>
    <?php endforeach; ?>
</div>
<p class="small bh-muted mb-4">Doanh thu được tính trên tổng các mục đơn hàng đã giao thành công.</p>

<div class="row g-4 mb-4">
    <div class="col-lg-7"><div class="bh-panel">
        <div class="bh-panel__title"><i class="bi bi-bar-chart-line"></i><h5 class="mb-0">Doanh thu theo tháng</h5></div>
        <canvas id="chartMonthly" height="130"></canvas>
    </div></div>
    <div class="col-lg-5"><div class="bh-panel">
        <div class="bh-panel__title"><i class="bi bi-graph-up"></i><h5 class="mb-0">Doanh thu 30 ngày gần nhất</h5></div>
        <canvas id="chartDaily" height="190"></canvas>
    </div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6"><div class="bh-panel">
        <div class="bh-panel__title"><i class="bi bi-trophy"></i><h5 class="mb-0">Nguyên vật liệu bán chạy nhất</h5></div>
        <?php if ($topSelling === []) : ?><p class="bh-muted mb-0">Chưa có dữ liệu.</p>
        <?php else : ?>
            <ul class="bh-rank-list">
                <?php foreach ($topSelling as $i => $row) : ?>
                    <li class="bh-rank-item">
                        <span class="bh-rank-item__num"><?= $i + 1 ?></span>
                        <span class="bh-rank-item__name"><?= e($row['product_name']) ?></span>
                        <span class="badge bg-warning text-dark bh-rank-item__badge"><?= (int) $row['qty'] ?> đã bán</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div></div>
    <div class="col-lg-6"><div class="bh-panel">
        <div class="bh-panel__title"><i class="bi bi-star"></i><h5 class="mb-0">Nguyên vật liệu được đánh giá nhiều nhất</h5></div>
        <?php if ($topReviewed === []) : ?><p class="bh-muted mb-0">Chưa có dữ liệu.</p>
        <?php else : ?>
            <ul class="bh-rank-list">
                <?php foreach ($topReviewed as $i => $row) : ?>
                    <li class="bh-rank-item">
                        <span class="bh-rank-item__num"><?= $i + 1 ?></span>
                        <span class="bh-rank-item__name"><?= e($row['name']) ?></span>
                        <span class="badge bg-warning text-dark bh-rank-item__badge"><?= (int) $row['rating_count'] ?> đánh giá · <?= e(number_format((float) $row['rating_avg'], 1)) ?>★</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div></div>
</div>

<div class="row g-4">
    <div class="col-lg-7"><div class="bh-panel">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="bh-panel__title mb-0"><i class="bi bi-receipt"></i><h5 class="mb-0">Đơn hàng gần đây</h5></div>
            <a href="/admin/orders">Xem tất cả</a>
        </div>
        <div class="table-responsive"><table class="table align-middle mb-0 bh-table"><thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Tổng đơn</th><th>Trạng thái</th></tr></thead><tbody>
        <?php foreach ($recentOrders as $order) : ?>
            <tr><td><a href="/admin/orders/<?= (int) $order['id'] ?>"><?= e($order['order_code']) ?></a></td><td><?= e($order['recipient_name']) ?></td><td><?= e(format_vnd((int) $order['total_amount'])) ?></td><td><?= status_badge($order['status']) ?></td></tr>
        <?php endforeach; ?>
        <?php if ($recentOrders === []) : ?><tr><td colspan="4" class="text-center bh-muted">Chưa có đơn hàng.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div></div>
    <div class="col-lg-5"><div class="bh-panel">
        <div class="bh-panel__title"><i class="bi bi-clock-history"></i><h5 class="mb-0">Hoạt động gần đây</h5></div>
        <?php if ($activities === []) : ?><p class="bh-muted mb-0">Chưa có hoạt động.</p>
        <?php else : ?>
            <ul class="bh-timeline">
            <?php foreach ($activities as $activity) : ?>
                <li>Đơn <strong><?= e($activity['order_code']) ?></strong> chuyển sang <?= e(\App\Enums\OrderStatus::from($activity['to_status'])->label()) ?><br><small class="bh-muted"><?= e(date('d/m/Y H:i', strtotime($activity['created_at']))) ?></small></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
  var monthly = <?= json_encode($monthly, JSON_UNESCAPED_UNICODE) ?>;
  var daily = <?= json_encode($daily, JSON_UNESCAPED_UNICODE) ?>;
  var css = getComputedStyle(document.documentElement);
  var caramel = css.getPropertyValue('--bh-color-caramel').trim();
  var rust = css.getPropertyValue('--bh-color-rust').trim();
  var fmt = function (v) { return new Intl.NumberFormat('vi-VN').format(v) + ' ₫'; };
  var opts = { plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return fmt(c.parsed.y); } } } },
               scales: { y: { beginAtZero: true, ticks: { callback: function (v) { return new Intl.NumberFormat('vi-VN').format(v); } } } } };
  new Chart(document.getElementById('chartMonthly'), { type: 'bar',
    data: { labels: Object.keys(monthly), datasets: [{ data: Object.values(monthly), backgroundColor: caramel, borderRadius: 6 }] }, options: opts });
  new Chart(document.getElementById('chartDaily'), { type: 'line',
    data: { labels: Object.keys(daily).map(function (d) { return d.slice(5); }), datasets: [{ data: Object.values(daily), borderColor: rust, backgroundColor: 'rgba(201,148,74,.2)', fill: true, tension: .3 }] }, options: opts });
})();
</script>
