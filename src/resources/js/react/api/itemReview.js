import { apiClient } from "./client"


export const getReviews = (url, options = {}) => {
  return apiClient(url, {
    method: "GET",
    ...options,
  });
}

export const postReview = (url, body = {}, options = {}) => {
  return apiClient(url, {
    method: "POST",
    body: JSON.stringify(body),
    ...options,
  })
}

export const updateReview = (url, body = {}, options = {}) => {
  return apiClient(url, {
    method: "PATCH",
    body: JSON.stringify(body),
    ...options,
  })
}

export const deleteReview = (url, options = {}) => {
  return apiClient(url, {
    method: "DELETE",
    ...options,
  })
}

export const toggleHelpful = (url, options = {}) => {
  return apiClient(url, {
    method: "POST",
    ...options,
  })
}