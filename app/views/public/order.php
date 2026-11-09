<?php
declare(strict_types=1);

/** @var array<string, mixed> $order */
/** @var list<array<string, mixed>> $lines */
$progressSteps = ['pending', 'accepted', 'processing', 'payment_pending', 'payment_completed', 'shipping', 'delivered', 'completed'];
$currentStep = array_search($order['status'], $progressSteps, true);
?>
<section class="order-hero">
    <div class="shell">
        <p class="eyebrow"><?= e(t('order.title')) ?></p>
        <div class="order-hero__row">
            <div>
                <h1><?= e(t('order.received')) ?></h1>
                <p class="order-hero__ref"><span class="mono"><?= e(t('order.reference')) ?></span> <strong dir="ltr"><?= e($order['reference']) ?></strong></p>
            </div>
            <span class="status-pill status--<?= e($order['status']) ?>"><?= e(order_status_label((string) $order['status'])) ?></span>
        </div>
        <p class="order-hero__note"><?= e(t('order.no_payment')) ?></p>
    </div>
</section>

<?php if ($currentStep !== false): ?>
    <div class="shell order-progress-wrap">
        <ol class="order-progress" aria-label="<?= e(t('order.history')) ?>">
            <?php foreach ($progressSteps as $index => $step): ?>
                <li class="order-progress__step<?= $index <= $currentStep ? ' is-done' : '' ?><?= $index === $currentStep ? ' is-current' : '' ?>">
                    <span class="order-progress__dot" aria-hidden="true"></span>
                    <span><?= e(order_status_label($step)) ?></span>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
<?php elseif (in_array($order['status'], ['rejected', 'cancelled'], true)): ?>
    <div class="shell order-progress-wrap">
        <p class="order-aborted">
            <span class="status-pill status--<?= e($order['status']) ?>"><?= e(order_status_label((string) $order['status'])) ?></span>
            <?= e(t('order.aborted')) ?>
        </p>
    </div>
<?php endif; ?>

<section class="section">
    <div class="shell order-detail">
        <div class="order-main">
            <div class="panel">
                <h2 class="panel__title"><?= e(t('order.items')) ?></h2>
                <?php if ($lines === []): ?>
                    <p class="panel__empty"><?= e(t('order.empty')) ?></p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th><?= e(t('cart.item')) ?></th>
                                    <th class="align-end"><?= e(t('cart.quantity')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lines as $line): ?>
                                    <tr>
                                        <td>
                                            <span class="cell-strong"><?= e(bi($line, 'name')) ?></span>
                                            <span class="cell-sub"><?= e(bi($line, 'category')) ?></span>
                                        </td>
                                        <td class="align-end mono"><?= (int) $line['quantity'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($order['notes'])): ?>
                <div class="panel">
                    <h2 class="panel__title"><?= e(t('order.notes')) ?></h2>
                    <p class="order-note" dir="auto"><?= nl2br(e($order['notes'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="panel">
                <h2 class="panel__title"><?= e(t('order.history')) ?></h2>
                <ol class="timeline">
                    <?php foreach ($history as $entry): ?>
                        <li class="timeline__item">
                            <span class="timeline__dot" aria-hidden="true"></span>
                            <div class="timeline__body">
                                <div class="timeline__head">
                                    <span class="status-pill status--<?= e($entry['status']) ?>"><?= e(order_status_label((string) $entry['status'])) ?></span>
                                    <time class="timeline__date"><bdi dir="ltr"><?= e(format_date($entry['created_at'], 'j M Y, H:i')) ?></bdi></time>
                                </div>
                                <?php if (!empty($entry['note'])): ?>
                                    <p class="timeline__note" dir="auto"><?= nl2br(e($entry['note'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($history === []): ?>
                        <li class="timeline__empty"><?= e(t('order.history.empty')) ?></li>
                    <?php endif; ?>
                </ol>
            </div>
        </div>

        <aside class="order-side">
            <div class="panel">
                <h2 class="panel__title"><?= e(t('order.customer')) ?></h2>
                <dl class="detail-list">
                    <div><dt><?= e(t('contact.form.name')) ?></dt><dd dir="auto"><?= e($order['customer_name']) ?></dd></div>
                    <?php if (!empty($order['organization'])): ?>
                        <div><dt><?= e(t('contact.form.organization')) ?></dt><dd dir="auto"><?= e($order['organization']) ?></dd></div>
                    <?php endif; ?>
                    <div><dt><?= e(t('contact.form.phone')) ?></dt><dd><a href="<?= e(tel_href((string) $order['phone'])) ?>" dir="ltr"><?= e($order['phone']) ?></a></dd></div>
                    <div><dt><?= e(t('contact.form.email')) ?></dt><dd><a href="mailto:<?= e($order['email']) ?>" dir="ltr"><?= e($order['email']) ?></a></dd></div>
                </dl>
            </div>

            <div class="panel">
                <h2 class="panel__title"><?= e(t('order.delivery')) ?></h2>
                <dl class="detail-list">
                    <div><dt><?= e(t('checkout.city')) ?></dt><dd dir="auto"><?= e($order['city']) ?></dd></div>
                    <div><dt><?= e(t('checkout.address')) ?></dt><dd dir="auto"><?= e($order['address']) ?></dd></div>
                </dl>
            </div>

            <div class="panel">
                <h2 class="panel__title"><?= e(t('order.status')) ?></h2>
                <dl class="detail-list">
                    <div><dt><?= e(t('order.placed')) ?></dt><dd><bdi dir="ltr"><?= e(format_date($order['created_at'], 'j M Y, H:i')) ?></bdi></dd></div>
                    <div><dt><?= e(t('order.updated')) ?></dt><dd><bdi dir="ltr"><?= e(format_date($order['updated_at'], 'j M Y, H:i')) ?></bdi></dd></div>
                </dl>
                <p class="summary__note"><?= e(t('order.help')) ?></p>
            </div>
        </aside>
    </div>

    <div class="shell order-actions">
        <a class="btn btn--ghost" href="<?= e(lang_url('/equipment')) ?>"><?= e(t('order.back')) ?></a>
    </div>
</section>
