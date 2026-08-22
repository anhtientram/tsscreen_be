<?php

namespace App\Support;

final class LegacyJson
{
    /**
     * App Flutter: JSON string + text/html (jsonDecode(response.data)).
     * Swagger Try it out: application/json pretty-print.
     */
    public static function send(array $payload, int $http = 200, bool $forceJson = false)
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        if ($forceJson || self::fromSwagger()) {
            return response()->json(
                $payload,
                $http,
                ['Content-Type' => 'application/json; charset=utf-8'],
                $flags | JSON_PRETTY_PRINT
            );
        }

        return response(
            json_encode($payload, $flags),
            $http,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    public static function fromSwagger(): bool
    {
        $referer = (string) request()->header('Referer', '');

        return str_contains($referer, '/api/documentation')
            || str_contains($referer, 'swagger');
    }

    public static function str(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    public static function date(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return \Carbon\Carbon::parse($value)
                ->timezone('Asia/Ho_Chi_Minh')
                ->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}
