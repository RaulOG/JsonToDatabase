<?php

namespace JsonToDatabase\Customer\Service;

use Carbon\Carbon;
use JsonToDatabase\Customer\Exception\CustomerDuplicatedException;
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

        $index = 0;

        if($this->isNotFirstTimeRunningFor($fileName))
        {
            $index = $this->customerRepository->getLastStoredIndexByFileName($fileName);
            $reader->startAt($index);
        }

        $value = $reader->read();

        while ($value)
        {
            $index++;
            $transformedData = $this->prepareData($value, $index, $fileName);

            try
            {
                $this->customerRepository->store($transformedData);
            }
            catch(CustomerDuplicatedException $e)
            {
            }

            $value = $reader->read();
        }

    }

    private function prepareData(array $raw, int $index, string $fileName): array
    {
        $hash = $this->makeHash($raw);
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
            'index_in_file' => $index,
            'filename' => $fileName,
            'hash' => $hash
        ]);
    }

    /**
     * @param array $value
     * @return string
     */
    private function makeHash(array $value) {
        return hash("sha512",
            $value['name'].$value['address'].$value['checked'].$value['description'].$value['interest'].$value['date_of_birth'].$value['email'].$value['account'].$value['credit_card']['type'].$value['credit_card']['number'].$value['credit_card']['name'].$value['credit_card']['expirationDate']
        );
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

    /**
     * @param $dateOfBirth
     * @return bool
     */
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
     * We consider the service is not running for the first time on a file when there are already customers stored who match the filename
     *
     * @param string $fileName
     * @return bool
     */
    private function isNotFirstTimeRunningFor(string $fileName): bool
    {
        return $this->customerRepository->existsByFilename($fileName);
    }
}
