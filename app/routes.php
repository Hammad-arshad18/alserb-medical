<?php
declare(strict_types=1);

/** @var Router $router */

// Public site, language prefix is stripped before dispatch.
$router->get('/', static fn (): mixed => PublicSite::home());
$router->get('/about', static fn (): mixed => PublicSite::about());
$router->get('/equipment', static fn (): mixed => PublicSite::equipment());
$router->get('/equipment/{slug}', static fn (array $params): mixed => PublicSite::product((string) $params['slug']));
$router->get('/supply', static fn (): mixed => PublicSite::supply());
$router->get('/contact', static fn (): mixed => PublicSite::contact());
$router->post('/contact', static fn (): mixed => PublicSite::contact());
$router->get('/credits', static fn (): mixed => PublicSite::credits());
$router->get('/terms', static fn (): mixed => PublicSite::terms());

$router->get('/cart', static fn (): mixed => PublicSite::cart());
$router->post('/cart/add', static fn (): mixed => PublicSite::cartAdd());
$router->post('/cart/update', static fn (): mixed => PublicSite::cartUpdate());
$router->post('/cart/remove', static fn (): mixed => PublicSite::cartRemove());
$router->get('/checkout', static fn (): mixed => PublicSite::checkout());
$router->post('/checkout', static fn (): mixed => PublicSite::checkout());
$router->get('/order/{token}', static fn (array $params): mixed => PublicSite::order((string) $params['token']));
$router->get('/track', static fn (): mixed => PublicSite::track());
$router->post('/track', static fn (): mixed => PublicSite::track());

// Admin.
$router->get('/admin/login', static fn (): mixed => Admin::login());
$router->post('/admin/login', static fn (): mixed => Admin::login());
$router->post('/admin/logout', static fn (): mixed => Admin::logout());

$router->get('/admin', static fn (): mixed => Admin::dashboard());

$router->get('/admin/products', static fn (): mixed => Admin::products());
$router->get('/admin/products/new', static fn (): mixed => Admin::productForm());
$router->post('/admin/products/new', static fn (): mixed => Admin::productForm());
$router->get('/admin/products/{id}/edit', static fn (array $params): mixed => Admin::productForm((int) $params['id']));
$router->post('/admin/products/{id}/edit', static fn (array $params): mixed => Admin::productForm((int) $params['id']));
$router->post('/admin/products/{id}/toggle', static fn (array $params): mixed => Admin::toggleProduct((int) $params['id']));

$router->get('/admin/categories', static fn (): mixed => Admin::categories());
$router->get('/admin/categories/new', static fn (): mixed => Admin::categoryForm());
$router->post('/admin/categories/new', static fn (): mixed => Admin::categoryForm());
$router->get('/admin/categories/{id}/edit', static fn (array $params): mixed => Admin::categoryForm((int) $params['id']));
$router->post('/admin/categories/{id}/edit', static fn (array $params): mixed => Admin::categoryForm((int) $params['id']));
$router->post('/admin/categories/{id}/toggle', static fn (array $params): mixed => Admin::toggleCategory((int) $params['id']));

$router->get('/admin/inquiries', static fn (): mixed => Admin::inquiries());
$router->get('/admin/inquiries/{id}', static fn (array $params): mixed => Admin::inquiry((int) $params['id']));
$router->post('/admin/inquiries/{id}/status', static fn (array $params): mixed => Admin::updateInquiry((int) $params['id']));

$router->get('/admin/orders', static fn (): mixed => Admin::orders());
$router->get('/admin/orders/{id}', static fn (array $params): mixed => Admin::orderView((int) $params['id']));
$router->post('/admin/orders/{id}/status', static fn (array $params): mixed => Admin::updateOrder((int) $params['id']));

$router->get('/admin/settings', static fn (): mixed => Admin::settings());
$router->post('/admin/settings', static fn (): mixed => Admin::settings());
