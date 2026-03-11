<?php

namespace App\Services\Compliance\Registry;

class FormTemplateRegistry
{
    private static array $templates = [
        'FORM_XII' => 'compliance.forms.form_xii',
        'FORM_XIII' => 'compliance.forms.form_xiii',
        'FORM_XIV' => 'compliance.forms.form_xiv',
        'FORM_XVI' => 'compliance.forms.form_xvi',
        'FORM_XVII' => 'compliance.forms.form_xvii',
        'FORM_XIX' => 'compliance.forms.form_xix',
        'FORM_XX' => 'compliance.forms.form_xx',
        'FORM_XXI' => 'compliance.forms.form_xxi',
        'FORM_XXII' => 'compliance.forms.form_xxii',
        'FORM_XXIII' => 'compliance.forms.form_xxiii',

        'FORM_A' => 'compliance.forms.form_a',
        'FORM_C' => 'compliance.forms.form_c',
        'FORM_D' => 'compliance.forms.form_d',
        'FORM_D_ER' => 'compliance.forms.form_d_er',
        'FORM_11' => 'compliance.forms.form_11',

        'ESI_FORM_12' => 'compliance.forms.esi_form_12',
        'EPF_INSPECTION' => 'compliance.forms.epf_inspection',
        'FORM_B' => 'compliance.forms.form_b',
        'FORM_2' => 'compliance.forms.form_2',

        'FORM_8' => 'compliance.forms.form_8',
        'FORM_10' => 'compliance.forms.form_10',
        'FORM_12' => 'compliance.forms.form_12',
        'FORM_17' => 'compliance.forms.form_17',
        'FORM_18' => 'compliance.forms.form_18',
        'FORM_25' => 'compliance.forms.form_25',
        'FORM_26' => 'compliance.forms.form_26',
        'FORM_26A' => 'compliance.forms.form_26a',
        'HAZARD_REG' => 'compliance.forms.hazard_reg',
        'SHOPS_FORM_C' => 'compliance.forms.shops_form_c',
        'SHOPS_UNPAID' => 'compliance.forms.shops_unpaid',
        'SHOPS_FORM_12' => 'compliance.forms.shops_form_12',
        'SHOPS_FORM_13' => 'compliance.forms.shops_form_13',
        'SHOPS_FINES' => 'compliance.forms.shops_fines',
        'SHOPS_FORM_VI' => 'compliance.forms.shops_form_vi',

    ];

    public static function resolve(string $formCode): string
    {
        return self::$templates[$formCode] ?? 'compliance.forms.' . strtolower($formCode);
    }

    public static function getAll(): array
    {
        return self::$templates;
    }

    public static function exists(string $formCode): bool
    {
        return isset(self::$templates[$formCode]);
    }
}
