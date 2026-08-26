<?php

namespace App\Support;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class AppLog
{
    private const REDACT = [
        'password', 'pass', 'token', 'fcm_token', 'computer_token', 'authorization',
        'cookie', 'secret', 'key', 'cmd', 'cmd_id',
    ];

    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    public static function exception(Throwable $e, array $context = []): void
    {
        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
            return;
        }

        self::write('error', $e->getMessage() !== '' ? $e->getMessage() : $e::class, $context + [
            'exception' => $e::class,
            'file' => $e->getFile().':'.$e->getLine(),
        ]);
    }

    public static function directory(): string
    {
        return storage_path('logs/app');
    }

    public static function todayPath(): string
    {
        return self::directory().'/app-'.now()->format('Y-m-d').'.log';
    }

    private static function write(string $level, string $message, array $context): void
    {
        try {
            $dir = self::directory();
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $row = [
                'ts' => now()->format('Y-m-d H:i:s'),
                'level' => $level,
                'msg' => $message,
                'url' => self::requestUrl(),
                'method' => self::requestMethod(),
                'ip' => self::requestIp(),
                'ctx' => self::redact($context),
            ];

            file_put_contents(
                self::todayPath(),
                json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n",
                FILE_APPEND | LOCK_EX
            );

            error_log('[app] '.$level.' '.$message.(self::requestUrl() !== '' ? ' '.self::requestUrl() : ''));
        } catch (Throwable) {
            // không đệ quy
        }
    }

    private static function requestUrl(): string
    {
        try {
            $request = request();
            if (! $request) {
                return '';
            }
            $url = $request->fullUrl();

            return (string) preg_replace('/([?&])key=[^&]*/i', '$1key=*', $url);
        } catch (Throwable) {
            return '';
        }
    }

    private static function requestMethod(): string
    {
        try {
            return strtoupper((string) request()?->method());
        } catch (Throwable) {
            return '';
        }
    }

    private static function requestIp(): string
    {
        try {
            return (string) request()?->ip();
        } catch (Throwable) {
            return '';
        }
    }

    private static function redact(array $context): array
    {
        $out = [];
        foreach ($context as $k => $v) {
            $key = strtolower((string) $k);
            $hide = false;
            foreach (self::REDACT as $needle) {
                if (str_contains($key, $needle)) {
                    $hide = true;
                    break;
                }
            }
            if ($hide) {
                $out[$k] = '*';

                continue;
            }
            if (is_array($v)) {
                $out[$k] = self::redact($v);

                continue;
            }
            if (is_scalar($v) || $v === null) {
                $out[$k] = $v;

                continue;
            }
            $out[$k] = get_debug_type($v);
        }

        return $out;
    }
}
