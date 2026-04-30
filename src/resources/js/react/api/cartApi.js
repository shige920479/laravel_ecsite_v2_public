import { apiClient } from "./client"

export const postCart = (url, body = {}, options = []) => {
  return apiClient(url, {
    method: "POST",
    body: JSON.stringify(body),
    ...options
  });
}

export const getCart = (url, options = {}) => {
  return apiClient(url, {
    method: "GET",
    ...options
  })
}

export const changeQuantity = (url, body = {}, options = {}) => {
  return apiClient(url, {
    method: "PATCH",
    body: JSON.stringify(body),
    ...options
  })
}

export const deleteCart = (url, options = {}) => {
  return apiClient(url, {
    method: "DELETE",
    ...options
  })
}