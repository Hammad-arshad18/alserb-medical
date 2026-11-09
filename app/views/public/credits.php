<?php
declare(strict_types=1);

$groups = [
    t('credits.unsplash') => [
        ['title' => 'Operating theatre', 'author' => 'Unsplash contributor', 'license' => 'Unsplash License', 'license_url' => 'https://unsplash.com/license', 'source' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514'],
        ['title' => 'Hospital laboratory', 'author' => 'Unsplash contributor', 'license' => 'Unsplash License', 'license_url' => 'https://unsplash.com/license', 'source' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67'],
    ],
    t('credits.pexels') => [
        ['title' => 'Operating theatre hero', 'author' => 'Pexels contributor', 'license' => 'Pexels License', 'license_url' => 'https://www.pexels.com/license/', 'source' => 'https://www.pexels.com/photo/36101264/'],
        ['title' => 'Bedside patient monitor', 'author' => 'Pexels contributor', 'license' => 'Pexels License', 'license_url' => 'https://www.pexels.com/license/', 'source' => 'https://www.pexels.com/photo/3845129/'],
        ['title' => 'Central monitoring station', 'author' => 'Pexels contributor', 'license' => 'Pexels License', 'license_url' => 'https://www.pexels.com/license/', 'source' => 'https://www.pexels.com/photo/11198232/'],
        ['title' => 'Diagnostic ultrasound system', 'author' => 'Pexels contributor', 'license' => 'Pexels License', 'license_url' => 'https://www.pexels.com/license/', 'source' => 'https://www.pexels.com/photo/7089029/'],
        ['title' => 'Electrocardiograph system', 'author' => 'Pexels contributor', 'license' => 'Pexels License', 'license_url' => 'https://www.pexels.com/license/', 'source' => 'https://www.pexels.com/photo/9408868/'],
        ['title' => 'Laboratory centrifuge', 'author' => 'Pexels contributor', 'license' => 'Pexels License', 'license_url' => 'https://www.pexels.com/license/', 'source' => 'https://www.pexels.com/photo/6627667/'],
        ['title' => 'Steam sterilizer', 'author' => 'Pexels contributor', 'license' => 'Pexels License', 'license_url' => 'https://www.pexels.com/license/', 'source' => 'https://www.pexels.com/photo/6627665/'],
        ['title' => 'Sterilization cabinet', 'author' => 'Pexels contributor', 'license' => 'Pexels License', 'license_url' => 'https://www.pexels.com/license/', 'source' => 'https://www.pexels.com/photo/8413159/'],
        ['title' => 'Hospital bed', 'author' => 'Pexels contributor', 'license' => 'Pexels License', 'license_url' => 'https://www.pexels.com/license/', 'source' => 'https://www.pexels.com/photo/12081340/'],
        ['title' => 'Examination couch', 'author' => 'Pexels contributor', 'license' => 'Pexels License', 'license_url' => 'https://www.pexels.com/license/', 'source' => 'https://www.pexels.com/photo/7789609/'],
    ],
    t('credits.wikimedia') => [
        ['title' => 'A medical ultrasound linear array probe, scan head, transducer', 'author' => 'Harrison Keely', 'license' => 'CC BY 4.0', 'license_url' => 'https://creativecommons.org/licenses/by/4.0/', 'source' => 'https://commons.wikimedia.org/wiki/File:A_medical_ultrasound_linear_array_probe,_scan_head,_transducer.jpg'],
        ['title' => 'Lampada scialitica', 'author' => 'Glaucolongoni', 'license' => 'CC BY-SA 4.0', 'license_url' => 'https://creativecommons.org/licenses/by-sa/4.0/', 'source' => 'https://commons.wikimedia.org/wiki/File:Lampada_scialitica.jpg'],
    ],
];
?>
<section class="page-head">
    <div class="wrap page-head__inner">
        <p class="eyebrow"><?= e(t('credits.eyebrow')) ?></p>
        <h1><?= e(t('credits.title')) ?></h1>
        <p class="page-head__lead"><?= e(t('credits.lead')) ?></p>
    </div>
</section>

<section class="section">
    <div class="wrap credits">
        <p class="credits__note"><?= e(t('credits.changes')) ?></p>

        <?php foreach ($groups as $groupName => $photos): ?>
            <h2 class="credits__group"><?= e($groupName) ?></h2>
            <ul class="credit-list">
                <?php foreach ($photos as $photo): ?>
                    <li class="credit">
                        <div class="credit__main">
                            <p class="credit__title"><?= e($photo['title']) ?></p>
                            <p class="credit__meta"><?= e($photo['author']) ?> · <a href="<?= e($photo['license_url']) ?>" rel="noopener" target="_blank"><?= e($photo['license']) ?></a></p>
                        </div>
                        <a class="credit__source" href="<?= e($photo['source']) ?>" rel="noopener" target="_blank"><?= e(t('credits.source')) ?>
                            <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>

        <a class="link-arrow" href="<?= e(lang_url('/')) ?>"><?= e(t('credits.back')) ?>
            <svg class="icon-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h9M8.5 3.5 13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
        </a>
    </div>
</section>
