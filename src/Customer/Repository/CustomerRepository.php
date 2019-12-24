<?php

namespace JsonToDatabase\Customer\Repository;

use JsonToDatabase\Customer\Entity\Customer;

class CustomerRepository
{
    public function existsByFilename(string $fileName):bool
    {
        return Customer::where("filename", $fileName)->exists();
    }

    public function findLastCountByFilename(string $fileName):int
    {
        return Customer::where(["filename" => $fileName])->max("count");
    }

    public function store(array $transformedData):Customer
    {
        $customer = new Customer($transformedData);
        $customer->save();
        return $customer;
    }
}
