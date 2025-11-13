<?php

// Include the age grade tables
require_once 'age-grade-tables.php';

use SportstatsTiming\AgeGradeTables\AgeGradeTables;

/**
 * Dynamic age grade table integration
 * Creates lookup tables on-demand based on what's actually needed
 */
function submitPartdataWithDynamicAgeGrade($eid, $input) {
    // Initialize empty lookup cache
    $ageGradeLookups = [];
    
    if ($input['function'] == 'submit') {
        if (empty($input['partdata'])) {
            throw new Exception('No Participant Data (partdata) Supplied');
        } elseif (!is_array($input['partdata'])) {
            throw new Exception('Participant Data (partdata) is an array');
        } else {
            $partdata = $input['partdata'];
        }

        $bibs = array();
        $tables = array();
        $updated = 0;
        $update_errors = 0;
        $dropped_invalid_pid_bib = 0;
        $dropped_invalid_rid_cid = 0;

        // Get list of bibs for event
        $rids = Rdb::fetchRidsByEid($eid);
        foreach($rids as $rid) {
            $bibs[$rid] = Rdb::getBibPidsByRid($rid);
            $tables[$rid] = Rdb::fetchAgeGradeTableKey($rid);
        }
        $rid_cids = Rdb::getEventCols($eid);

        // NEW: Create lookup tables dynamically based on what's actually needed
        $neededTables = array_unique(array_values($tables));
        foreach ($neededTables as $tableName) {
            if (!empty($tableName)) {
                $lookup = createLookupForTable($tableName);
                if ($lookup !== null) {
                    $ageGradeLookups[$tableName] = $lookup;
                }
            }
        }

        // Scan data to determine if we have pids/bibs/valid CID/RID coming in
        foreach ($partdata as $k => &$pd) {
            // Check for Multi
            if (str_contains($pd['cid'], '_')) {
                list($pdcid, $pdcount) = explode('_', $pd['cid']);
            } else {
                $pdcid = $pd['cid'];
            }
            
            if (!in_array($pdcid, $rid_cids[$pd['rid']])) {
                unset($partdata[$k]);
                $dropped_invalid_rid_cid++;
                continue;
            }
            
            if ((empty($pd['pid'])) && (empty($pd['bib']))) {
                $dropped_invalid_pid_bib++;
                unset($partdata[$k]);
                continue;
            } else {
                if ((empty($pd['pid'])) && (!empty($bibs[$pd['rid']][$pd['bib']]))) {
                    $pd['pid'] = $bibs[$pd['rid']][$pd['bib']];
                    
                    if(!empty($tables[$pd['rid']])) {
                        $partInfo = Rdb::getPartGenderAgeDiv($eid, $pd['pid']);
                        // returns array of {pg => 'M', pa => 44, pc => 'M40-44'}
                        
                        if (!empty($partInfo)) {
                            // Use the dynamically created lookup
                            $ageGradeFactor = getAgeGradeFactorFromLookup($tables[$pd['rid']], $partInfo, $ageGradeLookups);
                            
                            if ($ageGradeFactor !== null) {
                                // Calculate age graded time
                                $pd['cagt'] = $pd['ft'] / $ageGradeFactor; // ft = finish time in seconds
                                $pd['agf'] = $ageGradeFactor; // Store the factor for reference
                            }
                        }
                    }
                } else {
                    $pd['pid'] = null;
                }
                addToPublisher($pd['rid']);
            }
        }
        
        if ($updated = Partdata::submitPartdata($eid, $partdata)) {
            $updated = sizeof($partdata);
        }

        Response::add('data', 'partdataSubmit', array(
            'updated' => $updated, 
            'invalid_pidbib' => $dropped_invalid_pid_bib, 
            'invalid_ridcid' => $dropped_invalid_rid_cid,
            'tables_loaded' => count($ageGradeLookups) // Debug info
        ), true);
    }
}

/**
 * Create lookup table for a specific table name
 * 
 * @param string $tableName The table name from database
 * @return array|null Lookup array or null if table not found
 */
function createLookupForTable($tableName) {
    // Map database table names to our package table names
    $tableMapping = [
        // Ironman tables
        'ironman' => '2025_ironman',
        'ironman_2025' => '2025_ironman',
        'ironman_2024' => '2024_ironman',
        'ironman_2023' => '2023_ironman',
        
        // Ironman 70.3 tables
        'ironman703' => '2025_ironman703',
        'ironman_703' => '2025_ironman703',
        'ironman703_2025' => '2025_ironman703',
        'ironman_703_2025' => '2025_ironman703',
        'ironman703_2024' => '2024_ironman703',
        'ironman_703_2024' => '2024_ironman703',
        
        // Marathon tables (if you add them later)
        'marathon' => '2025_marathon',
        'marathon_2025' => '2025_marathon',
        
        // Half marathon tables (if you add them later)
        'half_marathon' => '2025_half_marathon',
        'halfmarathon' => '2025_half_marathon',
        'half_marathon_2025' => '2025_half_marathon',
        
        // Sprint/Olympic triathlon tables (if you add them later)
        'sprint_tri' => '2025_sprint_tri',
        'olympic_tri' => '2025_olympic_tri',
        'sprint_tri_2025' => '2025_sprint_tri',
        'olympic_tri_2025' => '2025_olympic_tri',
        
        // Add more mappings as you add more tables
    ];
    
    // Get the mapped table name, fallback to original if not found
    $mappedTableName = $tableMapping[$tableName] ?? $tableName;
    
    try {
        $table = AgeGradeTables::getAgeGradeTable($mappedTableName, 'array');
        $lookup = AgeGradeTables::createAgeGradeLookup($table);
        
        // Log successful table creation for debugging
        error_log("Created age grade lookup for table: $tableName -> $mappedTableName");
        
        return $lookup;
    } catch (Exception $e) {
        error_log("Failed to create age grade lookup for table: $tableName -> $mappedTableName - " . $e->getMessage());
        return null;
    }
}

