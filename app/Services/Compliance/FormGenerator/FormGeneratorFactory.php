<?php

namespace App\Services\Compliance\FormGenerator;

class FormGeneratorFactory
{
    protected static array $payrollForms = [
        'FORM_B', 'FORM_10', 'FORM_25', 'FORM_XVI', 'FORM_XVII', 'FORM_XIX',
        'FORM_XXI', 'FORM_XXIII', 'SHOPS_FORM_12', 'SHOPS_FINES', 'FORM_XX',
        'FORM_XXII', 'SHOPS_UNPAID'
    ];

    protected static array $contractorForms = [
        'FORM_XIII', 'FORM_XIV', 'FORM_XII', 'CLRA_LICENSE', 'FORM_XXIV',
        'FORM_XXV', 'SHOPS_FORM_1', 'CONTRACTOR_MASTER'
    ];

    protected static array $incidentForms = [
        'FORM_8', 'FORM_11', 'FORM_26', 'FORM_26A', 'ESI_FORM_12', 'FORM_18'
    ];

    protected static array $inspectionForms = [
        'HAZARD_REG', 'EPF_INSPECTION', 'SHOPS_FORM_13'
    ];

    protected static array $masterRegisterForms = [
        'FORM_12', 'FORM_17', 'FORM_2', 'SHOPS_FORM_C', 'SHOPS_FORM_VI', 'CLRA_RETURN'
    ];

    public static function make(string $formCode): ?BaseFormGenerator
    {
        if (in_array($formCode, self::$payrollForms)) {
            return new PayrollBasedFormGenerator($formCode);
        }
        
        if (in_array($formCode, self::$contractorForms)) {
            return new ContractorBasedFormGenerator($formCode);
        }
        
        if (in_array($formCode, self::$incidentForms)) {
            return new IncidentBasedFormGenerator($formCode);
        }
        
        if (in_array($formCode, self::$inspectionForms)) {
            return new InspectionBasedFormGenerator($formCode);
        }
        
        if (in_array($formCode, self::$masterRegisterForms)) {
            return new MasterRegisterFormGenerator($formCode);
        }

        return null;
    }

    public static function getSupportedForms(): array
    {
        return array_merge(
            self::$payrollForms,
            self::$contractorForms,
            self::$incidentForms,
            self::$inspectionForms,
            self::$masterRegisterForms
        );
    }
}
