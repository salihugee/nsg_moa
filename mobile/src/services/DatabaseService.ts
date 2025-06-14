import SQLite from 'react-native-sqlite-storage';
import { enablePromise } from 'react-native-sqlite-storage';

// Enable Promise-based SQLite operations
enablePromise(true);

export interface DataRecord {
  id?: number;
  type: string;
  data: Record<string, any>;
  status: 'pending' | 'synced' | 'error';
  created_at: string;
  updated_at: string;
  sync_error?: string;
  sync_attempts?: number;
}

class DatabaseService {
  private database: SQLite.SQLiteDatabase | null = null;

  async initDatabase() {
    try {
      this.database = await SQLite.openDatabase({
        name: 'nsg_moa.db',
        location: 'default',
      });

      await this.createTables();
      console.log('Database initialized successfully');
    } catch (error) {
      console.error('Database initialization failed:', error);
      throw error;
    }
  }

  private async createTables() {
    if (!this.database) throw new Error('Database not initialized');

    // Create tables for different data types
    await this.database.executeSql(`
      CREATE TABLE IF NOT EXISTS data_records (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT NOT NULL,
        data TEXT NOT NULL,
        status TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        sync_error TEXT,
        sync_attempts INTEGER DEFAULT 0
      );
    `);

    // Create index for faster querying
    await this.database.executeSql(`
      CREATE INDEX IF NOT EXISTS idx_data_records_status 
      ON data_records(status);
    `);
  }

  async saveRecord(record: Omit<DataRecord, 'id'>) {
    if (!this.database) throw new Error('Database not initialized');

    const { type, data, status } = record;
    const now = new Date().toISOString();

    const [result] = await this.database.executeSql(`
      INSERT INTO data_records (type, data, status, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?)
    `, [type, JSON.stringify(data), status, now, now]);

    return result.insertId;
  }

  async getPendingRecords(): Promise<DataRecord[]> {
    if (!this.database) throw new Error('Database not initialized');

    const [results] = await this.database.executeSql(`
      SELECT * FROM data_records 
      WHERE status = 'pending'
      ORDER BY created_at ASC;
    `);

    return Array.from({ length: results.rows.length })
      .map((_, index) => {
        const record = results.rows.item(index);
        return {
          ...record,
          data: JSON.parse(record.data)
        };
      });
  }

  async updateRecordStatus(
    id: number, 
    status: 'synced' | 'error', 
    error?: string
  ) {
    if (!this.database) throw new Error('Database not initialized');

    const now = new Date().toISOString();
    await this.database.executeSql(`
      UPDATE data_records 
      SET status = ?, 
          updated_at = ?,
          sync_error = ?,
          sync_attempts = sync_attempts + 1
      WHERE id = ?
    `, [status, now, error || null, id]);
  }

  async getRecordsByType(type: string): Promise<DataRecord[]> {
    if (!this.database) throw new Error('Database not initialized');

    const [results] = await this.database.executeSql(`
      SELECT * FROM data_records 
      WHERE type = ?
      ORDER BY created_at DESC;
    `, [type]);

    return Array.from({ length: results.rows.length })
      .map((_, index) => {
        const record = results.rows.item(index);
        return {
          ...record,
          data: JSON.parse(record.data)
        };
      });
  }

  async deleteRecord(id: number) {
    if (!this.database) throw new Error('Database not initialized');

    await this.database.executeSql(`
      DELETE FROM data_records 
      WHERE id = ?
    `, [id]);
  }
}

export const databaseService = new DatabaseService();
