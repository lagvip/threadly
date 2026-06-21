<?php

namespace Tests\Unit\Client;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Services\Client\Addresses\ClientAddressService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientAddressServiceTest extends TestCase
{
    public function test_first_address_is_created_as_default_inside_user_lock(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $addresses = $this->createMock(AddressRepositoryInterface::class);
        $addresses->expects($this->once())->method('lockUser')->with(5);
        $addresses->expects($this->once())->method('countForUser')->with(5)->willReturn(0);
        $addresses->expects($this->once())->method('unsetDefaultForUser')->with(5);
        $addresses->expects($this->once())
            ->method('createForUser')
            ->with(5, $this->callback(fn (array $data): bool => $data['user_id'] === 5 && $data['is_default'] === true
            ));

        (new ClientAddressService($addresses))->create(5, [
            'recipient_name' => 'Khách hàng',
        ], false);
    }
}
