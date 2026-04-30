import { apiClient } from "./client";

export const getFavorite = (url) =>
  apiClient(url, {
    method: "GET",
  })

export const post = (url, body = {}) => 
  apiClient(url, {
    method: "POST",
    body: JSON.stringify(body),
  });

export const del = (url) =>
  apiClient(url, {
    method: "DELETE",
  });

