import { databaseService, DataRecord } from './DatabaseService';
import { api } from './api';
import NetInfo from '@react-native-community/netinfo';

class SyncService {
  private syncInProgress = false;
  private syncInterval: NodeJS.Timeout | null = null;

  startAutoSync(intervalMs: number = 300000) { // 5 minutes by default
    if (this.syncInterval) {
      clearInterval(this.syncInterval);
    }

    this.syncInterval = setInterval(() => {
      this.syncPendingRecords();
    }, intervalMs);

    // Initial sync
    this.syncPendingRecords();

    // Listen for network changes
    NetInfo.addEventListener(state => {
      if (state.isConnected) {
        this.syncPendingRecords();
      }
    });
  }

  stopAutoSync() {
    if (this.syncInterval) {
      clearInterval(this.syncInterval);
      this.syncInterval = null;
    }
  }

  async syncPendingRecords() {
    if (this.syncInProgress) return;

    const networkState = await NetInfo.fetch();
    if (!networkState.isConnected) return;

    try {
      this.syncInProgress = true;
      const pendingRecords = await databaseService.getPendingRecords();

      for (const record of pendingRecords) {
        try {
          await this.syncRecord(record);
          await databaseService.updateRecordStatus(record.id!, 'synced');
        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Sync failed';
          await databaseService.updateRecordStatus(
            record.id!,
            'error',
            errorMessage
          );
        }
      }
    } finally {
      this.syncInProgress = false;
    }
  }

  private async syncRecord(record: DataRecord) {
    const endpoint = `/api/${record.type}`;
    
    try {
      await api.post(endpoint, record.data);
    } catch (error) {
      console.error(`Failed to sync record ${record.id}:`, error);
      throw error;
    }
  }

  async getConnectionStatus() {
    const networkState = await NetInfo.fetch();
    return {
      isConnected: networkState.isConnected,
      type: networkState.type,
      isSyncing: this.syncInProgress,
    };
  }
}

export const syncService = new SyncService();
