<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Repository\CountryRepository;
use App\Repository\CurrencyRepository;
use App\Repository\CustomerRepository;
use App\Repository\CustomerTypeRepository;
use App\Repository\LanguageRepository;
use App\Repository\PrincipalRepository;
use DateTimeImmutable;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Customer>
 *
 * @method        Customer|Proxy                     create(array|callable $attributes = [])
 * @method static Customer|Proxy                     createOne(array $attributes = [])
 * @method static Customer|Proxy                     find(object|array|mixed $criteria)
 * @method static Customer|Proxy                     findOrCreate(array $attributes)
 * @method static Customer|Proxy                     first(string $sortedField = 'id')
 * @method static Customer|Proxy                     last(string $sortedField = 'id')
 * @method static Customer|Proxy                     random(array $attributes = [])
 * @method static Customer|Proxy                     randomOrCreate(array $attributes = [])
 * @method static CustomerRepository|RepositoryProxy repository()
 * @method static Customer[]|Proxy[]                 all()
 * @method static Customer[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Customer[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Customer[]|Proxy[]                 findBy(array $attributes)
 * @method static Customer[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Customer[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class CustomerFactory extends ModelFactory
{
    public function __construct(
        private readonly CountryRepository      $countryRepository,
        private readonly CurrencyRepository     $currencyRepository,
        private readonly CustomerTypeRepository $customerTypeRepository,
        private readonly LanguageRepository     $languageRepository,
        private readonly PrincipalRepository    $principalRepository,
    )
    {
        parent::__construct();
    }

    protected function getDefaults(): array
    {
        return [
            'createdAt' => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'name' => self::faker()->company().' '.self::faker()->companySuffix(),
            'addressLine1' => self::faker()->streetAddress(),
            'addressLine2' => self::faker()->randomNumber(5).' '.self::faker()->city,
            'addressLineCountry' => $this->countryRepository->find(self::faker()->numberBetween(1,3)),
            'principal' => $this->principalRepository->find(1),
            'customerType' => $this->customerTypeRepository->find(self::faker()->numberBetween(1,2)),
            'languageDefault' => $this->languageRepository->find(self::faker()->numberBetween(1,2)),
            'currencyDefault' => $this->currencyRepository->find(self::faker()->numberBetween(1,2)),
            'ledgerAccountNumber' => self::faker()->numberBetween(10001, 19999),
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function(Customer $customer) {
                if (!$customer->getShortName()) {
                    $customer->setShortName($this->initialLettersToUpper($customer->getName()));
                }
            })
            ;
    }

    private function initialLettersToUpper($inputString):string
    {
        // Zerlegt den String in Wörter
        $words = explode(' ', $inputString);
        $initials = '';

        // Durchläuft jedes Wort und fügt den ersten Buchstaben zu $initials hinzu
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);  // Fügt den ersten Buchstaben in Großbuchstaben hinzu
            }
        }

        return $initials;
    }

    protected static function getClass(): string
    {
        return Customer::class;
    }
}
