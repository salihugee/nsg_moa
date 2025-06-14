export interface ValidationError {
  message: string;
  field?: string;
}

export interface Location {
  latitude: number;
  longitude: number;
  timestamp?: number;
}

export interface FormField {
  name: string;
  label: string;
  type: 'text' | 'number' | 'date' | 'location' | 'select' | 'multiselect';
  required?: boolean;
  options?: string[];
  value?: any;
  error?: string;
}

export interface DataCollectionForm {
  id?: string;
  timestamp: Date;
  location: Location;
  farmerId: string;
  farmerName: string;
  cropType: string[];
  farmSize: number;
  visitDate: Date;
  notes?: string;
  status: 'draft' | 'pending' | 'synced';
  lastModified: Date;
}
