<?php

/**
 * Helps identify what classes haven't been covered with tests
 */

$xml = simplexml_load_string(file_get_contents('clover.xml'));
$json = json_encode($xml);
$array = json_decode($json, TRUE);

foreach ($xml->xpath('//file') as $file) {
    $metric = $file->children()[count($file->children()) - 1];

    /** @var SimpleXMLElement $metric */
    $methods = (int) $metric['methods'] ?? 0;
    $coveredMethods = (int) $metric['coveredmethods'] ?? 0;
    if ($coveredMethods < $methods) {
        fwrite(STDOUT, sprintf('%s, methods: %d, coveredmethods: %d', $file['name'] ?? '', $methods, $coveredMethods) . PHP_EOL);
    }
    $statements = (int) $metric['statements'] ?? 0;
    $coveredStatements = (int) $metric['coveredstatements'] ?? 0;
    if ($coveredStatements < $statements) {
        fwrite(STDOUT, sprintf('%s, statements: %d, coveredstatements: %d', $file['name'] ?? '', $statements, $coveredStatements) . PHP_EOL);
    }
    $elements = (int) $metric['elements'] ?? 0;
    $coveredElements = (int) $metric['coveredelements'] ?? 0;
    if ($coveredElements < $elements) {
        fwrite(STDOUT, sprintf('%s, elements: %d, coveredelements: %d', $file['name'] ?? '', $elements, $coveredElements) . PHP_EOL);
    }
}
