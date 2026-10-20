# Hungarian Tax Number & KATA Rules Validator 🇭🇺

[![Latest Version on Packagist](https://img.shields.io/packagist/v/katazom/hungarian-tax-validator.svg?style=flat-square)](https://packagist.org/packages/katazom/hungarian-tax-validator)
[![Total Downloads](https://img.shields.io/packagist/dt/katazom/hungarian-tax-validator.svg?style=flat-square)](https://packagist.org/packages/katazom/hungarian-tax-validator)
[![License](https://img.shields.io/packagist/l/katazom/hungarian-tax-validator.svg?style=flat-square)](LICENSE)
[![Powered By](https://img.shields.io/badge/Powered%20By-Katazom.hu-blue.svg?style=flat-square)](https://katazom.hu)

A lightweight, zero-dependency PHP library for validating Hungarian tax numbers (adószám checksum CDV verification, VAT code, county code) and calculating official Hungarian KATA sole proprietorship tax thresholds.

Maintained by the engineering team at **[Katazom.hu](https://katazom.hu)** – The automated KATA compliance and invoicing assistant.

---

## 🚀 Telepítés (Installation)

```bash
composer require katazom/hungarian-tax-validator
```

---

## 💡 Használat (Usage)

### 1. Magyar Adószám Validálása és Elemzése

A könyvtár ellenőrzi a formátumot, a matematikai ellenőrző összeget (CDV algoritmus), az áfakódot és a megyekódot:

```php
use Katazom\TaxValidator\HungarianTaxNumber;

// Egyszerű érvényesség vizsgálat:
if (HungarianTaxNumber::isValid('12345678-1-42')) {
    echo "Érvényes magyar adószám!";
}

// Részletes adószám elemzés:
$taxNumber = HungarianTaxNumber::parse('12345678142');

if ($taxNumber->isValid) {
    echo $taxNumber->baseNumber;  // "12345678" (törzsszám)
    echo $taxNumber->vatCode;     // "1" (áfakód: 1 = alanyi adómentes)
    echo $taxNumber->countyCode;  // "42" (megyekód)
    echo $taxNumber->format();    // "12345678-1-42" (szabványos formázott)

    if ($taxNumber->isVatExempt()) {
        echo "Alanyi adómentes (AAM) partner.";
    }
}
```

### 2. KATA Keretszámítás és Időarányosítás

Segédmetódusok a hatályos KATA szabályok szerinti keretekhez (havi 1,5M Ft, évi 18M Ft, 40%-os büntetőadó):

```php
use Katazom\TaxValidator\KataRules;

// 12 aktív hónap esetén a maximális keret: 18.000.000 Ft
$limit = KataRules::calculateThreshold(12);

// Szüneteltetés esetén (pl. 8 aktív hónap): 12.000.000 Ft
$proRataLimit = KataRules::calculateThreshold(8);

// Kerettúllépés ellenőrzése 14.000.000 Ft bevételnél (8 aktív hónap esetén):
if (KataRules::isThresholdExceeded(14_000_000, 8)) {
    // 40%-os különadó számítása a 2M Ft-os túllépésre: 800.000 Ft
    $surtax = KataRules::calculateSurtax(14_000_000, 8);
    echo "Fizetendő 40%-os különadó: " . number_format($surtax, 0, ',', ' ') . " Ft";
}
```

---

## 🔗 Kapcsolódó Ingyenes Eszközök

- 📊 **[Hivatalos KATA Kalkulátor](https://katazom.hu/#kalkulator)** – Élő keretszámítás és HIPA kalkuláció
- 🔄 **[Átalányadó Váltási Kalkulátor](https://katazom.hu/kalkulator)** – Valós számlák alapján megéri-e váltani?
- 🆓 **[IngyenKATA.hu](https://ingyenkata.hu)** – Ingyenes KATA keretfigyelő és számlázó asszisztens
- 📚 **[KATA Tudástár és Nagy GYIK](https://katazom.hu/tudastar)** – Szakmai cikkek és adózási útmutatók

---

## 📄 Licenc

Ez a csomag nyílt forráskódú és az **MIT License** alatt használható.
Készítette: **[Katazom.hu](https://katazom.hu)**
