import { buildReviewUrl } from "../../utils/urlBuilders";
import { apiClient } from "./client"

export const getMyReviews = (filters, options = {}) => {
  
  const url = buildReviewUrl(filters);

  return apiClient(url, {
    method: "GET",
    ...options,
  });
}