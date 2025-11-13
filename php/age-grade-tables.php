<?php

/**
 * Age Grade Tables for PHP
 * 
 * Provides age grading tables for races like Ironman and Ironman 70.3
 * in a compact, efficient format for PHP applications.
 * 
 * @author rcrawford
 * @license MIT
 */

class AgeGradeTables
{
    /**
     * 2025 Ironman Age Grade Table
     * Format: [gender, start_age, end_age, factor]
     */
    private static $ironman2025 = [
        // Male age groups
        ['M', 18, 24, 0.9698],
        ['M', 25, 29, 0.9921],
        ['M', 30, 34, 1.0000],
        ['M', 35, 39, 0.9895],
        ['M', 40, 44, 0.9683],
        ['M', 45, 49, 0.9401],
        ['M', 50, 54, 0.9002],
        ['M', 55, 59, 0.8667],
        ['M', 60, 64, 0.8262],
        ['M', 65, 69, 0.7552],
        ['M', 70, 74, 0.6876],
        ['M', 75, 79, 0.6768],
        ['M', 80, 84, 0.5555],
        ['M', 85, 89, 0.5416],

        // Female age groups
        ['F', 18, 24, 0.8567],
        ['F', 25, 29, 0.8961],
        ['F', 30, 34, 0.8977],
        ['F', 35, 39, 0.8866],
        ['F', 40, 44, 0.8707],
        ['F', 45, 49, 0.8501],
        ['F', 50, 54, 0.8125],
        ['F', 55, 59, 0.7778],
        ['F', 60, 64, 0.7218],
        ['F', 65, 69, 0.6828],
        ['F', 70, 74, 0.6439],
        ['F', 75, 79, 0.5521],
        ['F', 80, 84, 0.5521],
        ['F', 85, 89, 0.5521],
    ];

    private static $ironman2025_v2 = [
        // Male age groups
        ['M', 18, 24, 0.9698],
        ['M', 25, 29, 0.9921],
        ['M', 30, 34, 1.0000],
        ['M', 35, 39, 0.9895],
        ['M', 40, 44, 0.9683],
        ['M', 45, 49, 0.9401],
        ['M', 50, 54, 0.9002],
        ['M', 55, 59, 0.8667],
        ['M', 60, 64, 0.8262],
        ['M', 65, 69, 0.7552],
        ['M', 70, 74, 0.6876],
        ['M', 75, 79, 0.6768],
        ['M', 80, 84, 0.5555],
        ['M', 85, 89, 0.5416],

        // Female age groups
        ['F', 18, 24, 0.9543],
        ['F', 25, 29, 0.9982],
        ['F', 30, 34, 1.0000],
        ['F', 35, 39, 0.9877],
        ['F', 40, 44, 0.9699],
        ['F', 45, 49, 0.9470],
        ['F', 50, 54, 0.9051],
        ['F', 55, 59, 0.8665],
        ['F', 60, 64, 0.8041],
        ['F', 65, 69, 0.7606],
        ['F', 70, 74, 0.7173],
        ['F', 75, 79, 0.6150],
        ['F', 80, 84, 0.6150],
        ['F', 85, 89, 0.6150],
    ];

    /**
     * 2025 Ironman 70.3 Age Grade Table
     * Format: [gender, start_age, end_age, factor]
     */
    private static $ironman7032025 = [
        // Male age groups
        ['M', 18, 24, 1.0000],
        ['M', 25, 29, 0.9929],
        ['M', 30, 34, 0.9655],
        ['M', 35, 39, 0.9500],
        ['M', 40, 44, 0.9262],
        ['M', 45, 49, 0.8978],
        ['M', 50, 54, 0.8833],
        ['M', 55, 59, 0.8565],
        ['M', 60, 64, 0.8192],
        ['M', 65, 69, 0.7640],
        ['M', 70, 74, 0.7119],
        ['M', 75, 79, 0.6419],
        ['M', 80, 84, 0.5095],
        ['M', 85, 89, 0.5402],

        // Female age groups
        ['F', 18, 24, 0.9921],
        ['F', 25, 29, 1.0000],
        ['F', 30, 34, 0.9828],
        ['F', 35, 39, 0.9658],
        ['F', 40, 44, 0.9426],
        ['F', 45, 49, 0.9192],
        ['F', 50, 54, 0.9016],
        ['F', 55, 59, 0.8746],
        ['F', 60, 64, 0.8391],
        ['F', 65, 69, 0.7775],
        ['F', 70, 74, 0.7348],
        ['F', 75, 79, 0.6234],
        ['F', 80, 84, 0.6234],
        ['F', 85, 89, 0.6234],
    ];

    /**
     * Get age grade table by name
     * 
     * @param string $name Table name ('2025_ironman' or '2025_ironman703')
     * @param string $format Output format ('array' or 'json')
     * @return array The age grade table
     * @throws InvalidArgumentException
     */
    public static function getAgeGradeTable(string $name, string $format = 'array'): array
    {
        $table = null;
        
        switch ($name) {
            case '2025_ironman':
                $table = self::$ironman2025;
                break;
            case '2025_ironman703':
                $table = self::$ironman7032025;
                break;
            case '2025_ironman_v2':
                $table = self::$ironman2025_v2;
                break;
            default:
                throw new InvalidArgumentException("Unknown table name: $name");
        }
        
        if ($format === 'json') {
            return self::convertToJsonFormat($table);
        }
        
        return $table;
    }

