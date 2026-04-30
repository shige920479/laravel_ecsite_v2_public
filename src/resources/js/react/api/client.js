import { BASE_PATH } from "../../config";

export const apiClient = async (url, options = {}) => {
  try {
    const fullUrl = url.startsWith("http") ? url : `${BASE_PATH}${url}`;

    const res = await fetch(fullUrl, {
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": CSRF_TOKEN,
        ...options.headers,
      },
      ...options,
    });

    // 204 No Content
    if (res.status === 204) return null;

    // JSON以外対策
    const contentType = res.headers.get("content-type");
    let data = null;
    if (contentType && contentType.includes("application/json")) {
        data = await res.json();
    }

    // Laravelはエラー時に必ず適切なHTTPステータスを返す設計
    if (! res.ok || data?.success === false) {
      const error = new Error(data?.message ?? "HTTPエラー");
      error.status = res.status;
      error.code = data?.code ?? "HTTP_ERROR";

      // ★ここが最重要：errors をトップレベルにコピー
      error.errors = data?.errors ?? null;

      // ★念のため data も保持（デバッグ・後方互換）
      error.data = data ?? null;

      throw error;
    }
  
    return data;

  } catch (error) {
    if (error.name === "AbortError") {
      throw error;
    }

    // 既に整形済みならそのまま
    if (error.code) throw error;
    
    // 通信エラー（fetchが失敗した場合）
    const networkError = new Error("ネットワークエラーが発生しました。");
    networkError.status = 0;
    networkError.code = "NETWORK_ERROR";
    networkError.errors = null;
    networkError.data = null;

    throw networkError;
  }
}