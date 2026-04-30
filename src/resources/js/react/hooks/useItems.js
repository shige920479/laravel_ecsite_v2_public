import { useState, useEffect } from 'react';
import { fetchItemsApi } from '../api/itemsApi';
import { handleApiError } from '../../utils/apiErrorHandler';
import toast from 'react-hot-toast';
import { buildItemsUrl } from '../../utils/urlBuilders';
import { BASE_PATH } from '../../config';

export const useItems = (filters) => {

  const [items, setItems] = useState([]);
  const [meta, setMeta] = useState(null);
  const [isInitialLoading, setIsInitialLoading] = useState(true);
  const [isFetching, setIsFetching] = useState(false);
  const [currentUrl, setCurrentUrl] = useState(null);
  
  useEffect(() => {

    window.scrollTo({ top: 0, behavior: "smooth" });
    const controller = new AbortController();

    setIsFetching(true);

    const fetchData = async () => {
      try {
        const data = await fetchItemsApi(filters, controller.signal);

        setItems(data.data);
        setMeta(data.meta);
        
        let url = buildItemsUrl(filters);

         url = filters.category
         ? BASE_PATH + '/category' + url
         : BASE_PATH + url;

        setCurrentUrl(url);

      } catch (error) {
        if (error.name !== "AbortError") {
          const validationErrors = handleApiError(error);
          
          if(validationErrors) {
            const message = Object.values(validationErrors)[0][0];
            toast.error(message);
          } 
        }
      } finally {
        if (!controller.signal.aborted) {
          setIsFetching(false)
          setIsInitialLoading(false);
        }
      }
    }

    fetchData();

    return () => controller.abort();
    
  }, [filters])

  return {items, meta, isInitialLoading, isFetching, currentUrl};  
}