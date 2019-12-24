<?php

namespace JsonToDatabase\Customer\Service;

use Carbon\Carbon;
use JsonToDatabase\Customer\Repository\CustomerRepository;
use JsonToDatabase\Reader\Factory\ReaderFactory;

class CustomerImportService
{
    private $customerRepository;

    private $readerFactory;

    public function __construct(ReaderFactory $readerFactory, CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
        $this->readerFactory = $readerFactory;
    }

    public function run(string $fileName):void
    {
        $reader = $this->readerFactory->makeFor($fileName);

        $count = 1;

        if ($this->customerRepository->existsByFilename($fileName)) {
            $numberOfCustomersAlreadyStored = $this->customerRepository->findLastCountByFilename($fileName);

            $reader->startAt($numberOfCustomersAlreadyStored);
        }

        $value = $reader->read();

        while ($value) {
            $transformedData = $this->prepareData($value, $count, $fileName);
            $this->customerRepository->store($transformedData);
            $count++;

            $value = $reader->read();
        }

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
}
