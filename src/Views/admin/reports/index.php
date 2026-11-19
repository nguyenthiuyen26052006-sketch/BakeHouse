<?php /** @var array $rows */ /** @var array $summary */ /** @var \App\Core\Paginator $paginator */
/** @var string $search */ /** @var string $month */
$printUrl = '/admin/reports/print' . (($search !== '' || $month !== '') ? '?' . http_build_query(array_filter(['q' => $search, 'month' => $month])) : ''); ?>
<form method="get" class="row g-2 align-items-end mb-4">
    <div class="col-12 col-md-4"><input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="Tìm theo mã đơn, tên, SĐT…"></div>
    <div class="col-6 col-md-3"><input class="form-control" type="month" name="month" value="<?= e($month) ?>" aria-label="Lọc theo tháng"></div>
    <div class="col-auto"><button class="bh-btn bh-btn--dark" type="submit"><i class="bi bi-search"></i> Lọc</button></div>
    <div class="col-auto"><a class="bh-btn bh-btn--outline" href="<?= e(current_path()) ?>"><i class="bi bi-x-lg"></i> Xóa lọc</a></div>
    <div class="col-auto ms-md-auto"><a class="bh-btn bh-btn--primary" href="<?= e($printUrl) ?>" target="_blank" rel="noopener"><i class="bi bi-printer"></i> In báo cáo</a></div>
</form>

<div class="row g-3 mb-4 row-cols-1 row-cols-sm-3">
    <div class="col"><div class="bh-kpi"><div class="bh-kpi__label">Tổng số đơn</div><div class="bh-kpi__value"><?= number_format($summary['total_orders'], 0, ',', '.') ?></div></div></div>
    <div class="col"><div class="bh-kpi"><div class="bh-kpi__label">Đơn đã giao</div><div class="bh-kpi__value"><?= number_format($summary['delivered_orders'], 0, ',', '.') ?></div></div></div>
    <div class="col"><div class="bh-kpi"><div class="bh-kpi__label">Tổng doanh thu</div><div class="bh-kpi__value"><?= e(format_vnd($summary['total_revenue'])) ?></div></div></div>
</div>

<div class="bh-panel p-0 table-responsive">
    <table class="table table-hover align-middle mb-0 bh-table">
        <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Số điện thoại</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $order) : ?>
            <tr>
                <td><a href="/admin/orders/<?= (int) $order['id'] ?>"><?= e($order['order_code']) ?></a></td>
                <td><?= e($order['recipient_name']) ?></td>
                <td><?= e($order['shipping_phone']) ?></td>
                <td><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                <td><?= e(format_vnd((int) $order['total_amount'])) ?></td>
                <td><?= status_badge($order['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows === []) : ?><tr><td colspan="6" class="text-center bh-muted py-4">Không có đơn hàng phù hợp.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<div class="mt-3"><?= $paginator->render() ?></div>
