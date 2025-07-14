/**
 * Compact entry format for age grade data
 * Format: [gender, start, end, factor]
 */
export type AgeGradeCompactEntry = [string, number, number, number];

/**
 * Object entry format for age grade data
 */
export interface AgeGradeObjectEntry {
  /** Gender identifier */
  gender: string;
  /** Start age */
  start: number;
  /** End age */
  end: number;
  /** Age grade factor */
  factor: number;
}

/**
 * Table name type for supported age grade tables
 */
export type TableName = '2025_ironman' | '2025_ironman703';

/**
 * Format type for data output
 */
export type Format = 'array' | 'json'; 