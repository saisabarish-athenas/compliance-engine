<?php

namespace App\Services\Compliance\FormGenerator;

/**
 * FormGeneratorFactory - Maps form codes to dedicated generators
 *
 * Aligned with official compliance form catalog.
 * Each form has its own generator for better maintainability and debugging.
 */
class FormGeneratorFactory
{
    protected static array $generatorMap = [
        // Contractor Forms
        'FORM_XII' => FormXIIGenerator::class,
        'FORM_XIII' => FormXIIIGenerator::class,
        'FORM_XIV' => FormXIVGenerator::class,
        'FORM_XVI' => FormXVIGenerator::class,
        'FORM_XVII' => FormXVIIGenerator::class,
        'FORM_XIX' => FormXIXGenerator::class,
        'FORM_XX' => FormXXGenerator::class,
        'FORM_XXI' => FormXXIGenerator::class,
        'FORM_XXII' => FormXXIIGenerator::class,
        'FORM_XXIII' => FormXXIIIGenerator::class,

        // Master Register Forms
        'FORM_A' => FormAGenerator::class,
        'FORM_C' => FormCGenerator::class,
        'FORM_D' => FormDGenerator::class,
        'FORM_D_ER' => FormDERGenerator::class,

        // Incident Forms
        'FORM_11' => Form11Generator::class,
        'ESI_FORM_12' => ESIForm12Generator::class,
        'EPF_INSPECTION' => EPFInspectionGenerator::class,

        // Payroll Forms
        'FORM_B' => FormBGenerator::class,
        'FORM_2' => Form2Generator::class,
        'FORM_10' => Form10Generator::class,
        'FORM_12' => Form12Generator::class,
        'FORM_17' => Form17Generator::class,
        'FORM_18' => Form18Generator::class,
        'FORM_25' => Form25Generator::class,
        'FORM_8' => Form8Generator::class,
        'FORM_26' => Form26Generator::class,
        'FORM_26A' => Form26AGenerator::class,
        'HAZARD_REG' => HazardRegisterGenerator::class,

        // Shops Forms
        'SHOPS_FORM_C' => ShopsFormCGenerator::class,
        'SHOPS_UNPAID' => ShopsUnpaidGenerator::class,
        'SHOPS_FORM_12' => ShopsForm12Generator::class,
        'SHOPS_FORM_13' => ShopsForm13Generator::class,
        'SHOPS_FINES' => ShopsFinesGenerator::class,
        'SHOPS_FORM_VI' => ShopsFormVIGenerator::class,
    ];

    public static function make(string $formCode): ?BaseFormGenerator
    {
        if (!isset(self::$generatorMap[$formCode])) {
            return null;
        }

        $generatorClass = self::$generatorMap[$formCode];
        return new $generatorClass();
    }

    public static function getSupportedForms(): array
    {
        return array_keys(self::$generatorMap);
    }
}
