<?php

namespace JsonToDatabase\Customer\Service;

use Carbon\Carbon;
use JsonToDatabase\Customer\Exception\ImportException;
use JsonToDatabase\Customer\Repository\CustomerRepository;
use pcrov\JsonReader\InputStream\IOException;
use pcrov\JsonReader\JsonReader;
use pcrov\JsonReader\Parser\ParseException;

class CustomerImportService
{
    private $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function run(string $fileName):void
    {
        $filePath = base_path($fileName);

        $reader = new JsonReader();

        try {
            $reader->open($filePath);
            $reader->read(); // Enter in array of customers
            $reader->read(); // Reader index at the customer 0
        } catch (IOException $e) {
            throw new ImportException("File not found");
        } catch (ParseException $e) {
            throw new ImportException("File is empty");
        }

        $count = 1;

        if ($this->customerRepository->existsByFilename($fileName)) {
            $numberOfCustomersAlreadyStored = $this->customerRepository->findLastCountByFilename($fileName);

            while ($numberOfCustomersAlreadyStored > 0) {
                $reader->next();
                $count++;
                $numberOfCustomersAlreadyStored--;
            }
        }

        while (!$this->isProcessFinished($reader)) {
            $transformedData = $this->prepareData($reader->value(), $count, $fileName);
            $this->customerRepository->store($transformedData);
            $count++;

            $reader->next();
        }

        $reader->close();
    }

    private function prepareData(array $raw, int $counter, string $fileName): array
    {
        return array_merge([
            "name" => $raw["name"],
            "address" => $raw["address"],
            "checked" => (boolean)$raw["checked"],
            "description" => $raw["description"],
            "interest" => $raw["interest"],
            "date_of_birth" => $this->getDateOfBirth($raw["date_of_birth"]),
            "email" => $raw["email"],
            "account" => $raw["account"],
            "credit_card_type" => $raw["credit_card"]["type"],
            "credit_card_number" => $raw["credit_card"]["number"],
            "credit_card_name" => $raw["credit_card"]["name"],
            "credit_card_expiration_date" => $raw["credit_card"]["expirationDate"],
        ], [
            'count' => $counter,
            'filename' => $fileName
        ]);
    }

    /**
     * @param string $dateOfBirth
     * @return null|string
     */
    private function getDateOfBirth($dateOfBirth)
    {
        if (is_null($dateOfBirth)) {
            return null;
        }

        if ($this->dateHasSlashes($dateOfBirth)) {
            return $this->parseSlashedDate($dateOfBirth);
        }

        return Carbon::create($dateOfBirth)->format("Y-m-d");
    }

    private function dateHasSlashes($dateOfBirth): bool
    {
        return (bool)strpos($dateOfBirth, '/');
    }

    /**
     * Assuming slashed dates have always the format d-m-Y
     *
     * @param $dateOfBirth
     * @return string
     */
    private function parseSlashedDate($dateOfBirth)
    {
        return Carbon::createFromFormat('d/m/Y', $dateOfBirth)->format("Y-m-d");
    }

    /**
     * @param JsonReader $reader
     * @return bool
     */
    private function isProcessFinished(JsonReader $reader): bool
    {
        return is_null($reader->value());
    }
}
