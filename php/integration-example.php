<?php

// Include the age grade tables
require_once 'age-grade-tables.php';

use SportstatsTiming\AgeGradeTables\AgeGradeTables;

/**
 * Example integration with existing race results system
 * This shows how to modify your existing function to use the age grade tables
 */
function submitPartdataWithAgeGrade($eid, $input) {
    // Setup age grade lookup tables once (do this in your service constructor or bootstrap)
    $ageGradeLookups = [];
    $ageGradeLookups['2025_ironman'] = AgeGradeTables::createAgeGradeLookup(
        AgeGradeTables::getAgeGradeTable('2025_ironman', 'array')
    );
    $ageGradeLookups['2025_ironman703'] = AgeGradeTables::createAgeGradeLookup(
        AgeGradeTables::getAgeGradeTable('2025_ironman703', 'array')
    );

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
                            // NEW: Calculate age grade factor using the PHP package
                            $ageGradeFactor = calculateAgeGradeFactor($tables[$pd['rid']], $partInfo, $ageGradeLookups);
                            
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
            'invalid_ridcid' => $dropped_invalid_rid_cid
        ), true);
    }
}

/**
 * Calculate age grade factor based on table name and participant info
 * 
 * @param string $tableName The age grade table name from database
 * @param array $partInfo Participant info with gender, age, division
 * @param array $ageGradeLookups Pre-built lookup tables
 * @return float|null Age grade factor or null if not found
 */
function calculateAgeGradeFactor($tableName, $partInfo, $ageGradeLookups) {
    // Map database table names to our package table names
    $tableMapping = [
        'ironman' => '2025_ironman',
        'ironman703' => '2025_ironman703',
        'ironman_2025' => '2025_ironman',
        'ironman703_2025' => '2025_ironman703',
        // Add more mappings as needed
    ];
    
    // Get the mapped table name, fallback to original if not found
    $mappedTableName = $tableMapping[$tableName] ?? $tableName;
    
    // Check if we have a lookup for this table
    if (!isset($ageGradeLookups[$mappedTableName])) {
        // Fallback: try to get the table and create lookup on-the-fly
        try {
            $table = AgeGradeTables::getAgeGradeTable($mappedTableName, 'array');
            $ageGradeLookups[$mappedTableName] = AgeGradeTables::createAgeGradeLookup($table);
        } catch (Exception $e) {
            error_log("Age grade table not found: $mappedTableName");
            return null;
        }
    }
    
    $lookup = $ageGradeLookups[$mappedTableName];
    $gender = $partInfo['pg']; // Participant gender
    $age = $partInfo['pa'];    // Participant age
    
    return AgeGradeTables::getFactorFromLookup($lookup, $gender, $age);
}

/**
 * Alternative: Bulk processing for better performance
 * Use this if you're processing many participants at once
 */
function processBulkAgeGradeFactors($partdata, $tables, $ageGradeLookups) {
    // Group participants by table for bulk processing
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
    
    // Process each table's participants in bulk
    $results = [];
    foreach ($participantsByTable as $tableName => $participants) {
        $mappedTableName = $tableMapping[$tableName] ?? $tableName;
        
        if (isset($ageGradeLookups[$mappedTableName])) {
            $lookup = $ageGradeLookups[$mappedTableName];
            
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
    }
    
    return $results;
}

// Example usage in your existing function:
/*
// In your main function, replace the individual calculation with:
if (!empty($partInfo)) {
    $ageGradeFactors = processBulkAgeGradeFactors($partdata, $tables, $ageGradeLookups);
    
    foreach ($partdata as &$pd) {
        if (!empty($pd['pid']) && isset($ageGradeFactors[$pd['pid']])) {
            $factor = $ageGradeFactors[$pd['pid']];
            if ($factor !== null) {
                $pd['cagt'] = $pd['ft'] / $factor;
                $pd['agf'] = $factor;
            }
        }
    }
}
*/ 