import { useEffect, useState } from "react";
import { getReviews } from "../../api/itemReview";
import { handleApiError } from "../../../utils/apiErrorHandler";
import toast from "react-hot-toast";

export const useReviews = ({itemId}) => {

  const [allReviews, setAllReviews] = useState([]);
  const [item, setItem] = useState({});
  const [ratingSummary, setRatingSummary] = useState({});
  const [user, setUser] = useState(null);
  const [isInitialLoading, setIsInitialLoading] = useState(true);

  useEffect(() => {

    const fetchData = async () => {
      const url = `/api/item/${itemId}/reviews`;

      try {
        const res = await getReviews(url);

        setAllReviews(res.data);
        setItem(res.meta.item);
        setRatingSummary(res.meta.rating_summary);
        setUser(res.meta.user);

      } catch (error) {
        const validationErrors = handleApiError(error);
        
        if(validationErrors) {
          const message = Object.values(validationErrors)[0][0];
          toast.error(message);
        } 

      } finally {
        setIsInitialLoading(false);
      }
    }

    fetchData();
  
  }, [itemId]);

  return {
    allReviews,
    setAllReviews,
    item,
    ratingSummary,
    user,
    isInitialLoading
  };
}