    /**
     * Get age grade factor for a specific age and gender
     * 
     * @param array $table Age grade table
     * @param string $gender Athlete gender ('M' or 'F')
     * @param int $age Athlete age
     * @return float|null Age grade factor or null if not found
     */
    public static function getFactorByAgeAndGender(array $table, string $gender, int $age): ?float
    {
        foreach ($table as $entry) {
            if ($entry[0] === $gender && $age >= $entry[1] && $age <= $entry[2]) {
                return $entry[3];
            }
        }
        
        return null;
    }

    /**
     * Create a fast lookup array for bulk processing
     * 
     * @param array $table Age grade table
     * @return array Lookup array with key format: "gender_age" => factor
     */
    public static function createAgeGradeLookup(array $table): array
    {
        $lookup = [];
        
        foreach ($table as [$gender, $startAge, $endAge, $factor]) {
            for ($age = $startAge; $age <= $endAge; $age++) {
                $lookup["{$gender}_{$age}"] = $factor;
            }
        }
        
        return $lookup;
    }

    /**
     * Get factor from pre-built lookup array
     * 
     * @param array $lookup Pre-built lookup array
     * @param string $gender Athlete gender ('M' or 'F')
     * @param int $age Athlete age
     * @return float|null Age grade factor or null if not found
     */
    public static function getFactorFromLookup(array $lookup, string $gender, int $age): ?float
    {
        $key = "{$gender}_{$age}";
        return $lookup[$key] ?? null;
    }

    /**
     * Process multiple athletes efficiently
     * 
     * @param array $lookup Pre-built lookup array
     * @param array $athletes Array of athlete arrays with 'age' and 'gender' keys
     * @return array Array of athletes with their age grade factors
     */
    public static function processAthletesBulk(array $lookup, array $athletes): array
    {
        $results = [];
        
        foreach ($athletes as $athlete) {
            $factor = self::getFactorFromLookup($lookup, $athlete['gender'], $athlete['age']);
            $results[] = array_merge($athlete, ['factor' => $factor]);
        }
        
        return $results;
    }

    /**
     * Convert array format to JSON format
     * 
     * @param array $table Table in array format
     * @return array Table in JSON format
     */
    private static function convertToJsonFormat(array $table): array
    {
        $jsonTable = [];
        
        foreach ($table as [$gender, $start, $end, $factor]) {
            $jsonTable[] = [
                'gender' => $gender,
                'start' => $start,
                'end' => $end,
                'factor' => $factor
            ];
        }
        
        return $jsonTable;
    }
}

// Example usage functions
class AgeGradeExamples
{
    /**
     * Example: Process 40,000 athletes efficiently
     */
    public static function processLargeRace(): void
    {
        // Setup: Create fast lookup (do once)
        $table = AgeGradeTables::getAgeGradeTable('2025_ironman', 'array');
        $lookup = AgeGradeTables::createAgeGradeLookup($table);
        
        // Example: Generate sample athletes
        $athletes = [];
        for ($i = 0; $i < 40000; $i++) {
            $athletes[] = [
                'id' => $i + 1,
                'name' => "Athlete " . ($i + 1),
                'age' => rand(18, 89),
                'gender' => rand(0, 1) ? 'M' : 'F',
                'finishTime' => rand(28800, 36000), // 8-10 hours in seconds
            ];
        }
        
        // Process all athletes
        $startTime = microtime(true);
        $results = AgeGradeTables::processAthletesBulk($lookup, $athletes);
        $endTime = microtime(true);
        
        echo "Processed " . count($results) . " athletes in " . round(($endTime - $startTime) * 1000, 2) . "ms\n";
        
        // Calculate age-graded times for first 5 athletes
        $ageGradedResults = [];
        for ($i = 0; $i < 5; $i++) {
            $athlete = $results[$i];
            if ($athlete['factor']) {
                $ageGradedTime = $athlete['finishTime'] / $athlete['factor'];
                $ageGradedResults[] = array_merge($athlete, [
                    'ageGradedTime' => round($ageGradedTime),
                    'improvement' => round(($athlete['finishTime'] - $ageGradedTime) / 60) // minutes
                ]);
            }
        }
        
        echo "Sample results:\n";
        foreach ($ageGradedResults as $result) {
            echo "ID: {$result['id']}, Age: {$result['age']}, Gender: {$result['gender']}, ";
            echo "Factor: " . number_format($result['factor'], 4) . ", ";
            echo "Age Graded Time: " . number_format($result['ageGradedTime'] / 3600, 2) . " hours\n";
        }
    }
}

// If this file is run directly, show example
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'] ?? '')) {
    AgeGradeExamples::processLargeRace();
} 