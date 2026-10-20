<?php

declare(strict_types=1);

namespace Katazom\TaxValidator;

/**
 * Magyar Adószám Validátor és Elemző
 *
 * Támogatja a 8-1-2 formátumú magyar adószámok (pl. 12345678-1-42) formai,
 * matematikai ellenőrzőösszeg (CDV) és felépítésbeli validálását.
 *
 * @author Katazom.hu <https://katazom.hu>
 */
class HungarianTaxNumber
{
    /**
     * Súlyok az első 7 számjegy ellenőrző összegének számításához.
     */
    private const WEIGHTS = [9, 7, 3, 1, 9, 7, 3];

    /**
     * Érvényes áfakódok:
     * 1: alanyi adómentes (AAM) vagy adómentes tevékenység
     * 2: általános áfaalany
     * 3: egyszerűsített elszámolású (EVA/különbözeti)
     * 4: csoportos áfaalanyiság képviselője
     * 5: csoportos áfaalanyiság tagja
     */
    private const VALID_VAT_CODES = ['1', '2', '3', '4', '5'];

    public function __construct(
        public readonly string $raw,
        public readonly string $baseNumber,
        public readonly string $vatCode,
        public readonly string $countyCode,
        public readonly bool $isValid
    ) {}

    /**
     * Adószám elemzése és validálása.
     *
     * @param string $taxNumber Bármilyen formátumú adószám (pl. "12345678-1-42" vagy "12345678142")
     */
    public static function parse(string $taxNumber): self
    {
        $normalized = preg_replace('/[^0-9]/', '', trim($taxNumber)) ?? '';

        if (strlen($normalized) !== 11) {
            return new self(
                raw: $taxNumber,
                baseNumber: '',
                vatCode: '',
                countyCode: '',
                isValid: false
            );
        }

        $base = substr($normalized, 0, 8);
        $vat = substr($normalized, 8, 1);
        $county = substr($normalized, 9, 2);

        $isValid = self::validateChecksum($base)
            && in_array($vat, self::VALID_VAT_CODES, true)
            && self::validateCountyCode($county);

        return new self(
            raw: $taxNumber,
            baseNumber: $base,
            vatCode: $vat,
            countyCode: $county,
            isValid: $isValid
        );
    }

    /**
     * Egyszerű gyorsellenőrzés: érvényes-e az adószám?
     */
    public static function isValid(string $taxNumber): bool
    {
        return self::parse($taxNumber)->isValid;
    }

    /**
     * Alanyi adómentes (AAM) adószám-e? (áfakód == '1')
     */
    public function isVatExempt(): bool
    {
        return $this->isValid && $this->vatCode === '1';
    }

    /**
     * Szabványos formázott megjelenítés: XXXXXXXX-Y-ZZ
     */
    public function format(): string
    {
        if (!$this->isValid && strlen($this->baseNumber) !== 8) {
            return $this->raw;
        }

        return sprintf('%s-%s-%s', $this->baseNumber, $this->vatCode, $this->countyCode);
    }

    /**
     * Magyar adószám 8. számjegyének (CDV ellenőrzőösszeg) matematikai ellenőrzése.
     */
    private static function validateChecksum(string $base8): bool
    {
        if (strlen($base8) !== 8 || !ctype_digit($base8)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += ((int) $base8[$i]) * self::WEIGHTS[$i];
        }

        $expectedCheckDigit = $sum % 10;
        $actualCheckDigit = (int) $base8[7];

        return $expectedCheckDigit === $actualCheckDigit;
    }

    /**
     * Megyekód érvényesség ellenőrzése (02-44, illetve 51 a kiemelt adózóknak).
     */
    private static function validateCountyCode(string $county): bool
    {
        $code = (int) $county;

        if ($code === 51) {
            return true;
        }

        return $code >= 2 && $code <= 44;
    }
}
