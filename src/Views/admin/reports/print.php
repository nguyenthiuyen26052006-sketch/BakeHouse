<?php /** @var array $rows */ /** @var array $summary */ /** @var string $search */ /** @var string $month */
$monthLabel = $month !== '' ? implode('/', array_reverse(explode('-', $month))) : 'Tất cả thời gian';
$totalItems = array_sum(array_map(static fn (array $o): int => (int) ($o['total_amount'] ?? 0), $rows));
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Báo cáo đơn hàng — BakeHouse</title>
<style>
  body { font-family: 'Segoe UI', Arial, sans-serif; color: #2b1d16; margin: 2rem; }
  h1 { font-size: 1.4rem; margin: 0 0 .25rem; }
  .bh-print-sub { color: #7a6a5f; margin-bottom: 1.5rem; }
  .bh-print-summary { display: flex; gap: 2rem; margin-bottom: 1.5rem; }
  .bh-print-summary div strong { display: block; font-size: 1.2rem; }
  table { width: 100%; border-collapse: collapse; font-size: .9rem; }
  th, td { border: 1px solid #ddd; padding: .5rem .6rem; text-align: left; }
  thead th { background: #f3e9db; }
  tfoot td { font-weight: 700; background: #faf5ec; }
  .toolbar { margin-bottom: 1.5rem; }
  .toolbar button { padding: .5rem 1.1rem; margin-right: .5rem; border: 0; border-radius: 8px; background: #c9944a; color: #2b1d16; font-weight: 600; cursor: pointer; }
  .toolbar button.outline { background: transparent; border: 1px solid #c9944a; }
  @media print { .toolbar { display: none; } body { margin: 0; } }
</style>
</head>
<body>
  <div class="toolbar">
    <button type="button" onclick="window.print()">In / Lưu PDF</button>
    <button type="button" class="outline" onclick="window.close()">Đóng</button>
  </div>

  <h1>Báo cáo đơn hàng — BakeHouse</h1>
  <p class="bh-print-sub">
    Kỳ báo cáo: <?= e($monthLabel) ?><?= $search !== '' ? ' · Từ khóa: "' . e($search) . '"' : '' ?>
    &nbsp;·&nbsp; Xuất lúc: <?= e(date('d/m/Y H:i')) ?>
  </p>

  <div class="bh-print-summary">
    <div>Tổng số đơn<strong><?= number_format($summary['total_orders'], 0, ',', '.') ?></strong></div>
    <div>Đơn đã giao<strong><?= number_format($summary['delivered_orders'], 0, ',', '.') ?></strong></div>
    <div>Tổng doanh thu<strong><?= e(format_vnd($summary['total_revenue'])) ?></strong></div>
  </div>

  <table>
    <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Số điện thoại</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $order) : ?>
        <tr>
            <td><?= e($order['order_code']) ?></td>
            <td><?= e($order['recipient_name']) ?></td>
            <td><?= e($order['shipping_phone']) ?></td>
            <td><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
            <td><?= e(format_vnd((int) $order['total_amount'])) ?></td>
            <td><?= e(\App\Enums\OrderStatus::from($order['status'])->label()) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($rows === []) : ?><tr><td colspan="6" style="text-align:center;color:#7a6a5f">Không có đơn hàng phù hợp.</td></tr><?php endif; ?>
    </tbody>
    <?php if ($rows !== []) : ?>
        <tfoot><tr><td colspan="4">Tổng cộng</td><td><?= e(format_vnd($totalItems)) ?></td><td></td></tr></tfoot>
    <?php endif; ?>
  </table>

  <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
