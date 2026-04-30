import { buildItemsUrl } from '../../utils/urlBuilders';
import { apiClient } from './client';

export const fetchItemsApi = (filters, signal) => {
  
  const params = buildItemsUrl(filters);

  return apiClient(`/api/items${params}`, {signal});
};