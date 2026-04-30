import { useEffect, useState } from "react"
import { handleApiError } from "../../../utils/apiErrorHandler";
import { getMyReviews } from "../../api/MyReview";

export const useMyReviews = (filters) => {

  const [isInitialLoading, setIsInitialLoading] = useState(true);
  const [allReviews, setAllReviews] = useState([]);
  const [meta, setMeta] = useState({});

  useEffect(() => {

    const fetchData = async() => {
      try {
        const res = await getMyReviews(filters);
        setAllReviews(res.data);
        setMeta(res.meta);
  
      } catch (error) {
        handleApiError(error);
  
      } finally {
        setIsInitialLoading(false);
      }
    }

    fetchData();

  }, [filters]);


  return {isInitialLoading, allReviews, setAllReviews, meta}
}