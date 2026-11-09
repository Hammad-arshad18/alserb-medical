<?php
declare(strict_types=1);

/**
 * Small SMTP mailer for order notifications.
 *
 * Configure transport in app/config.local.php. The local default points at
 * Mailpit (127.0.0.1:1025). Set enabled to false to turn emails off.
 */
final class Mailer
{
    /**
     * @param array<string, mixed> $order
     * @param list<array<string, mixed>> $lines
     */
    public static function sendOrderEvent(array $order, array $lines, string $event, ?string $note = null): bool
    {
        if (!(bool) config('mail.enabled', false)) {
            return false;
        }

        $lang = in_array($order['lang'] ?? 'en', ['ar', 'en'], true) ? (string) $order['lang'] : 'en';
        $previousLang = $GLOBALS['lang'] ?? 'en';
        $GLOBALS['lang'] = $lang;

        try {
            $status = (string) ($order['status'] ?? 'pending');
            $statusLabel = order_status_label($status);
            $url = order_public_url($order);
            $reference = (string) ($order['reference'] ?? '');
            $name = (string) ($order['customer_name'] ?? '');

            if ($event === 'created') {
                $subject = t('email.subject.created', ['reference' => $reference]);
                $heading = t('email.created.heading');
                $intro = t('email.created.intro');
            } else {
                $subject = t('email.subject.status', ['reference' => $reference, 'status' => $statusLabel]);
                $heading = t('email.status.heading');
                $intro = t('email.status.intro', ['reference' => $reference, 'status' => $statusLabel]);
            }

            $email = [
                'lang' => $lang,
                'dir' => $lang === 'ar' ? 'rtl' : 'ltr',
                'reference' => $reference,
                'status' => $statusLabel,
                'status_key' => $status,
                'customer_name' => $name,
                'url' => $url,
                'heading' => $heading,
                'intro' => $intro,
                'note' => $note,
                'items' => $lines,
                'subject' => $subject,
            ];

            ob_start();
            require APP_ROOT . '/app/views/emails/order.php';
            $html = (string) ob_get_clean();

            $text = self::plainText($email);

            return self::send(
                (string) ($order['email'] ?? ''),
                $subject,
                $html,
                $text,
                (string) config('mail.admin_to', '')
            );
        } catch (Throwable $error) {
            error_log('Mailer: ' . $error->getMessage());

            return false;
        } finally {
            $GLOBALS['lang'] = $previousLang;
        }
    }

