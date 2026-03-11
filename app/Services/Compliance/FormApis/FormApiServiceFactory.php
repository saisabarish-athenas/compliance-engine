<?php

namespace App\Services\Compliance\FormApis;

class FormApiServiceFactory
{
    protected static array $apiServices = [
        // CLRA Forms
        'FORM_XII' => FormXIIApiService::class,
        'FORM_XIII' => FormXIIIApiService::class,
        'FORM_XIV' => FormXIVApiService::class,
        'FORM_XVI' => FormXVIApiService::class,
        'FORM_XVII' => FormXVIIApiService::class,
        'FORM_XIX' => FormXIXApiService::class,
        'FORM_XX' => FormXXApiService::class,
        'FORM_XXI' => FormXXIApiService::class,
        'FORM_XXII' => FormXXIIApiService::class,
        'FORM_XXIII' => FormXXIIIApiService::class,

        // Labour Welfare Forms
        'FORM_A' => FormAApiService::class,
        'FORM_C' => FormCApiService::class,
        'FORM_D' => FormDApiService::class,
        'FORM_D_ER' => FormDERApiService::class,

        // Social Security
        'FORM_11' => Form11ApiService::class,
        'ESI_FORM_12' => ESIForm12ApiService::class,
        'EPF_INSPECTION' => EPFInspectionApiService::class,

        // Factories Act
        'FORM_B' => FormBApiService::class,
        'FORM_2' => Form2ApiService::class,
        'FORM_8' => Form8ApiService::class,
        'FORM_10' => Form10ApiService::class,
        'FORM_12' => Form12ApiService::class,
        'FORM_17' => Form17ApiService::class,
        'FORM_18' => Form18ApiService::class,
        'FORM_25' => Form25ApiService::class,
        'FORM_26' => Form26ApiService::class,
        'FORM_26A' => Form26AApiService::class,
        'HAZARD_REG' => HazardRegApiService::class,

        // Bonus
        'SHOPS_FORM_C' => ShopsFormCApiService::class,
        'SHOPS_UNPAID' => ShopsUnpaidApiService::class,

        // Shops & Establishment
        'SHOPS_FORM_12' => ShopsForm12ApiService::class,
        'SHOPS_FORM_13' => ShopsForm13ApiService::class,
        'SHOPS_FINES' => ShopsFinesApiService::class,
        'SHOPS_FORM_VI' => ShopsFormVIApiService::class,
    ];

    public static function make(string $formCode): ?BaseFormApiService
    {
        $serviceClass = self::$apiServices[$formCode] ?? null;
        
        if (!$serviceClass) {
            return null;
        }

        try {
            return app($serviceClass);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function register(string $formCode, string $serviceClass): void
    {
        self::$apiServices[$formCode] = $serviceClass;
    }
}
