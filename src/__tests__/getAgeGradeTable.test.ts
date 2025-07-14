import { getAgeGradeTable, getFactorByAgeAndGender } from '../index';

describe('getAgeGradeTable', () => {
  describe('2025_ironman', () => {
    it('should return Ironman table in array format by default', () => {
      const table = getAgeGradeTable('2025_ironman');
      expect(Array.isArray(table)).toBe(true);
      expect(table.length).toBeGreaterThan(0);
      
      // Check first entry structure
      const firstEntry = table[0] as any;
      expect(Array.isArray(firstEntry)).toBe(true);
      expect(firstEntry).toHaveLength(4);
      expect(typeof firstEntry[0]).toBe('string'); // gender
      expect(typeof firstEntry[1]).toBe('number'); // start
      expect(typeof firstEntry[2]).toBe('number'); // end
      expect(typeof firstEntry[3]).toBe('number'); // factor
    });

    it('should return Ironman table in array format explicitly', () => {
      const table = getAgeGradeTable('2025_ironman', 'array');
      expect(Array.isArray(table)).toBe(true);
      expect(table.length).toBeGreaterThan(0);
    });

    it('should return Ironman table in json format', () => {
      const table = getAgeGradeTable('2025_ironman', 'json');
      expect(Array.isArray(table)).toBe(true);
      expect(table.length).toBeGreaterThan(0);
      
      // Check first entry structure
      const firstEntry = table[0] as any;
      expect(typeof firstEntry).toBe('object');
      expect(firstEntry).toHaveProperty('gender');
      expect(firstEntry).toHaveProperty('start');
      expect(firstEntry).toHaveProperty('end');
      expect(firstEntry).toHaveProperty('factor');
    });
  });

  describe('2025_ironman703', () => {
    it('should return Ironman 70.3 table in array format by default', () => {
      const table = getAgeGradeTable('2025_ironman703');
      expect(Array.isArray(table)).toBe(true);
      expect(table.length).toBeGreaterThan(0);
    });

    it('should return Ironman 70.3 table in json format', () => {
      const table = getAgeGradeTable('2025_ironman703', 'json');
      expect(Array.isArray(table)).toBe(true);
      expect(table.length).toBeGreaterThan(0);
    });
  });

  describe('Error handling', () => {
    it('should throw error for unknown table name', () => {
      expect(() => {
        getAgeGradeTable('unknown_table' as any);
      }).toThrow('Unknown table name: unknown_table');
    });

    it('should throw error for unknown format', () => {
      expect(() => {
        getAgeGradeTable('2025_ironman', 'unknown_format' as any);
      }).toThrow('Unknown format: unknown_format');
    });
  });

  describe('Data validation', () => {
    it('should have correct age groups for Ironman', () => {
      const table = getAgeGradeTable('2025_ironman', 'array') as any[];
      
      // Check that we have entries for both genders
      const maleEntries = table.filter(entry => entry[0] === 'M');
      const femaleEntries = table.filter(entry => entry[0] === 'F');
      
      expect(maleEntries.length).toBeGreaterThan(0);
      expect(femaleEntries.length).toBeGreaterThan(0);
      
      // Check age ranges
      const ages = table.map(entry => [entry[1], entry[2]]);
      const minAge = Math.min(...ages.flat());
      const maxAge = Math.max(...ages.flat());
      
      expect(minAge).toBe(0);
      expect(maxAge).toBe(99);
    });

    it('should have correct age groups for Ironman 70.3', () => {
      const table = getAgeGradeTable('2025_ironman703', 'array') as any[];
      
      // Check that we have entries for both genders
      const maleEntries = table.filter(entry => entry[0] === 'M');
      const femaleEntries = table.filter(entry => entry[0] === 'F');
      
      expect(maleEntries.length).toBeGreaterThan(0);
      expect(femaleEntries.length).toBeGreaterThan(0);
      
      // Check age ranges
      const ages = table.map(entry => [entry[1], entry[2]]);
      const minAge = Math.min(...ages.flat());
      const maxAge = Math.max(...ages.flat());
      
      expect(minAge).toBe(0);
      expect(maxAge).toBe(99);
    });
  });
});

describe('getFactorByAgeAndGender', () => {
  it('should return correct factor for valid age and gender', () => {
    const table = getAgeGradeTable('2025_ironman', 'array') as any[];
    const factor = getFactorByAgeAndGender(table, 'M', 35);
    expect(factor).toBe(0.98);
  });

  it('should return correct factor for female', () => {
    const table = getAgeGradeTable('2025_ironman', 'array') as any[];
    const factor = getFactorByAgeAndGender(table, 'F', 35);
    expect(factor).toBe(0.98);
  });

  it('should return null for invalid age', () => {
    const table = getAgeGradeTable('2025_ironman', 'array') as any[];
    const factor = getFactorByAgeAndGender(table, 'M', 999);
    expect(factor).toBeNull();
  });

  it('should return null for invalid gender', () => {
    const table = getAgeGradeTable('2025_ironman', 'array') as any[];
    const factor = getFactorByAgeAndGender(table, 'X', 35);
    expect(factor).toBeNull();
  });

  it('should work with age at boundary values', () => {
    const table = getAgeGradeTable('2025_ironman', 'array') as any[];
    
    // Test age at start of range
    const factorStart = getFactorByAgeAndGender(table, 'M', 0);
    expect(factorStart).toBe(1.0);
    
    // Test age at end of range
    const factorEnd = getFactorByAgeAndGender(table, 'M', 99);
    expect(factorEnd).toBe(0.925);
  });
}); 