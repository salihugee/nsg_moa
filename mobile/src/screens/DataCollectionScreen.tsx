import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView } from 'react-native';
import { 
  Button, 
  Text, 
  Card, 
  FAB, 
  List,
  ActivityIndicator,
  useTheme
} from 'react-native-paper';
import { useNavigation } from '@react-navigation/native';
import { useTranslation } from 'react-i18next';
import { databaseService } from '../services/DatabaseService';
import { syncService } from '../services/SyncService';
import { DataRecord } from '../services/DatabaseService';

const DataCollectionScreen = () => {
  const { t } = useTranslation();
  const navigation = useNavigation();
  const theme = useTheme();
  const [records, setRecords] = useState<DataRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [syncing, setSyncing] = useState(false);
  const [connectionStatus, setConnectionStatus] = useState<{
    isConnected: boolean;
    type: string;
  }>({ isConnected: false, type: 'none' });

  useEffect(() => {
    loadRecords();
    checkConnectionStatus();
  }, []);

  const loadRecords = async () => {
    try {
      const farmData = await databaseService.getRecordsByType('farm_data');
      const weatherData = await databaseService.getRecordsByType('weather_data');
      setRecords([...farmData, ...weatherData]);
    } catch (error) {
      console.error('Failed to load records:', error);
    } finally {
      setLoading(false);
    }
  };

  const checkConnectionStatus = async () => {
    const status = await syncService.getConnectionStatus();
    setConnectionStatus({
      isConnected: status.isConnected,
      type: status.type
    });
    setSyncing(status.isSyncing);
  };

  const handleSync = async () => {
    setSyncing(true);
    try {
      await syncService.syncPendingRecords();
      await loadRecords();
    } finally {
      setSyncing(false);
      await checkConnectionStatus();
    }
  };

  const handleNewRecord = () => {
    navigation.navigate('NewRecord' as never);
  };

  const renderRecord = (record: DataRecord) => (
    <Card 
      key={record.id} 
      style={styles.card}
      onPress={() => navigation.navigate(
        'RecordDetail' as never,
        { recordId: record.id } as never
      )}
    >
      <Card.Content>
        <Text style={styles.recordType}>
          {t(record.type)}
        </Text>
        <Text style={styles.date}>
          {new Date(record.created_at).toLocaleDateString()}
        </Text>
        <List.Item
          title={t('status')}
          description={t(record.status)}
          left={props => (
            <List.Icon
              {...props}
              icon={record.status === 'synced' ? 'check-circle' : 'clock-outline'}
              color={record.status === 'synced' ? theme.colors.success : theme.colors.warning}
            />
          )}
        />
      </Card.Content>
    </Card>
  );

  if (loading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>{t('dataCollection')}</Text>
        <View style={styles.connectionInfo}>
          <Text>
            {connectionStatus.isConnected 
              ? t('connected', { type: connectionStatus.type })
              : t('offline')
            }
          </Text>
          <Button
            mode="outlined"
            onPress={handleSync}
            loading={syncing}
            disabled={!connectionStatus.isConnected || syncing}
          >
            {t('sync')}
          </Button>
        </View>
      </View>

      <ScrollView style={styles.recordList}>
        {records.map(renderRecord)}
      </ScrollView>

      <FAB
        style={styles.fab}
        icon="plus"
        onPress={handleNewRecord}
        label={t('newRecord')}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    padding: 16,
    backgroundColor: 'white',
    elevation: 2,
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    marginBottom: 8,
  },
  connectionInfo: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  recordList: {
    padding: 16,
  },
  card: {
    marginBottom: 8,
  },
  recordType: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  date: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  fab: {
    position: 'absolute',
    margin: 16,
    right: 0,
    bottom: 0,
  },
  centered: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
});

export default DataCollectionScreen;
