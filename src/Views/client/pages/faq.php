<?php
partial('hero', ['heroTitle' => 'Câu hỏi thường gặp', 'crumbs' => [['Trang chủ', '/'], ['FAQ', null]]]);
$faqs = require BASE_PATH . '/src/Data/faq.php';
?>
<div class="container py-5" style="max-width:820px">
    <div class="accordion" id="bhFaq">
        <?php foreach ($faqs as $i => [$q, $a]) : ?>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button<?= $i ? ' collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>"><?= e($q) ?></button></h2>
                <div id="faq<?= $i ?>" class="accordion-collapse collapse<?= $i ? '' : ' show' ?>" data-bs-parent="#bhFaq"><div class="accordion-body"><?= e($a) ?></div></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
