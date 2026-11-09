<?php
declare(strict_types=1);

/** @var array<string, mixed> $email */
$phone = setting('phone');
$emailAddress = setting('email');
?>
<!doctype html>
<html lang="<?= e($email['lang']) ?>" dir="<?= e($email['dir']) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($email['subject']) ?></title>
</head>
<body style="margin:0;padding:0;background:#eef3f7;font-family:Arial,Helvetica,sans-serif;color:#041d33;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef3f7;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #d7e4ec;">
                <tr>
                    <td style="padding:22px 28px;border-bottom:3px solid #0663a4;background:#ffffff;">
                        <img src="<?= e(app_url()) ?>/assets/img/logo-lockup.png" width="220" alt="Alserb Medical" style="display:block;border:0;margin-bottom:10px;">
                        <div style="margin-top:4px;font-size:10px;letter-spacing:1.4px;text-transform:uppercase;color:#6f8393;"><?= e(t('site.tagline')) ?></div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px 28px;">
                        <p style="margin:0 0 8px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#0a7fa3;"><?= e(t('order.title')) ?></p>
                        <h1 style="margin:0 0 14px;font-size:24px;line-height:1.25;color:#041d33;font-weight:bold;"><?= e($email['heading']) ?></h1>
                        <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#41576a;"><?= e($email['intro']) ?></p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px;background:#f4f8fb;border-left:3px solid #0663a4;">
                            <tr>
                                <td style="padding:14px 16px;font-size:13px;line-height:1.6;color:#41576a;">
                                    <strong style="color:#041d33;"><?= e(t('order.reference')) ?>:</strong> <span dir="ltr" style="font-family:Consolas,monospace;"><?= e($email['reference']) ?></span><br>
                                    <strong style="color:#041d33;"><?= e(t('order.status')) ?>:</strong> <?= e($email['status']) ?>
                                </td>
                            </tr>
                        </table>

                        <?php if (!empty($email['note'])): ?>
                            <p style="margin:0 0 10px;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#6f8393;"><?= e(t('email.note_label')) ?></p>
                            <p style="margin:0 0 22px;padding:12px 14px;background:#e6f7fc;border-left:3px solid #0aabd2;font-size:13px;line-height:1.6;color:#41576a;" dir="auto"><?= nl2br(e($email['note'])) ?></p>
                        <?php endif; ?>

                        <h2 style="margin:0 0 10px;font-size:14px;color:#041d33;"><?= e(t('email.items')) ?></h2>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;border-top:1px solid #d7e4ec;">
                            <?php foreach ($email['items'] as $item): ?>
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #d7e4ec;font-size:13px;line-height:1.5;color:#041d33;"><?= e(bi($item, 'name')) ?></td>
                                    <td style="padding:10px 0;border-bottom:1px solid #d7e4ec;font-size:13px;line-height:1.5;color:#6f8393;text-align:end;white-space:nowrap;" dir="ltr">× <?= (int) $item['quantity'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 22px;">
                            <tr>
                                <td style="background:#0663a4;">
                                    <a href="<?= e($email['url']) ?>" style="display:inline-block;padding:13px 22px;font-size:13px;font-weight:bold;color:#ffffff;text-decoration:none;"><?= e(t('email.track')) ?></a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 8px;font-size:12px;line-height:1.6;color:#6f8393;"><?= e(t('email.help')) ?></p>
                        <p style="margin:0 0 8px;font-size:12px;line-height:1.6;color:#6f8393;"><?= e(t('email.no_payment')) ?></p>
                        <p style="margin:0;font-size:12px;line-height:1.6;color:#6f8393;">
                            <?php if ($phone !== ''): ?><?= e(t('contact.aside.phone')) ?>: <span dir="ltr"><?= e($phone) ?></span><?php endif; ?>
                            <?php if ($phone !== '' && $emailAddress !== ''): ?> · <?php endif; ?>
                            <?php if ($emailAddress !== ''): ?><?= e($emailAddress) ?><?php endif; ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 28px;border-top:1px solid #d7e4ec;background:#f4f8fb;font-size:11px;line-height:1.6;color:#6f8393;">
                        <?= e(t('email.footer')) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
