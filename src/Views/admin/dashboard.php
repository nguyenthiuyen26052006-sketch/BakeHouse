<?php
/** @var array $summary */ /** @var array $recentOrders */ /** @var array $activities */ /** @var array $monthly */
/** @var array $daily */ /** @var array $topSelling */ /** @var array $topReviewed */
$kpis = [
    ['Tổng đơn hàng', number_format($summary['total_orders'], 0, ',', '.'), 'bi-receipt'],
    ['Đơn hoàn tất', number_format($summary['completed_orders'], 0, ',', '.'), 'bi-bag-check'],
    ['Đơn đang xử lý', number_format($summary['in_progress_orders'], 0, ',', '.'), 'bi-hourglass-split'],
    ['NVL đã bán', number_format($summary['items_sold'], 0, ',', '.'), 'bi-box-seam'],
    ['Doanh thu', format_vnd($summary['revenue']), 'bi-cash-coin'],
    ['Doanh thu TB / đơn', format_vnd($summary['average_revenue']), 'bi-graph-up-arrow'],
];
$activityLabels = ['pending' => 'chuyển Chờ xác nhận', 'processing' => 'chuyển Đang xử lý', 'shipped' => 'chuyển Đang giao', 'delivered' => 'chuyển Đã giao'];
?>
<div class="row g-3 mb-4 row-cols-1 row-cols-sm-2 row-cols-xl-3">
    <?php foreach ($kpis as [$label, $value, $icon]) : ?>
        <div class="col"><div class="bh-kpi d-flex align-items-center justify-content-between gap-3">
            <div><div class="bh-kpi__label"><?= e($label) ?></div><div class="bh-kpi__value"><?= e($value) ?></div></div>
            <div class="bh-kpi__icon"><i class="bi <?= e($icon) ?>"></i></div>
        </div></div>
    <?php endforeach; ?>
</div>
<p class="small bh-muted">Số liệu tính theo các sản phẩm do bạn tạo. Doanh thu = tổng các mục đơn đã giao.</p>

<div class="row g-4 mb-4">
    <div class="col-lg-7"><div class="bh-panel"><h5>Doanh thu theo tháng</h5><canvas id="chartMonthly" height="130"></canvas></div></div>
    <div class="col-lg-5"><div class="bh-panel"><h5>Doanh thu 30 ngày gần nhất</h5><canvas id="chartDaily" height="190"></canvas></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6"><div class="bh-panel"><h5>Top NVL bán chạy</h5>
        <?php if ($topSelling === []) : ?><p class="bh-muted mb-0">Chưa có dữ liệu.</p><?php endif; ?>
        <ol class="mb-0"><?php foreach ($topSelling as $row) : ?><li class="mb-1"><?= e($row['product_name']) ?> <span class="badge bg-warning text-dark"><?= (int) $row['qty'] ?> đã bán</span></li><?php endforeach; ?></ol></div></div>
    <div class="col-lg-6"><div class="bh-panel"><h5>Top NVL nhiều đánh giá nhất</h5>
        <?php if ($topReviewed === []) : ?><p class="bh-muted mb-0">Chưa có dữ liệu.</p><?php endif; ?>
        <ol class="mb-0"><?php foreach ($topReviewed as $row) : ?><li class="mb-1"><?= e($row['name']) ?> <span class="badge bg-warning text-dark"><?= (int) $row['rating_count'] ?> đánh giá · <?= e(number_format((float) $row['rating_avg'], 1)) ?>★</span></li><?php endforeach; ?></ol></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-7"><div class="bh-panel"><div class="d-flex justify-content-between"><h5>Đơn hàng gần đây</h5><a href="/admin/orders">Xem tất cả</a></div>
        <div class="table-responsive"><table class="table align-middle mb-0 bh-table"><thead><tr><th>Mã đơn</th><th>Khách</th><th>Phần của tôi</th><th>Trạng thái</th></tr></thead><tbody>
        <?php foreach ($recentOrders as $order) : ?>
            <tr><td><a href="/admin/orders/<?= (int) $order['id'] ?>"><?= e($order['order_code']) ?></a></td><td><?= e($order['recipient_name']) ?></td><td><?= e(format_vnd((int) $order['vendor_amount'])) ?></td><td><?= status_badge($order['status']) ?></td></tr>
        <?php endforeach; ?>
        <?php if ($recentOrders === []) : ?><tr><td colspan="4" class="text-center bh-muted">Chưa có đơn hàng.</td></tr><?php endif; ?>
        </tbody></table></div></div></div>
    <div class="col-lg-5"><div class="bh-panel"><h5>Hoạt động gần đây</h5>
        <ul class="list-unstyled mb-0">
        <?php foreach ($activities as $activity) : ?>
            <li class="mb-2"><i class="bi bi-dot"></i> Đơn <strong><?= e($activity['order_code']) ?></strong> <?= e($activityLabels[$activity['to_status']] ?? $activity['to_status']) ?> <small class="bh-muted">· <?= e(date('d/m H:i', strtotime($activity['created_at']))) ?></small></li>
        <?php endforeach; ?>
        <?php if ($activities === []) : ?><li class="bh-muted">Chưa có hoạt động.</li><?php endif; ?>
        </ul></div></div>
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
