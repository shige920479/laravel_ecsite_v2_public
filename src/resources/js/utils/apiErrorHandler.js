import toast from "react-hot-toast";
import { BASE_PATH } from "../config";

export const handleApiError = (error) => {

  switch (error.code) {

    case "UNAUTHENTICATED":
      window.location.href = `${BASE_PATH}/login`;
      return;

    case "VALIDATION_ERROR":
      return error.errors ?? error.data?.errors ;

    case "FORBIDDEN":
      toast.error(error.message);
      break;

    case "CONFLICT_ERROR":
      toast.error(error.message, {
        icon: "⚠️",
      });
      break;

    case "BUSINESS_ERROR":
      toast.error(error.message);
      break;

    case "SERVER_ERROR":
      toast.error("サーバーエラーが発生しました");
      break;

    case "NETWORK_ERROR":
      toast.error("通信環境をご確認ください");
    return;

    default:
      toast.error(error.message || "エラーが発生しました");
  }
}