<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Device;
use App\Models\DirectoryFolder;
use Illuminate\Http\Request;

final class LegacyCustomerResolver
{
    public static function resolve(Request $request): ?Customer
    {
        $customerId = trim((string) $request->input('customer_id', ''));
        if ($customerId !== '') {
            return Customer::query()->where('customer_id', $customerId)->first();
        }

        $token = trim((string) ($request->input('customer_token') ?: $request->input('name_dir') ?: ''));
        if ($token !== '') {
            $byToken = Customer::query()->where('customer_token', $token)->first();
            if ($byToken) {
                return $byToken;
            }
        }

        $email = trim((string) $request->input('email', ''));
        if ($email !== '') {
            $byEmail = Customer::query()->where('email', $email)->first();
            if ($byEmail) {
                return $byEmail;
            }
        }

        $computerId = self::filledId($request->input('computer_id')) ?: self::filledId($request->input('id_computer'));
        if ($computerId) {
            $device = Device::alive()->where('computer_id', $computerId)->first();
            if ($device?->customer_id) {
                return Customer::query()->where('customer_id', $device->customer_id)->first();
            }
        }

        $idDir = self::filledId($request->input('id_dir'));
        if ($idDir) {
            $dir = DirectoryFolder::alive()->where('id_dir', $idDir)->first();
            if ($dir?->customer_id) {
                return Customer::query()->where('customer_id', $dir->customer_id)->first();
            }
        }

        return null;
    }

    private static function filledId(mixed $value): ?string
    {
        $s = trim((string) $value);

        if ($s === '' || $s === '0') {
            return null;
        }

        return $s;
    }
}
