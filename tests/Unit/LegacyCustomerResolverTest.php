<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\DirectoryFolder;
use App\Support\LegacyCustomerResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class LegacyCustomerResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_customer_from_id_dir_when_customer_id_empty(): void
    {
        $customer = Customer::query()->create([
            'customer_name' => 'Dir owner',
            'email' => 'dir-owner@example.com',
            'password' => 'secret',
            'status' => 'y',
            'deleted' => 'n',
        ]);

        $dir = DirectoryFolder::query()->create([
            'name_dir' => 'Hall',
            'customer_id' => $customer->customer_id,
            'type_dir' => 'g',
            'deleted' => 'n',
        ]);

        $resolved = LegacyCustomerResolver::resolve(Request::create('/', 'POST', [
            'customer_id' => '',
            'id_dir' => $dir->id_dir,
            'id_computer' => '0',
        ]));

        $this->assertNotNull($resolved);
        $this->assertSame((string) $customer->customer_id, (string) $resolved->customer_id);
    }
}
