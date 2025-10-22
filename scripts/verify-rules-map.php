<?php

declare(strict_types=1);

$rulesPath = __DIR__ . '/../doc/regras-aplicadas.md';
$mapPath = __DIR__ . '/../tests/Specifications/README.md';

if (!file_exists($rulesPath)) {
    fwrite(STDERR, "Rules file not found at {$rulesPath}" . PHP_EOL);
    exit(1);
}

if (!file_exists($mapPath)) {
    fwrite(STDERR, "Specification map not found at {$mapPath}" . PHP_EOL);
    exit(1);
}

$rulesContent = file($rulesPath, FILE_IGNORE_NEW_LINES);
$ruleItems = [];

foreach ($rulesContent as $line) {
    if (preg_match('/^\s*-\s*\[[xX ]]\s*(.+)$/u', $line, $matches)) {
        $label = trim($matches[1]);

        if ($label === '') {
            continue;
        }

        $ruleItems[] = $label;
    }
}

$ruleItems = array_unique($ruleItems);

if ($ruleItems === []) {
    fwrite(STDERR, 'No checkbox rules were found in doc/regras-aplicadas.md; update the document before running this script.' . PHP_EOL);
    exit(1);
}

$mapContent = file($mapPath, FILE_IGNORE_NEW_LINES);
$mappedRules = [];
$pendingEntries = [];

foreach ($mapContent as $line) {
    $trimmed = trim($line);

    if ($trimmed === '' || !str_starts_with($trimmed, '|')) {
        continue;
    }

    if (preg_match('/^\|\s*-+\s*\|\s*-+\s*\|\s*-+\s*\|$/', $trimmed)) {
        continue; // header separator
    }

    $columns = array_map('trim', explode('|', trim($trimmed, '| ')));

    if (count($columns) < 3) {
        continue;
    }

    [$ruleColumn, $classColumn, $typeColumn] = $columns;

    if ($ruleColumn === '' || $ruleColumn === '_Adicionar referência aqui_') {
        $pendingEntries[] = $trimmed;
        continue;
    }

    if (str_contains($classColumn, '_pending_') || str_contains($typeColumn, '_pending_')) {
        $pendingEntries[] = $trimmed;
        continue;
    }

    $mappedRules[$ruleColumn] = $classColumn;
}

$missing = array_values(array_diff($ruleItems, array_keys($mappedRules)));

$hasIssues = false;

if ($missing !== []) {
    $hasIssues = true;
    fwrite(STDERR, 'Missing rule mappings for:' . PHP_EOL);
    foreach ($missing as $rule) {
        fwrite(STDERR, "- {$rule}" . PHP_EOL);
    }
    fwrite(STDERR, 'Update tests/Specifications/README.md with the test class covering each rule.' . PHP_EOL);
}

if ($pendingEntries !== []) {
    $hasIssues = true;
    fwrite(STDERR, 'Pending or placeholder entries were found in tests/Specifications/README.md:' . PHP_EOL);
    foreach ($pendingEntries as $entry) {
        fwrite(STDERR, "- {$entry}" . PHP_EOL);
    }
}

if ($hasIssues) {
    exit(1);
}

echo 'All rules in doc/regras-aplicadas.md have corresponding specification mappings.' . PHP_EOL;
