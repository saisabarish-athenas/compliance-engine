#!/usr/bin/env php
<?php

/**
 * Validation Script: Verify Generators Have No Database Queries
 * 
 * Usage: php validate_generator_refactoring.php
 */

$generatorPath = __DIR__ . '/app/Services/Compliance/FormGenerator';
$apiPath = __DIR__ . '/app/Services/Compliance/FormApis';

$issues = [];
$passed = [];

// Patterns that indicate database queries
$dbPatterns = [
    'DB::table',
    'DB::select',
    'DB::insert',
    'DB::update',
    'DB::delete',
    '::where',
    '::find',
    '::get',
    '::first',
    '::all',
    '->query',
    'Model::',
    'Eloquent',
    'aggregate',
    'getBranchDetails',
    'getTenantDetails',
];

echo "═══════════════════════════════════════════════════════════════\n";
echo "Generator Refactoring Validation\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Check generators
echo "Checking Generators...\n";
echo "─────────────────────────────────────────────────────────────\n";

$generators = glob($generatorPath . '/*Generator.php');
foreach ($generators as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    
    // Skip base class check for certain patterns
    if ($filename === 'BaseFormGenerator.php') {
        // BaseFormGenerator should not have DB queries
        $hasIssues = false;
        foreach ($dbPatterns as $pattern) {
            if (strpos($content, $pattern) !== false && 
                strpos($content, "// " . $pattern) === false) {
                // Check if it's in a comment
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    if (strpos($line, $pattern) !== false && 
                        strpos(trim($line), '//') !== 0 &&
                        strpos(trim($line), '*') !== 0) {
                        $hasIssues = true;
                        break;
                    }
                }
            }
        }
        
        if ($hasIssues) {
            $issues[] = "❌ $filename: Contains database query patterns";
        } else {
            $passed[] = "✓ $filename: No database queries";
        }
    } else {
        // Concrete generators should not have DB queries
        $hasIssues = false;
        $foundPatterns = [];
        
        foreach ($dbPatterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                // Verify it's not in a comment
                $lines = explode("\n", $content);
                foreach ($lines as $lineNum => $line) {
                    $trimmed = trim($line);
                    if (strpos($trimmed, '//') === 0 || strpos($trimmed, '*') === 0) {
                        continue; // Skip comments
                    }
                    if (strpos($line, $pattern) !== false) {
                        $hasIssues = true;
                        $foundPatterns[] = $pattern;
                        break;
                    }
                }
            }
        }
        
        if ($hasIssues) {
            $issues[] = "❌ $filename: Contains database patterns: " . implode(', ', array_unique($foundPatterns));
        } else {
            $passed[] = "✓ $filename: No database queries";
        }
    }
}

foreach ($passed as $msg) {
    echo "$msg\n";
}

echo "\n";

// Check API services
echo "Checking API Services...\n";
echo "─────────────────────────────────────────────────────────────\n";

$apiServices = glob($apiPath . '/*ApiService.php');
foreach ($apiServices as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    
    // API services SHOULD have DB queries
    $hasDbQueries = false;
    foreach (['DB::table', 'DB::select', '::where', '::get', '::first'] as $pattern) {
        if (strpos($content, $pattern) !== false) {
            $hasDbQueries = true;
            break;
        }
    }
    
    if ($hasDbQueries) {
        $passed[] = "✓ $filename: Contains database queries (expected)";
    } else {
        $issues[] = "⚠ $filename: No database queries found (might be incomplete)";
    }
}

foreach ($passed as $msg) {
    echo "$msg\n";
}

echo "\n";

// Summary
echo "═══════════════════════════════════════════════════════════════\n";
echo "Summary\n";
echo "─────────────────────────────────────────────────────────────\n";

if (empty($issues)) {
    echo "✓ All checks passed!\n";
    echo "✓ Generators have no database queries\n";
    echo "✓ API services contain database queries\n";
    echo "\nRefactoring Status: COMPLETE ✓\n";
    exit(0);
} else {
    echo "Issues found:\n";
    foreach ($issues as $issue) {
        echo "$issue\n";
    }
    echo "\nRefactoring Status: INCOMPLETE ✗\n";
    exit(1);
}
