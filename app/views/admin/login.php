<?php
declare(strict_types=1);

/** @var string|null $error */
/** @var string $username */
?>
<div class="auth-card">
    <div class="auth-card__brand">
        <img class="auth-card__logo" src="<?= e(asset('/assets/img/logo-lockup.png')) ?>" alt="Alserb Medical" width="704" height="176">
    </div>

    <h1 class="auth-card__title">Sign in</h1>
    <p class="auth-card__lead">Staff access to orders, the catalog, quotation requests, and contact settings.</p>

    <?php if ($error !== null): ?>
        <div class="flash flash--error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form class="form" method="post" action="/admin/login">
        <?= csrf_field() ?>

        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus dir="ltr">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required dir="ltr">
        </div>

        <button class="btn btn--primary btn--block" type="submit">Sign in</button>
    </form>

    <a class="auth-card__back" href="/ar">← Back to the public site</a>
</div>
