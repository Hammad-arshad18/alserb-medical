-- Alserb Medical — schema and seed data
-- Run through bin/install.php rather than importing by hand.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS staff_users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(60) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY staff_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug VARCHAR(120) NOT NULL,
    name_ar VARCHAR(160) NOT NULL,
    name_en VARCHAR(160) NOT NULL,
    summary_ar VARCHAR(400) NOT NULL DEFAULT '',
    summary_en VARCHAR(400) NOT NULL DEFAULT '',
    description_ar TEXT NULL,
    description_en TEXT NULL,
    image VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY categories_slug (slug),
    KEY categories_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    slug VARCHAR(120) NOT NULL,
    name_ar VARCHAR(160) NOT NULL,
    name_en VARCHAR(160) NOT NULL,
    summary_ar VARCHAR(400) NOT NULL DEFAULT '',
    summary_en VARCHAR(400) NOT NULL DEFAULT '',
    description_ar TEXT NULL,
    description_en TEXT NULL,
    image VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY products_slug (slug),
    KEY products_category (category_id, is_active, sort_order),
    CONSTRAINT products_category_fk FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inquiries (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    organization VARCHAR(160) NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(160) NOT NULL,
    product_id INT UNSIGNED NULL,
    equipment_interest VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'closed') NOT NULL DEFAULT 'new',
    lang CHAR(2) NOT NULL DEFAULT 'ar',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY inquiries_status (status, created_at),
    KEY inquiries_product (product_id),
    CONSTRAINT inquiries_product_fk FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    reference VARCHAR(32) NOT NULL,
    token VARCHAR(64) NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    organization VARCHAR(160) NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(160) NOT NULL,
    city VARCHAR(120) NOT NULL,
    address VARCHAR(255) NOT NULL,
    notes TEXT NULL,
    status ENUM('pending', 'accepted', 'processing', 'payment_pending', 'payment_completed', 'shipping', 'delivered', 'completed', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    admin_note TEXT NULL,
    lang CHAR(2) NOT NULL DEFAULT 'ar',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY orders_reference (reference),
    UNIQUE KEY orders_token (token),
    KEY orders_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_status_history (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'accepted', 'processing', 'payment_pending', 'payment_completed', 'shipping', 'delivered', 'completed', 'rejected', 'cancelled') NOT NULL,
    note TEXT NULL,
    internal_note TEXT NULL,
    source ENUM('system', 'staff', 'customer') NOT NULL DEFAULT 'system',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY history_order (order_id, created_at),
    CONSTRAINT history_order_fk FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT history_user_fk FOREIGN KEY (created_by) REFERENCES staff_users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    name_ar VARCHAR(160) NOT NULL,
    name_en VARCHAR(160) NOT NULL,
    category_ar VARCHAR(160) NOT NULL,
    category_en VARCHAR(160) NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY order_items_order (order_id),
    CONSTRAINT order_items_order_fk FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT order_items_product_fk FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
    bucket_key VARCHAR(190) NOT NULL,
    hits INT UNSIGNED NOT NULL DEFAULT 0,
    window_start INT UNSIGNED NOT NULL,
    PRIMARY KEY (bucket_key),
    KEY rate_limits_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    skey VARCHAR(60) NOT NULL,
    value_ar TEXT NULL,
    value_en TEXT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (skey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (id, slug, name_ar, name_en, summary_ar, summary_en, description_ar, description_en, image, sort_order, is_active) VALUES
(1, 'patient-monitoring', 'مراقبة المرضى', 'Patient monitoring', 'أجهزة مراقبة جانب السرير والمراقبة المركزية للأقسام والعناية المركزة.', 'Bedside and central monitoring for wards and intensive care.', 'أجهزة مراقبة للمرضى ومحطات مركزية لمتابعة القراءات في الأقسام والعناية المركزة. ويُتفق على التكوين في عرض السعر.', 'Bedside monitors and central stations for patient observation in wards and intensive care. The configuration is agreed in the quotation.', 'bedside-patient-monitor.jpg', 10, 1),
(2, 'diagnostics', 'التشخيص', 'Diagnostics', 'أجهزة تصوير بالموجات فوق الصوتية وتخطيط للقلب للفحص والتقييم التشخيصي.', 'Ultrasound and electrocardiography equipment for examination and diagnostic assessment.', 'معدات تشخيصية للعيادات والمستشفيات، تشمل التصوير بالموجات فوق الصوتية وتخطيط القلب. وتُؤكَّد التفاصيل في عرض السعر.', 'Diagnostic equipment for clinics and hospitals, including ultrasound and electrocardiography. Details are confirmed in the quotation.', 'diagnostics.jpg', 20, 1),
(3, 'operating-theatre', 'غرف العمليات', 'Operating theatre', 'طاولات عمليات وإضاءة جراحية ومعدات مساندة لغرف العمليات.', 'Operating tables, surgical lights, and supporting equipment for surgical suites.', 'معدات لغرف العمليات الجراحية، تُختار وفق متطلبات الغرفة والاستخدام. وتُؤكَّد متطلبات التركيب في عرض السعر.', 'Equipment for surgical suites, selected against the requirements of the room and its use. Installation requirements are confirmed in the quotation.', 'operating-theatre.jpg', 30, 1),
(4, 'laboratory', 'المختبرات', 'Laboratory', 'أجهزة تحليل وأجهزة طرد مركزي ومعدات للمختبرات السريرية.', 'Analysers, centrifuges, and instruments for clinical and hospital laboratories.', 'معدات للمختبرات السريرية ومختبرات المستشفيات. ويُتفق على نطاق العمل والتكوين في عرض السعر.', 'Equipment for clinical and hospital laboratories. The scope of work and configuration are agreed in the quotation.', 'laboratory.jpg', 40, 1),
(5, 'sterilization', 'التعقيم', 'Sterilization', 'أجهزة تعقيم بالبخار وخزائن تعقيم للأدوات والمواد القابلة لإعادة الاستخدام.', 'Steam sterilizers and cabinets for instruments and reusable items.', 'معدات تعقيم للمرافق الصحية، تُختار بحسب حجم العمل ومتطلبات التركيب. وتُؤكَّد التفاصيل في عرض السعر.', 'Sterilization equipment for health facilities, selected against workload and installation requirements. Details are confirmed in the quotation.', 'steam-sterilizer.jpg', 50, 1),
(6, 'hospital-furniture', 'أثاث المستشفيات', 'Hospital furniture', 'أسرّة مستشفى وأسرّة فحص وأثاث للأقسام وغرف الفحص.', 'Hospital beds, examination couches, and furniture for wards and consulting rooms.', 'أثاث طبي للأقسام وغرف الفحص والاستشارة. ويُؤكَّد التكوين والملحقات في عرض السعر.', 'Medical furniture for wards and examination rooms. Configuration and accessories are confirmed in the quotation.', 'hospital-bed.jpg', 60, 1);

INSERT INTO products (id, category_id, slug, name_ar, name_en, summary_ar, summary_en, description_ar, description_en, image, sort_order, is_active) VALUES
(1, 1, 'bedside-patient-monitor', 'جهاز مراقبة المريض جانب السرير', 'Bedside patient monitor', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'جهاز لمراقبة المريض جانب السرير في الأقسام والعناية المركزة. ويُتفق على حجم الشاشة والقياسات والملحقات في عرض السعر.', 'A bedside monitor for patient observation in wards and intensive care. Screen size, measurements, and accessories are agreed in the quotation.', 'bedside-patient-monitor.jpg', 10, 1),
(2, 1, 'central-monitoring-station', 'محطة مراقبة مركزية', 'Central monitoring station', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'محطة مركزية لعرض بيانات أجهزة المراقبة الجانبية في قسم أو وحدة. ويُؤكَّد عدد الأسرّة المرتبطة وترتيبات التركيب في عرض السعر.', 'A central station for viewing data from bedside monitors across a ward or unit. The number of connected beds and the installation arrangement are confirmed in the quotation.', 'central-monitoring-station.jpg', 20, 1),
(3, 2, 'diagnostic-ultrasound-system', 'جهاز تصوير بالموجات فوق الصوتية', 'Diagnostic ultrasound system', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'جهاز تصوير بالموجات فوق الصوتية للفحوص التشخيصية العامة. ويُؤكَّد اختيار المجس والتكوين في عرض السعر.', 'An ultrasound system for general diagnostic examinations. Probe selection and configuration are confirmed in the quotation.', 'diagnostic-ultrasound-system.jpg', 10, 1),
(4, 2, 'ecg-system', 'جهاز تخطيط القلب الكهربائي', 'Electrocardiograph system', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'جهاز تخطيط القلب لتقييم القلب الروتيني. ويُؤكَّد التكوين والمواد الاستهلاكية في عرض السعر.', 'An ECG system for routine cardiac assessment. Configuration and consumables are confirmed in the quotation.', 'ecg-system.jpg', 20, 1),
(5, 3, 'operating-table', 'طاولة عمليات', 'Operating table', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'طاولة جراحية لغرف العمليات. وتُؤكَّد خيارات الوضعيات والملحقات في عرض السعر.', 'A surgical table for operating theatres. Position options and accessories are confirmed in the quotation.', 'operating-table.jpg', 10, 1),
(6, 3, 'surgical-ceiling-light', 'إضاءة جراحية سقفية', 'Surgical ceiling light', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'إضاءة جراحية مثبتة بالسقف لغرف العمليات. وتُؤكَّد متطلبات التركيب في عرض السعر.', 'A ceiling-mounted surgical light for operating theatres. Installation requirements are confirmed in the quotation.', 'surgical-ceiling-light.jpg', 20, 1),
(7, 4, 'clinical-laboratory-analyser', 'جهاز تحليل للمختبر السريري', 'Clinical laboratory analyser', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'جهاز تحليل لأعمال المختبر السريري الروتينية. وتُؤكَّد قائمة الفحوص والتكوين في عرض السعر.', 'An analyser for routine clinical laboratory work. Test menu and configuration are confirmed in the quotation.', 'clinical-laboratory-analyser.jpg', 10, 1),
(8, 4, 'laboratory-centrifuge', 'جهاز طرد مركزي للمختبر', 'Laboratory centrifuge', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'جهاز طرد مركزي لتحضير العينات في المختبر. وتُؤكَّد السعة وخيارات الدوار في عرض السعر.', 'A centrifuge for sample preparation in the laboratory. Capacity and rotor options are confirmed in the quotation.', 'laboratory-centrifuge.jpg', 20, 1),
(9, 5, 'steam-sterilizer', 'جهاز تعقيم بالبخار', 'Steam sterilizer', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'جهاز تعقيم بالبخار للأدوات والمواد القابلة لإعادة الاستخدام. ويُؤكَّد حجم الحجرة ومتطلبات التركيب في عرض السعر.', 'A steam sterilizer for instruments and reusable items. Chamber size and installation requirements are confirmed in the quotation.', 'steam-sterilizer.jpg', 10, 1),
(10, 5, 'sterilization-cabinet', 'خزانة تعقيم الأدوات', 'Instrument sterilization cabinet', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'خزانة لتعقيم الأدوات وحفظها. وتُؤكَّد السعة والتكوين في عرض السعر.', 'A cabinet for sterilizing and storing instruments. Capacity and configuration are confirmed in the quotation.', 'sterilization-cabinet.jpg', 20, 1),
(11, 6, 'hospital-bed', 'سرير مستشفى', 'Hospital bed', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'سرير مستشفى للأقسام وغرف المرضى. ويُؤكَّد التكوين والملحقات في عرض السعر.', 'A hospital bed for wards and inpatient rooms. Configuration and accessories are confirmed in the quotation.', 'hospital-bed.jpg', 10, 1),
(12, 6, 'examination-couch', 'سرير فحص', 'Examination couch', 'يُورَّد جديداً، ويُثبَّت التكوين في عرض السعر.', 'Supplied new, with configuration confirmed in the quotation.', 'سرير فحص للعيادات وغرف الاستشارة. ويُؤكَّد التنجيد والتكوين في عرض السعر.', 'An examination couch for clinics and consulting rooms. Upholstery and configuration are confirmed in the quotation.', 'examination-couch.jpg', 20, 1);

INSERT INTO settings (skey, value_ar, value_en) VALUES
('phone', '0925100039', '0925100039'),
('email', 'info@example.com', 'info@example.com'),
('whatsapp', '+218 92 510 0039', '+218 92 510 0039'),
('address', 'بنغازي، ليبيا — شارع فينيسيا', 'Benghazi, Libya — Venice Street'),
('city', 'بنغازي، ليبيا', 'Benghazi, Libya'),
('timezone', 'Africa/Tripoli', 'Africa/Tripoli'),
('home_intro', 'يستورد السرب الطبي المعدات الطبية الجديدة ويورّدها للمستشفيات والعيادات في ليبيا. نعمل انطلاقاً من متطلبات مكتوبة، ونؤكد كل التفاصيل في عرض السعر.', 'Alserb Medical imports and supplies new medical equipment for hospitals and clinics across Libya. We work from a written requirement and confirm every detail in the quotation.'),
('about_intro', 'السرب الطبي مستورد ومورّد ليبي للمعدات الطبية الجديدة، مقره بنغازي. نخدم المستشفيات والعيادات والمرافق الطبية التي تحتاج إلى مسار توريد واضح وموثّق.', 'Alserb Medical is a Libyan importer and supplier of new medical equipment, based in Benghazi. We serve hospitals, clinics, and medical facilities that need a clear, documented supply process.');

INSERT INTO inquiries (name, organization, phone, email, product_id, equipment_interest, message, status, lang, created_at) VALUES
('Sample request — Dr. Amina Saleh', 'City Clinic (sample record)', '+218 91 111 2233', 'sample@example.com', 1, 'Bedside patient monitors', 'We are reviewing equipment for a six-bed ward. Please send a quotation for four bedside monitors and tell us what information you need from us.', 'new', 'en', NOW() - INTERVAL 2 DAY),
('طلب تجريبي — د. عمر الفيتوري', 'عيادة النور (سجل تجريبي)', '+218 92 555 7788', 'sample@example.com', NULL, 'أجهزة تعقيم', 'نحتاج إلى عرض سعر لجهاز تعقيم بالبخار لعيادة صغيرة، مع بيان متطلبات التركيب.', 'closed', 'ar', NOW() - INTERVAL 6 DAY);

INSERT INTO orders (reference, token, customer_name, organization, phone, email, city, address, notes, status, admin_note, lang, created_at) VALUES
('ALS-2026-0001', '5f1c9a7b2e4d8c6a1b3f9e7d5c2a8b4f', 'Sample order — Dr. Amina Saleh', 'City Clinic (sample record)', '+218 91 111 2233', 'sample@example.com', 'Benghazi', 'Benghazi, Libya — Venice Street', 'Please confirm availability and delivery for a six-bed ward.', 'pending', NULL, 'en', NOW() - INTERVAL 1 DAY);

INSERT INTO order_items (order_id, product_id, name_ar, name_en, category_ar, category_en, quantity) VALUES
(1, 1, 'جهاز مراقبة المريض جانب السرير', 'Bedside patient monitor', 'مراقبة المرضى', 'Patient monitoring', 4),
(1, 5, 'طاولة عمليات', 'Operating table', 'غرف العمليات', 'Operating theatre', 1);

INSERT INTO order_status_history (order_id, status, note, internal_note, source, created_by, created_at) VALUES
(1, 'pending', 'Order request received and awaiting review.', NULL, 'system', NULL, NOW() - INTERVAL 1 DAY);
