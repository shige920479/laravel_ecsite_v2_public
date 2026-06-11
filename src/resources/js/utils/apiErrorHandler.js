import toast from "react-hot-toast";
import { BASE_PATH } from "../config";

export const handleApiError = (error) => {

  switch (error.code) {

    case "UNAUTHENTICATED": {
      const currentUrl = 
        (window.location.pathname + window.location.search)
          .replace(BASE_PATH, '') || '/';

      window.location.href =
        `${BASE_PATH}/login?redirect=${encodeURIComponent(currentUrl)}`;

      return;
    }

    case "VALIDATION_ERROR": {
      return error.errors ?? error.data?.errors ;
    }

    case "FORBIDDEN": {
      toast.error(error.message);
      return;
    }

    case "CONFLICT_ERROR": {
      toast.error(error.message, {
        icon: "⚠️",
      });
      return;
    }

    case "BUSINESS_ERROR": {
      toast.error(error.message);
      return;
    }

    case "SERVER_ERROR": {
      toast.error("サーバーエラーが発生しました");
      return;
    }

    case "NETWORK_ERROR": {
      toast.error("通信環境をご確認ください");
      return;
    }

    default: {
      toast.error(error.message || "エラーが発生しました");
    }
  }
}