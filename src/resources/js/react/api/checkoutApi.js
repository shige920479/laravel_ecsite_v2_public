import { apiClient } from "./client"

export const checkoutApi = (url, body = {}, options = {}) => {

  return apiClient(url, {
    method: "POST",
    body: JSON.stringify(body),
    ...options,
  })
}