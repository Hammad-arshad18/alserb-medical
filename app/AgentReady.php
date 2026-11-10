<?php
declare(strict_types=1);

/**
 * Agent-facing discovery: llms.txt, Markdown negotiation, MCP card/endpoint.
 */
final class AgentReady
{
    public const CONTENT_SIGNAL = 'search=yes, ai-input=yes, ai-train=no';

    public static function emitCommonHeaders(): void
    {
        header('Content-Signal: ' . self::CONTENT_SIGNAL);
        header('Vary: Accept', false);
    }

    /**
     * RFC 8288 discovery links for agents (llms.txt, markdown, MCP card).
     */
    public static function emitLinkHeaders(?string $markdownUrl = null): void
    {
        $base = app_url();
        $parts = [
            '<' . $base . '/llms.txt>; rel="describedby"; type="text/plain"',
            '<' . $base . '/.well-known/mcp/server-card.json>; rel="mcp-server-card"; type="application/json"',
        ];

        if ($markdownUrl !== null && $markdownUrl !== '') {
            $parts[] = '<' . $markdownUrl . '>; rel="alternate"; type="text/markdown"';
        }

        header('Link: ' . implode(', ', $parts));
    }

    public static function wantsMarkdown(): bool
    {
        return stripos((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'text/markdown') !== false;
    }

    public static function serveMarkdown(string $markdown): never
    {
        // Discovery headers are already emitted by render().
        header('Content-Type: text/markdown; charset=utf-8');
        header('Cache-Control: public, max-age=300');
        echo $markdown;
        if (!str_ends_with($markdown, "\n")) {
            echo "\n";
        }
        exit;
    }

    /** @return list<string> */
    public static function sameAsProfiles(): array
    {
        $urls = [];

        $configured = config('app.same_as', []);
        if (is_array($configured)) {
            foreach ($configured as $url) {
                $url = trim((string) $url);
                if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                    $urls[] = $url;
                }
            }
        }

        $whatsapp = setting('whatsapp');
        if ($whatsapp !== '') {
            $urls[] = whatsapp_href($whatsapp);
        }

        $twitter = trim((string) config('app.twitter', ''));
        if ($twitter !== '') {
            $handle = ltrim($twitter, '@');
            if ($handle !== '') {
                $urls[] = 'https://x.com/' . rawurlencode($handle);
            }
        }

        return array_values(array_unique($urls));
    }

    /** @return list<array{question: string, answer: string}> */
    public static function faqs(): array
    {
        return [
            ['question' => t('faq.q1'), 'answer' => t('faq.a1')],
            ['question' => t('faq.q2'), 'answer' => t('faq.a2')],
            ['question' => t('faq.q3'), 'answer' => t('faq.a3')],
            ['question' => t('faq.q4'), 'answer' => t('faq.a4')],
        ];
    }

    public static function llmsTxt(): void
    {
        self::emitCommonHeaders();
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=3600');

        $base = app_url();
        $name = 'Alserb Medical';
        $lines = [
            '# ' . $name,
            '',
            '> Importer and supplier of new medical equipment for hospitals and clinics in Libya. Based in Benghazi. Catalog browsing, written quotations, and manually confirmed order requests — no online payment.',
            '',
            'Every page listed here is also available as Markdown: send `Accept: text/markdown`, or append `.md` is not required because content negotiation is used on the same URL.',
            '',
            '## Primary pages',
            '',
            '- [Home (English)](' . $base . '/en): Overview, departments, featured equipment, and how supply works.',
            '- [Home (Arabic)](' . $base . '/ar): Arabic version of the homepage.',
            '- [About](' . $base . '/en/about): Company background and supply principles.',
            '- [Equipment catalog](' . $base . '/en/equipment): Browse new medical equipment by department.',
            '- [How supply works](' . $base . '/en/supply): Requirement → quotation → delivery process.',
            '- [Contact / quotation](' . $base . '/en/contact): Request a written quotation.',
            '- [Terms](' . $base . '/en/terms): Ordering, confirmation, and privacy terms.',
            '- [Image credits](' . $base . '/en/credits): Photography attribution.',
            '',
            '## Departments',
            '',
            '- Patient monitoring',
            '- Diagnostics',
            '- Operating theatre',
            '- Laboratory',
            '- Sterilization',
            '- Hospital furniture',
            '',
            '## Agent resources',
            '',
            '- [robots.txt](' . $base . '/robots.txt): Crawl rules and Content-Signal (`' . self::CONTENT_SIGNAL . '`).',
            '- [sitemap.xml](' . $base . '/sitemap.xml): All public pages in Arabic and English.',
            '- [MCP server card](' . $base . '/.well-known/mcp/server-card.json): Discoverable tools for catalog and company info.',
            '- MCP endpoint: `' . $base . '/mcp`',
            '',
            '## Contact',
            '',
            '- Phone: ' . setting('phone'),
            '- Email: ' . setting('email'),
            '- WhatsApp: ' . setting('whatsapp'),
            '- Address: ' . setting('address'),
            '',
        ];

        echo implode("\n", $lines);
    }