/**
 * Get age grade factor from pre-built lookup
 * 
 * @param string $tableName The table name from database
 * @param array $partInfo Participant info with gender, age, division
 * @param array $ageGradeLookups Pre-built lookup tables
 * @return float|null Age grade factor or null if not found
 */
function getAgeGradeFactorFromLookup($tableName, $partInfo, $ageGradeLookups) {
    if (!isset($ageGradeLookups[$tableName])) {
        error_log("Lookup not found for table: $tableName");
        return null;
    }
    
    $lookup = $ageGradeLookups[$tableName];
    $gender = $partInfo['pg']; // Participant gender
    $age = $partInfo['pa'];    // Participant age
    
    return AgeGradeTables::getFactorFromLookup($lookup, $gender, $age);
}

/**
 * Bulk processing with dynamic table creation
 * More efficient for large datasets
 */
function processBulkAgeGradeFactorsDynamic($partdata, $tables, $eid) {
    // Group participants by table
    $participantsByTable = [];
    
    foreach ($partdata as $pd) {
        if (!empty($pd['rid']) && !empty($tables[$pd['rid']])) {
            $tableName = $tables[$pd['rid']];
            if (!isset($participantsByTable[$tableName])) {
                $participantsByTable[$tableName] = [];
            }
            $participantsByTable[$tableName][] = $pd;
        }
    }
    
    // Create lookups only for tables that are actually used
    $ageGradeLookups = [];
    $results = [];
    
    foreach ($participantsByTable as $tableName => $participants) {
        // Create lookup if not already created
        if (!isset($ageGradeLookups[$tableName])) {
            $lookup = createLookupForTable($tableName);
            if ($lookup !== null) {
                $ageGradeLookups[$tableName] = $lookup;
            } else {
                continue; // Skip this table if lookup creation failed
            }
        }
        
        $lookup = $ageGradeLookups[$tableName];
        
        // Convert participants to format expected by bulk processor
        $athletes = [];
        foreach ($participants as $pd) {
            $partInfo = Rdb::getPartGenderAgeDiv($eid, $pd['pid']);
            if (!empty($partInfo)) {
                $athletes[] = [
                    'pid' => $pd['pid'],
                    'age' => $partInfo['pa'],
                    'gender' => $partInfo['pg'],
                    'finishTime' => $pd['ft'] ?? 0
                ];
            }
        }
        
        // Process in bulk
        $processedAthletes = AgeGradeTables::processAthletesBulk($lookup, $athletes);
        
        // Map results back to original participants
        foreach ($processedAthletes as $athlete) {
            $results[$athlete['pid']] = $athlete['factor'];
        }
    }
    
    return $results;
}

/**
 * Alternative: Pre-load all possible tables at startup
 * Use this if you want to avoid any runtime table creation
 */
function preloadAllAgeGradeTables() {
    $allTables = [
        '2025_ironman',
        '2025_ironman703',
        '2024_ironman',
        '2024_ironman703',
        '2023_ironman',
        '2023_ironman703',
        // Add more as you create them
    ];
    
    $ageGradeLookups = [];
    
    foreach ($allTables as $tableName) {
        try {
            $table = AgeGradeTables::getAgeGradeTable($tableName, 'array');
            $ageGradeLookups[$tableName] = AgeGradeTables::createAgeGradeLookup($table);
        } catch (Exception $e) {
            error_log("Failed to preload table: $tableName - " . $e->getMessage());
        }
    }
    
    return $ageGradeLookups;
}

// Example usage with pre-loaded tables:
/*
// In your application bootstrap or service constructor:
$globalAgeGradeLookups = preloadAllAgeGradeTables();

// Then in your submit function, use the global lookups:
function submitPartdataWithPreloadedTables($eid, $input) {
    global $globalAgeGradeLookups;
    
    // ... your existing code ...
    
    if (!empty($partInfo)) {
        $ageGradeFactor = getAgeGradeFactorFromLookup($tables[$pd['rid']], $partInfo, $globalAgeGradeLookups);
        
        if ($ageGradeFactor !== null) {
            $pd['cagt'] = $pd['ft'] / $ageGradeFactor;
            $pd['agf'] = $ageGradeFactor;
        }
    }
    
    // ... rest of your code ...
}
*/ 