<?php
declare(strict_types=1);

/**
 * English-only admin panel.
 */
final class Admin
{
    public static function login(): void
    {
        if (admin_user() !== null) {
            redirect('/admin');
        }

        $error = null;
        $username = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            rate_limit('login', 8, 300);
            verify_csrf();

            $username = post_string('username');
            $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

            $stmt = db()->prepare('SELECT id, username, password_hash FROM staff_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user !== false && password_verify($password, (string) $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int) $user['id'];
                redirect('/admin');
            }

            sleep(1);
            $error = 'The username or password is incorrect.';
        }

        render_admin('admin/login', [
            'metaTitle' => 'Sign in',
            'minimal' => true,
            'error' => $error,
            'username' => $username,
        ]);
    }

    public static function logout(): void
    {
        require_admin();
        verify_csrf();

        unset($_SESSION['admin_id']);
        session_regenerate_id(true);

        flash('admin_error', 'You have been signed out.');
        redirect('/admin/login');
    }

    public static function dashboard(): void
    {
        require_admin();

        $counts = [
            'categories' => (int) db()->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
            'products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
            'active_products' => (int) db()->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn(),
            'pending_orders' => (int) db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
            'orders' => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'new_inquiries' => (int) db()->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn(),
        ];

        $recentOrders = db()->query(
            'SELECT o.*, (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) AS unit_count'
            . ' FROM orders o ORDER BY o.created_at DESC, o.id DESC LIMIT 5'
        )->fetchAll();

        $recent = db()->query(
            'SELECT i.*, p.slug AS product_slug, p.name_ar AS product_name_ar, p.name_en AS product_name_en'
            . ' FROM inquiries i LEFT JOIN products p ON p.id = i.product_id'
            . ' ORDER BY i.created_at DESC, i.id DESC LIMIT 5'
        )->fetchAll();

        render_admin('admin/dashboard', [
            'metaTitle' => 'Dashboard',
            'active' => 'dashboard',
            'counts' => $counts,
            'recentOrders' => $recentOrders,
            'recent' => $recent,
        ]);
    }

    public static function products(): void
    {
        require_admin();

        $query = mb_substr(get_string('q'), 0, 120);
        $sql = 'SELECT p.*, c.name_ar AS category_name_ar, c.name_en AS category_name_en, c.image AS category_image'
            . ' FROM products p INNER JOIN categories c ON c.id = p.category_id';
        $params = [];

        if ($query !== '') {
            $sql .= ' WHERE p.name_en LIKE ? OR p.name_ar LIKE ? OR p.slug LIKE ? OR c.name_en LIKE ? OR c.name_ar LIKE ?';
            $like = '%' . $query . '%';
            $params = [$like, $like, $like, $like, $like];
        }

        $sql .= ' ORDER BY c.sort_order ASC, p.sort_order ASC, p.id ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        render_admin('admin/products', [
            'metaTitle' => 'Equipment',
            'active' => 'products',
            'products' => $stmt->fetchAll(),
            'query' => $query,
        ]);
    }

    public static function productForm(?int $id = null): void
    {
        require_admin();

        $categories = db()->query('SELECT * FROM categories ORDER BY sort_order ASC, id ASC')->fetchAll();
        $errors = [];
        $saved = null;

        if ($id !== null) {
            $stmt = db()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $saved = $stmt->fetch();

            if ($saved === false) {
                not_found();
            }
        }

        $product = $saved ?? [
            'id' => null,
            'category_id' => $categories[0]['id'] ?? '',
            'slug' => '',
            'name_ar' => '',
            'name_en' => '',
            'summary_ar' => '',
            'summary_en' => '',
            'description_ar' => '',
            'description_en' => '',
            'image' => null,
            'sort_order' => 0,
            'is_active' => 1,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();

            $product = [
                'id' => $id,
                'category_id' => post_int('category_id'),
                'slug' => $saved['slug'] ?? '',
                'name_ar' => post_string('name_ar'),
                'name_en' => post_string('name_en'),
                'summary_ar' => post_string('summary_ar'),
                'summary_en' => post_string('summary_en'),
                'description_ar' => post_string('description_ar'),
                'description_en' => post_string('description_en'),
                'image' => $saved['image'] ?? null,
                'sort_order' => post_int('sort_order'),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            if ($product['name_ar'] === '') {
                $errors['name_ar'] = 'Enter the Arabic name.';
            }
            if ($product['name_en'] === '') {
                $errors['name_en'] = 'Enter the English name.';
            }
            if (mb_strlen($product['name_ar']) > 160 || mb_strlen($product['name_en']) > 160) {
                $errors['name'] = 'Names are limited to 160 characters.';
            }
            if (mb_strlen($product['summary_ar']) > 400 || mb_strlen($product['summary_en']) > 400) {
                $errors['summary'] = 'Summaries are limited to 400 characters.';
            }

            $categoryIds = array_map(static fn (array $row): int => (int) $row['id'], $categories);
            if (!in_array((int) $product['category_id'], $categoryIds, true)) {
                $errors['category_id'] = 'Choose a department.';
            }

            $product['slug'] = $id !== null
                ? (string) ($saved['slug'] ?? '')
                : unique_slug($product['name_en'] !== '' ? $product['name_en'] : $product['name_ar'], 'products');

            $removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';
            $oldImage = $saved['image'] ?? null;

            if ($errors === []) {
                $uploadError = null;
                $uploaded = handle_image_upload($_FILES['image'] ?? [], $uploadError);

                if ($uploadError !== null) {
                    $errors['image'] = $uploadError;
                } elseif ($uploaded !== null) {
                    $product['image'] = $uploaded;
                } elseif ($removeImage) {
                    $product['image'] = null;
                }
            }

            if ($errors === []) {
                if ($id === null) {
                    $stmt = db()->prepare(
                        'INSERT INTO products (category_id, slug, name_ar, name_en, summary_ar, summary_en, description_ar, description_en, image, sort_order, is_active)'
                        . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        (int) $product['category_id'],
                        $product['slug'],
                        $product['name_ar'],
                        $product['name_en'],
                        $product['summary_ar'],
                        $product['summary_en'],
                        $product['description_ar'],
                        $product['description_en'],
                        $product['image'],
                        (int) $product['sort_order'],
                        (int) $product['is_active'],
                    ]);
                } else {
                    $stmt = db()->prepare(
                        'UPDATE products SET category_id = ?, slug = ?, name_ar = ?, name_en = ?, summary_ar = ?, summary_en = ?,'
                        . ' description_ar = ?, description_en = ?, image = ?, sort_order = ?, is_active = ? WHERE id = ?'
                    );
                    $stmt->execute([
                        (int) $product['category_id'],
                        $product['slug'],
                        $product['name_ar'],
                        $product['name_en'],
                        $product['summary_ar'],
                        $product['summary_en'],
                        $product['description_ar'],
                        $product['description_en'],
                        $product['image'],
                        (int) $product['sort_order'],
                        (int) $product['is_active'],
                        $id,
                    ]);
                }

                if ($oldImage !== null && $oldImage !== $product['image']) {
                    delete_upload($oldImage);
                }

                flash('admin_success', $id === null ? 'Equipment item created.' : 'Equipment item saved.');
                redirect('/admin/products');
            }
        }

        render_admin('admin/product-form', [
            'metaTitle' => $id === null ? 'Add equipment' : 'Edit equipment',
            'active' => 'products',
            'product' => $product,
            'categories' => $categories,
            'errors' => $errors,
        ]);
    }

    public static function toggleProduct(int $id): void
    {
        require_admin();
        verify_csrf();

        $stmt = db()->prepare('UPDATE products SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?');
        $stmt->execute([$id]);

        flash('admin_success', 'Equipment visibility updated.');
        redirect('/admin/products');
    }

    public static function categories(): void
    {
        require_admin();

        $query = mb_substr(get_string('q'), 0, 120);
        $sql = 'SELECT c.*, COUNT(p.id) AS product_count'
            . ' FROM categories c LEFT JOIN products p ON p.category_id = c.id';
        $params = [];

        if ($query !== '') {
            $sql .= ' WHERE c.name_en LIKE ? OR c.name_ar LIKE ? OR c.slug LIKE ?';
            $like = '%' . $query . '%';
            $params = [$like, $like, $like];
        }

        $sql .= ' GROUP BY c.id ORDER BY c.sort_order ASC, c.id ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        render_admin('admin/categories', [
            'metaTitle' => 'Departments',
            'active' => 'categories',
            'categories' => $stmt->fetchAll(),
            'query' => $query,
        ]);
    }

    public static function categoryForm(?int $id = null): void
    {
        require_admin();

        $errors = [];
        $saved = null;

        if ($id !== null) {
            $stmt = db()->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $saved = $stmt->fetch();

            if ($saved === false) {
                not_found();
            }
        }

        $category = $saved ?? [
            'id' => null,
            'slug' => '',
            'name_ar' => '',
            'name_en' => '',
            'summary_ar' => '',
            'summary_en' => '',
            'description_ar' => '',
            'description_en' => '',
            'image' => null,
            'sort_order' => 0,
            'is_active' => 1,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();

            $category = [
                'id' => $id,
                'slug' => $saved['slug'] ?? '',
                'name_ar' => post_string('name_ar'),
                'name_en' => post_string('name_en'),
                'summary_ar' => post_string('summary_ar'),
                'summary_en' => post_string('summary_en'),
                'description_ar' => post_string('description_ar'),
                'description_en' => post_string('description_en'),
                'image' => $saved['image'] ?? null,
                'sort_order' => post_int('sort_order'),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            if ($category['name_ar'] === '') {
                $errors['name_ar'] = 'Enter the Arabic name.';
            }
            if ($category['name_en'] === '') {
                $errors['name_en'] = 'Enter the English name.';
            }
            if (mb_strlen($category['name_ar']) > 160 || mb_strlen($category['name_en']) > 160) {
                $errors['name'] = 'Names are limited to 160 characters.';
            }
            if (mb_strlen($category['summary_ar']) > 400 || mb_strlen($category['summary_en']) > 400) {
                $errors['summary'] = 'Summaries are limited to 400 characters.';
            }

            $category['slug'] = $id !== null
                ? (string) ($saved['slug'] ?? '')
                : unique_slug($category['name_en'] !== '' ? $category['name_en'] : $category['name_ar'], 'categories');

            $removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';
            $oldImage = $saved['image'] ?? null;

            if ($errors === []) {
                $uploadError = null;
                $uploaded = handle_image_upload($_FILES['image'] ?? [], $uploadError);

                if ($uploadError !== null) {
                    $errors['image'] = $uploadError;
                } elseif ($uploaded !== null) {
                    $category['image'] = $uploaded;
                } elseif ($removeImage) {
                    $category['image'] = null;
                }
            }

            if ($errors === []) {
                if ($id === null) {
                    $stmt = db()->prepare(
                        'INSERT INTO categories (slug, name_ar, name_en, summary_ar, summary_en, description_ar, description_en, image, sort_order, is_active)'
                        . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        $category['slug'],
                        $category['name_ar'],
                        $category['name_en'],
                        $category['summary_ar'],
                        $category['summary_en'],
                        $category['description_ar'],
                        $category['description_en'],
                        $category['image'],
                        (int) $category['sort_order'],
                        (int) $category['is_active'],
                    ]);
                } else {
                    $stmt = db()->prepare(
                        'UPDATE categories SET slug = ?, name_ar = ?, name_en = ?, summary_ar = ?, summary_en = ?,'
                        . ' description_ar = ?, description_en = ?, image = ?, sort_order = ?, is_active = ? WHERE id = ?'
                    );
                    $stmt->execute([
                        $category['slug'],
                        $category['name_ar'],
                        $category['name_en'],
                        $category['summary_ar'],
                        $category['summary_en'],
                        $category['description_ar'],
                        $category['description_en'],
                        $category['image'],
                        (int) $category['sort_order'],
                        (int) $category['is_active'],
                        $id,
                    ]);
                }

                if ($oldImage !== null && $oldImage !== $category['image']) {
                    delete_upload($oldImage);
                }

                flash('admin_success', $id === null ? 'Department created.' : 'Department saved.');
                redirect('/admin/categories');
            }
        }

        render_admin('admin/category-form', [
            'metaTitle' => $id === null ? 'Add department' : 'Edit department',
            'active' => 'categories',
            'category' => $category,
            'errors' => $errors,
        ]);
    }

    public static function toggleCategory(int $id): void
    {
        require_admin();
        verify_csrf();

        $stmt = db()->prepare('UPDATE categories SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?');
        $stmt->execute([$id]);

        flash('admin_success', 'Department visibility updated.');
        redirect('/admin/categories');
    }

    public static function inquiries(): void
    {
        require_admin();

        $query = mb_substr(get_string('q'), 0, 120);
        $sql = 'SELECT i.*, p.slug AS product_slug, p.name_ar AS product_name_ar, p.name_en AS product_name_en'
            . ' FROM inquiries i LEFT JOIN products p ON p.id = i.product_id';
        $params = [];

        if ($query !== '') {
            $sql .= ' WHERE i.name LIKE ? OR i.organization LIKE ? OR i.phone LIKE ? OR i.email LIKE ?'
                . ' OR i.equipment_interest LIKE ? OR i.message LIKE ?';
            $like = '%' . $query . '%';
            $params = [$like, $like, $like, $like, $like, $like];
        }

        $sql .= ' ORDER BY i.created_at DESC, i.id DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        render_admin('admin/inquiries', [
            'metaTitle' => 'Quotation requests',
            'active' => 'inquiries',
            'inquiries' => $stmt->fetchAll(),
            'query' => $query,
        ]);
    }

    public static function inquiry(int $id): void
    {
        require_admin();

        $stmt = db()->prepare(
            'SELECT i.*, p.slug AS product_slug, p.name_ar AS product_name_ar, p.name_en AS product_name_en'
            . ' FROM inquiries i LEFT JOIN products p ON p.id = i.product_id WHERE i.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $inquiry = $stmt->fetch();

        if ($inquiry === false) {
            not_found();
        }

        if ($inquiry['status'] === 'new') {
            $update = db()->prepare("UPDATE inquiries SET status = 'read' WHERE id = ?");
            $update->execute([$id]);
            $inquiry['status'] = 'read';
        }

        render_admin('admin/inquiry', [
            'metaTitle' => 'Request #' . $id,
            'active' => 'inquiries',
            'inquiry' => $inquiry,
        ]);
    }

    public static function updateInquiry(int $id): void
    {
        require_admin();
        verify_csrf();

        $status = post_string('status');
        if (!array_key_exists($status, inquiry_statuses())) {
            flash('admin_error', 'Choose a valid status.');
            redirect('/admin/inquiries/' . $id);
        }

        $stmt = db()->prepare('UPDATE inquiries SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);

        flash('admin_success', 'Request status updated.');
        redirect('/admin/inquiries/' . $id);
    }

    public static function orders(): void
    {
        require_admin();

        $status = get_string('status');
        $query = mb_substr(get_string('q'), 0, 120);
        $allowed = order_statuses();
        $conditions = [];
        $params = [];

        if ($status !== '' && array_key_exists($status, $allowed)) {
            $conditions[] = 'o.status = ?';
            $params[] = $status;
        }

        if ($query !== '') {
            $conditions[] = '(o.reference LIKE ? OR o.customer_name LIKE ? OR o.organization LIKE ? OR o.phone LIKE ?'
                . ' OR o.email LIKE ? OR o.city LIKE ? OR o.address LIKE ? OR o.notes LIKE ?)';
            $like = '%' . $query . '%';
            array_push($params, $like, $like, $like, $like, $like, $like, $like, $like);
        }

        $where = $conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = db()->prepare(
            'SELECT o.*,'
            . ' (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) AS unit_count,'
            . ' (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS line_count'
            . ' FROM orders o' . $where
            . ' ORDER BY o.created_at DESC, o.id DESC'
        );
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        $statusCounts = [];
        foreach (db()->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status') as $row) {
            $statusCounts[(string) $row['status']] = (int) $row['total'];
        }

        render_admin('admin/orders', [
            'metaTitle' => 'Orders',
            'active' => 'orders',
            'orders' => $orders,
            'statusFilter' => $status,
            'statusCounts' => $statusCounts,
            'query' => $query,
        ]);
    }

    public static function orderView(int $id): void
    {
        require_admin();

        $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if ($order === false) {
            not_found();
        }

        render_admin('admin/order', [
            'metaTitle' => 'Order ' . $order['reference'],
            'active' => 'orders',
            'order' => $order,
            'lines' => order_lines($id),
            'history' => order_history($id),
        ]);
    }

    public static function updateOrder(int $id): void
    {
        require_admin();
        verify_csrf();

        $status = post_string('status');
        $note = post_string('admin_note');
        $customerNote = post_string('customer_note');

        if (!array_key_exists($status, order_statuses())) {
            flash('admin_error', 'Choose a valid order status.');
            redirect('/admin/orders/' . $id);
        }

        if (mb_strlen($note) > 2000 || mb_strlen($customerNote) > 1000) {
            flash('admin_error', 'The note is longer than the allowed limit.');
            redirect('/admin/orders/' . $id);
        }

        $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if ($order === false) {
            not_found();
        }

        $statusChanged = $order['status'] !== $status;

        $stmt = db()->prepare('UPDATE orders SET status = ?, admin_note = ? WHERE id = ?');
        $stmt->execute([$status, $note !== '' ? $note : null, $id]);

        if ($statusChanged || $customerNote !== '') {
            try {
                record_order_status(
                    $id,
                    $status,
                    $customerNote !== '' ? $customerNote : null,
                    $note !== '' ? $note : null,
                    'staff',
                    (int) (admin_user()['id'] ?? 0) ?: null
                );
            } catch (Throwable $historyError) {
                error_log('Order history: ' . $historyError->getMessage());
            }
        }

        if ($statusChanged) {
            $order['status'] = $status;
            Mailer::sendOrderEvent($order, order_lines($id), 'status', $customerNote !== '' ? $customerNote : null);
        }

        flash('admin_success', 'Order updated.');
        redirect('/admin/orders/' . $id);
    }

    public static function settings(): void
    {
        require_admin();

        $bilingual = ['address', 'city', 'home_intro', 'about_intro'];
        $single = ['phone', 'email', 'whatsapp', 'timezone'];
        $errors = [];

        $values = [];
        foreach (array_merge($bilingual, $single) as $key) {
            $values[$key] = setting_pair($key);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();

            foreach ($single as $key) {
                $value = post_string($key);
                $values[$key] = ['value_ar' => $value, 'value_en' => $value];
            }

            foreach ($bilingual as $key) {
                $values[$key] = [
                    'value_ar' => post_string($key . '_ar'),
                    'value_en' => post_string($key . '_en'),
                ];
            }

            if ($values['email']['value_en'] !== '' && !filter_var($values['email']['value_en'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Enter a valid email address.';
            }
            if ($values['phone']['value_en'] === '') {
                $errors['phone'] = 'Enter a phone number.';
            }
            if (mb_strlen($values['phone']['value_en']) > 40 || mb_strlen($values['whatsapp']['value_en']) > 40) {
                $errors['phone'] = 'Phone numbers are limited to 40 characters.';
            }
            if (mb_strlen($values['email']['value_en']) > 160) {
                $errors['email'] = 'The email address is limited to 160 characters.';
            }
            if (mb_strlen($values['address']['value_ar']) > 255 || mb_strlen($values['address']['value_en']) > 255) {
                $errors['address'] = 'The address is limited to 255 characters in each language.';
            }
            if (mb_strlen($values['city']['value_ar']) > 80 || mb_strlen($values['city']['value_en']) > 80) {
                $errors['city'] = 'The city is limited to 80 characters in each language.';
            }
            if (!in_array($values['timezone']['value_en'], timezone_identifiers_list(), true)) {
                $errors['timezone'] = 'Enter a valid PHP timezone, for example Africa/Tripoli.';
            }

            foreach (['address', 'city', 'home_intro', 'about_intro'] as $key) {
                if ($values[$key]['value_ar'] === '' || $values[$key]['value_en'] === '') {
                    $errors[$key] = 'Complete both language fields.';
                }
            }

            if ($errors === []) {
                foreach ($single as $key) {
                    self::saveSetting($key, $values[$key]['value_ar'], $values[$key]['value_en']);
                }
                foreach ($bilingual as $key) {
                    self::saveSetting($key, $values[$key]['value_ar'], $values[$key]['value_en']);
                }

                flash('admin_success', 'Contact and page text saved.');
                redirect('/admin/settings');
            }
        }

        render_admin('admin/settings', [
            'metaTitle' => 'Settings',
            'active' => 'settings',
            'values' => $values,
            'errors' => $errors,
            'bilingual' => $bilingual,
            'single' => $single,
        ]);
    }

    private static function saveSetting(string $key, string $valueAr, string $valueEn): void
    {
        $stmt = db()->prepare(
            'INSERT INTO settings (skey, value_ar, value_en) VALUES (?, ?, ?)'
            . ' ON DUPLICATE KEY UPDATE value_ar = VALUES(value_ar), value_en = VALUES(value_en)'
        );
        $stmt->execute([$key, $valueAr, $valueEn]);
    }
}
