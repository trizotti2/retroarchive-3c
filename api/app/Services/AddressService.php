<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AddressService
{
    /**
     * @return Collection<int, Address>
     */
    public function listForUser(User $user): Collection
    {
        return $user->addresses()->latest('id')->get();
    }

    /**
     * @param  array{recipient: string, postal_code: string, street: string, number: string, complement?: string|null, neighborhood: string, city: string, state: string}  $data
     */
    public function create(User $user, array $data): Address
    {
        return $user->addresses()->create($data);
    }

    /**
     * @param  array{recipient: string, postal_code: string, street: string, number: string, complement?: string|null, neighborhood: string, city: string, state: string}  $data
     */
    public function update(User $user, int $addressId, array $data): Address
    {
        $address = $user->addresses()->findOrFail($addressId);
        $address->update($data);

        return $address;
    }

    public function delete(User $user, int $addressId): void
    {
        $user->addresses()->findOrFail($addressId)->delete();
    }
}
