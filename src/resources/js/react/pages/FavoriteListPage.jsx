import { BASE_PATH } from "../../config";
import FavoriteItem from "../components/favorite/FavoriteItem";
import { useFavorite } from "../hooks/useFavorite";

export default function FavoriteListPage() {
  
  const {
    favoriteItems,
    isPageLoading,
    processingId,
    handleDelete,
    moveToCart,
  } = useFavorite();

  if (isPageLoading) return null;

  return (
    <>
      <h2 className="text-2xl font-bold">お気に入り</h2>

      {favoriteItems.length === 0
        ? (
            <>
              <p className="mx-10 my-20">現在、お気に入り登録はありません</p>
            </>
          )
        : (
            <>
              <div className="favorite-list">
                <FavoriteItem
                  items={favoriteItems}
                  onDelete={handleDelete}
                  onMoveToCart={moveToCart}
                  processingId={processingId}
                />
              </div>
              <hr className="hr"/>
            </>
          )
      }
      <a className="link-text" href={`${BASE_PATH}/`}>一覧ページへ戻る</a>
    </>
  );
}