    public static function mcpServerCard(): void
    {
        self::emitCommonHeaders();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        header('Access-Control-Allow-Origin: *');

        $base = app_url();
        $card = [
            '$schema' => 'https://static.modelcontextprotocol.io/schemas/mcp-server-card/v1.json',
            'version' => '1.0',
            'protocolVersion' => '2025-06-18',
            'serverInfo' => [
                'name' => 'alserb-medical',
                'title' => 'Alserb Medical',
                'version' => '1.0.0',
            ],
            'description' => 'Public tools for Alserb Medical: company details, equipment departments, and catalog search for new medical equipment in Libya.',
            'iconUrl' => $base . '/assets/img/favicon-180.png',
            'documentationUrl' => $base . '/llms.txt',
            'transport' => [
                'type' => 'streamable-http',
                'endpoint' => $base . '/mcp',
            ],
            'capabilities' => [
                'tools' => new stdClass(),
            ],
            'authentication' => [
                'required' => false,
                'schemes' => [],
            ],
            'tools' => self::toolDefinitions(),
        ];

        echo json_encode($card, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    }

    public static function mcp(): void
    {
        self::emitCommonHeaders();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept, MCP-Protocol-Version, Mcp-Session-Id');
        header('Access-Control-Expose-Headers: MCP-Protocol-Version');
        header('MCP-Protocol-Version: 2025-06-18');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        rate_limit('mcp', 120, 60);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'name' => 'alserb-medical',
                'version' => '1.0.0',
                'protocolVersion' => '2025-06-18',
                'transport' => 'streamable-http',
                'capabilities' => ['tools' => new stdClass()],
                'serverCard' => app_url() . '/.well-known/mcp/server-card.json',
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents('php://input');
        $payload = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;

        if (!is_array($payload)) {
            echo json_encode(self::rpcError(null, -32700, 'Parse error'));
            exit;
        }

        // Batch support: array of messages.
        if (array_is_list($payload)) {
            $responses = [];
            foreach ($payload as $message) {
                if (is_array($message)) {
                    $response = self::handleRpc($message);
                    if ($response !== null) {
                        $responses[] = $response;
                    }
                }
            }
            echo json_encode($responses === [] ? null : $responses, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        $response = self::handleRpc($payload);
        if ($response === null) {
            http_response_code(202);
            exit;
        }

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * @param array<string, mixed> $message
     * @return array<string, mixed>|null
     */
    private static function handleRpc(array $message): ?array
    {
        $id = $message['id'] ?? null;
        $method = (string) ($message['method'] ?? '');
        $params = is_array($message['params'] ?? null) ? $message['params'] : [];

        // Notifications have no id and no response body.
        if (!array_key_exists('id', $message)) {
            return null;
        }

        return match ($method) {
            'initialize' => self::rpcResult($id, [
                'protocolVersion' => '2025-06-18',
                'capabilities' => ['tools' => new stdClass()],
                'serverInfo' => [
                    'name' => 'alserb-medical',
                    'version' => '1.0.0',
                ],
                'instructions' => 'Use these tools to look up Alserb Medical company details and search the medical equipment catalog. Orders and quotations are confirmed manually; this server does not take payments.',
            ]),
            'ping' => self::rpcResult($id, new stdClass()),
            'tools/list' => self::rpcResult($id, ['tools' => self::toolDefinitions()]),
            'tools/call' => self::callTool($id, $params),
            default => self::rpcError($id, -32601, 'Method not found: ' . $method),
        };
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private static function callTool(mixed $id, array $params): array
    {
        $name = (string) ($params['name'] ?? '');
        $arguments = is_array($params['arguments'] ?? null) ? $params['arguments'] : [];

        try {
            $text = match ($name) {
                'get_company_info' => self::toolCompanyInfo(),
                'list_departments' => self::toolListDepartments(),
                'search_equipment' => self::toolSearchEquipment((string) ($arguments['query'] ?? '')),
                default => throw new InvalidArgumentException('Unknown tool: ' . $name),
            };

            return self::rpcResult($id, [
                'content' => [
                    ['type' => 'text', 'text' => $text],
                ],
                'isError' => false,
            ]);
        } catch (Throwable $error) {
            return self::rpcResult($id, [
                'content' => [
                    ['type' => 'text', 'text' => $error->getMessage()],
                ],
                'isError' => true,
            ]);
        }
    }

    /** @return list<array<string, mixed>> */
    private static function toolDefinitions(): array
    {
        return [
            [
                'name' => 'get_company_info',
                'description' => 'Return Alserb Medical contact details, address, languages, and a short company summary.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => new stdClass(),
                    'additionalProperties' => false,
                ],
            ],
            [
                'name' => 'list_departments',
                'description' => 'List active equipment departments/categories in the Alserb Medical catalog.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => new stdClass(),
                    'additionalProperties' => false,
                ],
            ],
            [
                'name' => 'search_equipment',
                'description' => 'Search active catalog products by name or summary. Returns matching equipment with department and product URLs.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'Search terms, for example "ultrasound" or "patient monitor".',
                        ],
                    ],
                    'required' => ['query'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    private static function toolCompanyInfo(): string
    {
        $base = app_url();

        return implode("\n", [
            'Name: Alserb Medical (السرب الطبي)',
            'Summary: Importer and supplier of new medical equipment for hospitals and clinics in Libya.',
            'Phone: ' . setting('phone'),
            'Email: ' . setting('email'),
            'WhatsApp: ' . setting('whatsapp'),
            'Address: ' . setting('address'),
            'Languages: Arabic, English',
            'Website: ' . $base,
            'About: ' . $base . '/en/about',
            'Contact: ' . $base . '/en/contact',
            'Catalog: ' . $base . '/en/equipment',
            'Terms: ' . $base . '/en/terms',
            'Note: No online payment. Orders are confirmed manually after a written quotation.',
        ]);
    }

    private static function toolListDepartments(): string
    {
        $rows = db()->query(
            'SELECT slug, name_en, name_ar, summary_en FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        )->fetchAll();

        if ($rows === []) {
            return 'No active departments.';
        }

        $base = app_url();
        $lines = ['Equipment departments:'];
        foreach ($rows as $row) {
            $lines[] = sprintf(
                '- %s (%s): %s — %s/en/equipment?category=%s',
                $row['name_en'],
                $row['name_ar'],
                $row['summary_en'],
                $base,
                rawurlencode((string) $row['slug'])
            );
        }

        return implode("\n", $lines);
    }

    private static function toolSearchEquipment(string $query): string
    {
        $query = mb_substr(trim($query), 0, 100);
        if ($query === '') {
            throw new InvalidArgumentException('query is required');
        }

        $stmt = db()->prepare(
            'SELECT p.slug, p.name_en, p.name_ar, p.summary_en, c.name_en AS category_en, c.slug AS category_slug'
            . ' FROM products p INNER JOIN categories c ON c.id = p.category_id'
            . ' WHERE p.is_active = 1 AND c.is_active = 1'
            . ' AND (p.name_ar LIKE ? OR p.name_en LIKE ? OR p.summary_ar LIKE ? OR p.summary_en LIKE ?)'
            . ' ORDER BY c.sort_order ASC, p.sort_order ASC, p.id ASC LIMIT 12'
        );
        $like = '%' . $query . '%';
        $stmt->execute([$like, $like, $like, $like]);
        $rows = $stmt->fetchAll();

        if ($rows === []) {
            return 'No equipment matched “' . $query . '”. Try a broader term or browse ' . app_url() . '/en/equipment';
        }

        $base = app_url();
        $lines = ['Matches for “' . $query . '”:'];
        foreach ($rows as $row) {
            $lines[] = sprintf(
                '- %s (%s) — %s — %s/en/equipment/%s',
                $row['name_en'],
                $row['category_en'],
                $row['summary_en'],
                $base,
                rawurlencode((string) $row['slug'])
            );
        }

        return implode("\n", $lines);
    }

    /** @return array<string, mixed> */
    private static function rpcResult(mixed $id, mixed $result): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ];
    }

    /** @return array<string, mixed> */
    private static function rpcError(mixed $id, int $code, string $message): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];
    }

    /**
     * Markdown body for a public view, or null if negotiation should fall through to HTML.
     *
     * @param array<string, mixed> $data
     */
    public static function markdownForView(string $view, array $data): ?string
    {
        return match ($view) {
            'public/home' => self::markdownHome($data),
            'public/about' => self::markdownAbout($data),
            'public/supply' => self::markdownSupply(),
            'public/contact' => self::markdownContact(),
            'public/terms' => self::markdownTerms(),
            'public/equipment' => self::markdownEquipment($data),
            'public/credits' => self::markdownSimple(t('credits.title'), t('meta.credits'), [
                ['label' => t('nav.home'), 'path' => '/'],
                ['label' => t('nav.about'), 'path' => '/about'],
            ]),
            default => null,
        };
    }

    /** @param array<string, mixed> $data */
    private static function markdownHome(array $data): string
    {
        $categories = is_array($data['categories'] ?? null) ? $data['categories'] : [];
        $featured = is_array($data['featured'] ?? null) ? $data['featured'] : [];
        $base = app_url();
        $lang = current_lang();

        $lines = [
            '# ' . t('site.name'),
            '',
            '> ' . t('site.tagline'),
            '',
            t('home.hero.lead'),
            '',
            t('home.hero.note'),
            '',
            '## ' . t('home.categories.title'),
            '',
            t('home.categories.lead'),
            '',
        ];

        foreach ($categories as $category) {
            $lines[] = sprintf(
                '- [%s](%s/%s/equipment?category=%s) — %s',
                bi($category, 'name'),
                $base,
                $lang,
                rawurlencode((string) $category['slug']),
                bi($category, 'summary')
            );
        }

        $lines[] = '';
        $lines[] = '## ' . t('home.featured.title');
        $lines[] = '';
        foreach ($featured as $product) {
            $lines[] = sprintf(
                '- [%s](%s/%s/equipment/%s) — %s',
                bi($product, 'name'),
                $base,
                $lang,
                rawurlencode((string) $product['slug']),
                bi($product, 'summary')
            );
        }

        $lines[] = '';
        $lines[] = '## ' . t('home.supply.title');
        $lines[] = '';
        $lines[] = '1. **' . t('home.step1.title') . '** — ' . t('home.step1.body');
        $lines[] = '2. **' . t('home.step2.title') . '** — ' . t('home.step2.body');
        $lines[] = '3. **' . t('home.step3.title') . '** — ' . t('home.step3.body');
        $lines[] = '';
        $lines[] = '## FAQ';
        $lines[] = '';
        foreach (self::faqs() as $faq) {
            $lines[] = '### ' . $faq['question'];
            $lines[] = '';
            $lines[] = $faq['answer'];
            $lines[] = '';
        }

        $lines[] = '## Links';
        $lines[] = '';
        foreach (
            [
                ['About', '/about'],
                ['Equipment', '/equipment'],
                ['How supply works', '/supply'],
                ['Contact', '/contact'],
                ['Terms', '/terms'],
            ] as $link
        ) {
            $lines[] = '- [' . $link[0] . '](' . $base . '/' . $lang . $link[1] . ')';
        }

        return implode("\n", $lines) . "\n";
    }

    /** @param array<string, mixed> $data */
    private static function markdownAbout(array $data): string
    {
        unset($data);
        $intro = setting('about_intro');

        return implode("\n", [
            '# ' . t('about.title'),
            '',
            t('about.lead', ['city' => setting_city()]),
            '',
            $intro,
            '',
            '## ' . t('about.what.title'),
            '',
            t('about.what.body1'),
            '',
            t('about.what.body2'),
            '',
            '## ' . t('about.how.title'),
            '',
            '1. **' . t('about.step1.title') . '** — ' . t('about.step1.body'),
            '2. **' . t('about.step2.title') . '** — ' . t('about.step2.body'),
            '3. **' . t('about.step3.title') . '** — ' . t('about.step3.body'),
            '4. **' . t('about.step4.title') . '** — ' . t('about.step4.body'),
            '',
            '## ' . t('about.documents.title'),
            '',
            t('about.documents.body'),
            '',
            '- [' . t('nav.contact') . '](' . app_url() . lang_url('/contact') . ')',
            '- [' . t('nav.terms') . '](' . app_url() . lang_url('/terms') . ')',
            '',
        ]);
    }

    private static function markdownSupply(): string
    {
        $lines = [
            '# ' . t('supply.title'),
            '',
            t('supply.lead'),
            '',
            '1. **' . t('supply.step1.title') . '** — ' . t('supply.step1.body'),
            '2. **' . t('supply.step2.title') . '** — ' . t('supply.step2.body'),
            '3. **' . t('supply.step3.title') . '** — ' . t('supply.step3.body'),
            '4. **' . t('supply.step4.title') . '** — ' . t('supply.step4.body'),
            '',
            '## ' . t('supply.note.title'),
            '',
            t('supply.note.body'),
            '',
            '## FAQ',
            '',
        ];

        foreach (self::faqs() as $faq) {
            $lines[] = '### ' . $faq['question'];
            $lines[] = '';
            $lines[] = $faq['answer'];
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private static function markdownContact(): string
    {
        return implode("\n", [
            '# ' . t('contact.title'),
            '',
            t('contact.lead'),
            '',
            '- Phone: ' . setting('phone'),
            '- Email: ' . setting('email'),
            '- WhatsApp: ' . setting('whatsapp'),
            '- Address: ' . setting('address'),
            '',
            t('contact.form.privacy'),
            '',
            '[' . t('contact.form.submit') . '](' . app_url() . lang_url('/contact') . ')',
            '',
        ]);
    }

    private static function markdownTerms(): string
    {
        return implode("\n", [
            '# ' . t('terms.title'),
            '',
            t('terms.lead'),
            '',
            '## ' . t('terms.orders.title'),
            '',
            t('terms.orders.body'),
            '',
            '## ' . t('terms.pricing.title'),
            '',
            t('terms.pricing.body'),
            '',
            '## ' . t('terms.privacy.title'),
            '',
            t('terms.privacy.body'),
            '',
            '## ' . t('terms.site.title'),
            '',
            t('terms.site.body'),
            '',
            '[' . t('nav.contact') . '](' . app_url() . lang_url('/contact') . ')',
            '',
        ]);
    }

    /** @param array<string, mixed> $data */
    private static function markdownEquipment(array $data): string
    {
        $products = is_array($data['products'] ?? null) ? $data['products'] : [];
        $category = is_array($data['category'] ?? null) ? $data['category'] : null;
        $query = (string) ($data['query'] ?? '');
        $title = $category !== null ? bi($category, 'name') : t('equipment.title');

        $lines = [
            '# ' . $title,
            '',
            t('equipment.lead'),
            '',
        ];

        if ($query !== '') {
            $lines[] = t('equipment.results_for', ['query' => $query]);
            $lines[] = '';
        }

        if ($products === []) {
            $lines[] = t('equipment.empty');
        } else {
            foreach ($products as $product) {
                $lines[] = sprintf(
                    '- [%s](%s) — %s',
                    bi($product, 'name'),
                    app_url() . lang_url('/equipment/' . rawurlencode((string) $product['slug'])),
                    bi($product, 'summary')
                );
            }
        }

        $lines[] = '';
        return implode("\n", $lines);
    }

    /** @param list<array{label: string, path: string}> $links */
    private static function markdownSimple(string $title, string $lead, array $links): string
    {
        $lines = ['# ' . $title, '', $lead, '', '## Links', ''];
        foreach ($links as $link) {
            $lines[] = '- [' . $link['label'] . '](' . app_url() . lang_url($link['path']) . ')';
        }
        $lines[] = '';

        return implode("\n", $lines);
    }
}
