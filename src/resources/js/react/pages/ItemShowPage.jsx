import { useState } from "react";
import PropTypes from "prop-types";
import ItemImageGallery from "../components/items/ItemImageGallery";
import StarRating from "../components/items/StarRating";
import { del, post } from "../api/favoriteApi";
import { postCart } from "../api/cartApi";
import { handleApiError } from "../../utils/apiErrorHandler";
import toast from "react-hot-toast";
import FieldError from "../components/errors/FieldError";
import { BASE_PATH } from "../../config";

export default function ItemShowPage({ item, isFavorite, isLoggedIn }) {

  const [favorite, setFavorite] = useState(isFavorite);
  const [isCartLoading, setIsCartLoading] = useState(false);
  const [isFavoriteloading, setIsFavoriteLoading] = useState(false);
  const [quantity, setQuantity] = useState(1);
  const [errors, setErrors] = useState({});

  const handleFavoriteClick = async () => {
    if (! isLoggedIn) {
      const currentUrl = (window.location.pathname + window.location.search).replace(BASE_PATH, '') || '/';
      window.location.href = `${BASE_PATH}/login?redirect=${encodeURIComponent(currentUrl)}`;
      return
    }
    if (isFavoriteloading) return;
    setIsFavoriteLoading(true);
    
    const nextState = ! favorite;
    setFavorite(nextState);

    try {
      const url =`/api/items/${item.id}/favorite`;
      nextState ? await post(url) : await del(url);

    } catch (error) {
      setFavorite((prev) => ! prev);
      const validationErrors = handleApiError(error);
      console.log(validationErrors);
    
    } finally {
      setIsFavoriteLoading(false);
    }
  }

  const handleCartIn = async () => {
    setErrors({});
    if (! isLoggedIn) {
      const currentUrl = (window.location.pathname + window.location.search).replace(BASE_PATH, '') || '/';
      window.location.href = `${BASE_PATH}/login?redirect=${encodeURIComponent(currentUrl)}`;
      return;
    }

    if (isCartLoading) return;
    setIsCartLoading(true);

    try {
      const url = "/api/cart";
      const body = {
        'item_id': item.id,
        'quantity': quantity,
      };
      const data = await postCart(url, body);
      toast.success(data.message);
      setQuantity(1);

    } catch (error) {
      const validationErrors = handleApiError(error);
      if (validationErrors) {
        setErrors(validationErrors);
        return;
      }
    
    } finally {
      setIsCartLoading(false);
    }

  }

  return (
      <div className="item-show-flex">
        <ItemImageGallery images={item.images} />
        <div className="item-text">
          <div className="item-detail-info">
            <div>
              <div className="title-favorite-flex">
                <div>
                  {item.is_selling
                      ? (<span>販売中</span>)
                      : (<span className="not-selling">販売停止(現在お取り扱いしておりません)</span>)
                  }
                  <h2 className="page-title">{ item.name }</h2>
                </div>
                <div>
                  <button id="favorite-button"
                    className={`favorite-icon ${favorite ? 'favorited' : ''}`}
                    aria-label="お気に入り"
                    onClick={handleFavoriteClick}
                    disabled={isFavoriteloading}
                  >
                    ♥
                  </button>
                </div>
              </div>

              <a href={`${BASE_PATH}/item/${item.id}/reviews`} className="block w-fit">
                <StarRating avgStar={item.avg_star} />
                <span className="rating-text">
                  ({item.reviews_count} 件)
                </span>
                <div className="block text-indigo-500 w-fit">[ レビューを書く ]</div>
              </a>
            </div>
            <div>
              <div>{ item.shop_name }</div>
              <div>{ item.item_category } / { item.sub_category } / { item.category }</div>
            </div>
            <p>{ item.information }</p>
          </div>
          <hr className="hr" />
          <FieldError errors={errors} field="item_id" className="text-xs text-red-600 mt-1.5" />
          <FieldError errors={errors} field="quantity" className="text-xs text-red-600 mt-1.5" />
          <div>
            <div className="price-quantity">
              <span className="price text-[20px]">
                &yen;{ item.price }
                <small className="text-xs">(税込)</small>
              </span>
              <div>
                <span>在庫</span>
                <div>残り { item.stock_current } 個</div>
              </div>
              <div>
                <span>数量を選択</span>
                <input type="number"
                  name="quantity"
                  value={quantity}
                  onChange={(e) => setQuantity(Number(e.target.value))}
                  min="1"
                  placeholder="1"/>
              </div>
            </div>
            <button 
              className="cart-in cursor-pointer"
              disabled={
                  !item.is_selling ||
                  item.stock_current === 0 ||
                  isCartLoading
              }
              onClick={handleCartIn}
            >
              {! item.is_selling
                  ? "現在、販売を停止しております"
                  : item.stock_current === 0
                    ? "現在、入荷待ちです"
                    : (isLoggedIn
                        ? "カートに入れる"
                        : "ログインしてカートに入れる"
                      )
              }
            </button>
          </div>
        </div>
      </div>
  )

}
ItemShowPage.propTypes = {
  item: PropTypes.object.isRequired,
  isFavorite: PropTypes.bool.isRequired,
  isLoggedIn: PropTypes.bool.isRequired,
}