    private static function send(string $to, string $subject, string $html, string $text, string $bcc = ''): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('Mailer: invalid recipient address.');
            return false;
        }

        $transport = (string) config('mail.transport', 'smtp');

        try {
            if ($transport === 'mail') {
                return self::sendWithMail($to, $subject, $html, $text);
            }

            return self::sendWithSmtp($to, $subject, $html, $text, $bcc);
        } catch (Throwable $error) {
            error_log('Mailer: ' . $error->getMessage());
            return false;
        }
    }

    private static function sendWithMail(string $to, string $subject, string $html, string $text): bool
    {
        $fromEmail = (string) config('mail.from_email', 'orders@example.com');
        $fromName = (string) config('mail.from_name', 'Alserb Medical');
        $boundary = 'alserb-' . bin2hex(random_bytes(8));

        $headers = [
            'MIME-Version: 1.0',
            'From: ' . self::encodeHeader($fromName) . ' <' . $fromEmail . '>',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        $body = '--' . $boundary . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($text)) . "\r\n"
            . '--' . $boundary . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html)) . "\r\n"
            . '--' . $boundary . "--\r\n";

        return mail($to, self::encodeHeader($subject), $body, implode("\r\n", $headers));
    }

    private static function sendWithSmtp(string $to, string $subject, string $html, string $text, string $bcc): bool
    {
        $host = (string) config('mail.host', '127.0.0.1');
        $port = (int) config('mail.port', 1025);
        $username = (string) config('mail.username', '');
        $password = (string) config('mail.password', '');
        $encryption = strtolower((string) config('mail.encryption', ''));
        $fromEmail = (string) config('mail.from_email', 'orders@example.com');
        $fromName = (string) config('mail.from_name', 'Alserb Medical');

        $remote = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $socket = @stream_socket_client($remote, $errno, $errstr, 12, STREAM_CLIENT_CONNECT);

        if (!is_resource($socket)) {
            throw new RuntimeException('SMTP connection failed: ' . $errstr . ' (' . $errno . ')');
        }

        stream_set_timeout($socket, 12);

        try {
            self::expect($socket, [220]);
            self::command($socket, 'EHLO ' . (gethostname() ?: 'localhost'), [250]);

            if ($encryption === 'tls') {
                self::command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('SMTP STARTTLS failed.');
                }
                self::command($socket, 'EHLO ' . (gethostname() ?: 'localhost'), [250]);
            }

            if ($username !== '') {
                self::command($socket, 'AUTH LOGIN', [334]);
                self::command($socket, base64_encode($username), [334]);
                self::command($socket, base64_encode($password), [235]);
            }

            self::command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            self::command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);

            if ($bcc !== '' && filter_var($bcc, FILTER_VALIDATE_EMAIL) && strcasecmp($bcc, $to) !== 0) {
                self::command($socket, 'RCPT TO:<' . $bcc . '>', [250, 251]);
            }

            self::command($socket, 'DATA', [354]);

            $boundary = 'alserb-' . bin2hex(random_bytes(10));
            $headers = [
                'Date: ' . date(DATE_RFC2822),
                'From: ' . self::encodeHeader($fromName) . ' <' . $fromEmail . '>',
                'To: <' . $to . '>',
                'Subject: ' . self::encodeHeader($subject),
                'Message-ID: <' . bin2hex(random_bytes(8)) . '@' . $host . '>',
                'MIME-Version: 1.0',
                'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            ];

            $data = implode("\r\n", $headers) . "\r\n\r\n"
                . '--' . $boundary . "\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($text)) . "\r\n"
                . '--' . $boundary . "\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($html)) . "\r\n"
                . '--' . $boundary . "--\r\n";

            // Dot-stuffing for SMTP DATA.
            $data = preg_replace('/(^|\r\n)\./', '$1..', $data) ?? $data;

            fwrite($socket, $data . "\r\n.\r\n");
            self::expect($socket, [250]);
            self::command($socket, 'QUIT', [221]);

            return true;
        } finally {
            fclose($socket);
        }
    }

    /** @param resource $socket */
    private static function command($socket, string $command, array $expected): string
    {
        fwrite($socket, $command . "\r\n");

        return self::expect($socket, $expected);
    }

    /**
     * @param resource $socket
     * @param list<int> $expected
     */
    private static function expect($socket, array $expected): string
    {
        $response = '';

        while (($line = fgets($socket, 512)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
        }

        if ($response === '') {
            throw new RuntimeException('Empty SMTP response.');
        }

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expected, true)) {
            throw new RuntimeException('SMTP error: ' . trim($response));
        }

        return $response;
    }

    private static function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    /** @param array<string, mixed> $email */
    private static function plainText(array $email): string
    {
        $lines = [];
        $lines[] = (string) $email['heading'];
        $lines[] = '';
        $lines[] = (string) $email['intro'];
        $lines[] = '';
        $lines[] = t('email.reference') . ': ' . $email['reference'];
        $lines[] = t('email.status') . ': ' . $email['status'];

        if (!empty($email['note'])) {
            $lines[] = '';
            $lines[] = (string) $email['note'];
        }

        $lines[] = '';
        $lines[] = t('email.items') . ':';
        foreach ($email['items'] as $item) {
            $name = bi($item, 'name');
            $lines[] = '- ' . $name . ' × ' . (int) $item['quantity'];
        }

        $lines[] = '';
        $lines[] = t('email.track') . ': ' . $email['url'];
        $lines[] = '';
        $lines[] = t('email.footer');

        return implode("\n", $lines);
    }
}
