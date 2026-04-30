import { useEffect, useState } from "react";
import { del, getFavorite, post } from "../api/favoriteApi";
import { handleApiError } from "../../utils/apiErrorHandler";
import toast from "react-hot-toast";

export const useFavorite = () => {

  const [favoriteItems, setFavoriteItems] = useState([]);
  const [isPageLoading, setIsPageLoading] = useState(true);
  const [processingId, setProcessingId] = useState(null);

  useEffect(() => {

    setIsPageLoading(true);

    const fetchdata = async () => {
      try {
        const data = await getFavorite('/api/favorite');
        setFavoriteItems(data.data);

      } catch (error) {
        handleApiError(error);

      } finally {
        setIsPageLoading(false);
      }
    }
    fetchdata();
  }, []);

  const handleDelete = async (id) => {

    if (processingId) return;
    setProcessingId(id);

    try {
      await del(`/api/items/${id}/favorite`);
      
      setFavoriteItems(prev => 
        prev.filter(item => item.id !== id)
      );
      
      toast.success(`お気に入りから1件削除しました`);

    } catch (error) {
      handleApiError(error);
    
    } finally {
      setProcessingId(null);
    }

  }

  const moveToCart = async (id) => {

    if (processingId) return;
    setProcessingId(id);

    try {
      const data = await post(`/api/items/${id}/moveToCart`);
      setFavoriteItems(prev =>
        prev.filter(item => item.id !== id)
      );

      toast.success(data.message);

    } catch (error) {
      handleApiError(error);

    } finally {
      setProcessingId(null);
    }
  }

  return {
    favoriteItems,
    isPageLoading,
    processingId,
    handleDelete,
    moveToCart,
  }

}