<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Customer\CreateCustomerDTO;
use App\DTOs\Customer\UpdateCustomerDTO;
use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerService
{
    /**
     * Busca um cliente pelo seu UUID público.
     *
     * @throws ModelNotFoundException
     */
    public function findByUuid(string $uuid): Customer
    {
        return Customer::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Cria e persiste um novo cliente a partir do DTO correspondente.
     */
    public function create(CreateCustomerDTO $dto): Customer
    {
        return Customer::create([
            'company_id' => $dto->company_id,
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password, // Hasheado via cast no model
            'phone' => $dto->phone,
            'document' => $dto->document,
            'birth_date' => $dto->birth_date,
            'marketing_opt_in' => $dto->marketing_opt_in,
            'status' => $dto->status,
        ]);
    }

    /**
     * Atualiza os dados de um cliente a partir do DTO correspondente.
     */
    public function update(Customer $customer, UpdateCustomerDTO $dto): Customer
    {
        $data = array_filter([
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'document' => $dto->document,
            'birth_date' => $dto->birth_date,
            'marketing_opt_in' => $dto->marketing_opt_in,
            'status' => $dto->status,
        ], fn ($value) => $value !== null);

        // Se uma senha for provida, atualiza
        if ($dto->password !== null) {
            $data['password'] = $dto->password;
        }

        // Caso marketing_opt_in não seja filtrado (sendo booleano e podendo ser falso, array_filter removeria o false, então tratamos separadamente)
        $data['marketing_opt_in'] = $dto->marketing_opt_in;

        $customer->update($data);

        return $customer;
    }

    /**
     * Exclui logicamente o cliente.
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }
}
