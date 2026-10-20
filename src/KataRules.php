<?php

declare(strict_types=1);

namespace Katazom\TaxValidator;

/**
 * Hatályos KATA Adózási Szabályok és Keretszámító Segédosztály
 *
 * @author Katazom.hu <https://katazom.hu>
 */
class KataRules
{
    /**
     * Éves maximális KATA bevételi keret (Ft).
     */
    public const ANNUAL_KATA_LIMIT = 18_000_000;

    /**
     * Havi arányos KATA keret (Ft / aktív hónap).
     */
    public const MONTHLY_KATA_LIMIT = 1_500_000;

    /**
     * Éves alanyi adómentes (AAM) áfahatár (Ft).
     */
    public const ANNUAL_AAM_LIMIT = 12_000_000;

    /**
     * KATA kerettúllépés büntető különadó mértéke (40%).
     */
    public const SURTAX_RATE = 0.40;

    /**
     * Havi tételes KATA adó összege (Ft).
     */
    public const MONTHLY_TAX_AMOUNT = 50_000;

    /**
     * Időarányos KATA bevételi keret számítása aktív hónapok száma alapján.
     * (Szüneteltetés esetén a szüneteltetett hónapokra nem jár keret).
     *
     * @param int $activeMonths Az adott évben aktív hónapok száma (1-12)
     * @return int Időarányos keret Ft-ban
     */
    public static function calculateThreshold(int $activeMonths = 12): int
    {
        $clampedMonths = max(0, min(12, $activeMonths));

        return $clampedMonths * self::MONTHLY_KATA_LIMIT;
    }

    /**
     * Kerettúllépés ellenőrzése.
     */
    public static function isThresholdExceeded(float|int $revenue, int $activeMonths = 12): bool
    {
        return $revenue > self::calculateThreshold($activeMonths);
    }

    /**
     * A 40%-os különadó számítása a keretet meghaladó bevételrészre.
     *
     * @param float|int $revenue Összes éves KATA bevétel (Ft)
     * @param int $activeMonths Aktív hónapok száma (1-12)
     * @return float Fizetendő 40%-os különadó összege (Ft)
     */
    public static function calculateSurtax(float|int $revenue, int $activeMonths = 12): float
    {
        $threshold = self::calculateThreshold($activeMonths);

        if ($revenue <= $threshold) {
            return 0.0;
        }

        $excess = $revenue - $threshold;

        return (float) ($excess * self::SURTAX_RATE);
    }
}
