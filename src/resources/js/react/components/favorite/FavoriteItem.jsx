import PropTypes from "prop-types"
import { BASE_PATH } from "../../../config"

export default function FavoriteItem({items, onDelete, onMoveToCart, processingId}) {

  return (
    <>
      {items.map((item) => (
        
        <div className="favorite w-full" key={item.id}>
          <hr className="hr"/>
          <div className="favorite-box">
            <div className="favorite-img">
              <a href={`${BASE_PATH}/item/${item.id}`}>
                <img
                src={item.main_image}
                alt="favorite-image" />
              </a>
            </div>
            <div className="favorite-info">
              <ul>
                <li>{item.name}</li>
                <li><small>商品番号:</small>{item.id}</li>
                <li><small>ショップ:</small>{item.shop_name}</li>
              </ul>
              <div className="price-quantity">
                <div>
                  &yen;<span className="unit-price text-12">{item.price.toLocaleString()}</span> (税込)
                </div>
                <div>
                  <button
                    className="favorite-del-btn"
                    onClick={() => onDelete(item.id)}
                    disabled={item.id === processingId}
                  >解除
                  </button>
                </div>
                <div>
                  {item.is_selling
                    ? (
                        <button
                          className="favorite-to-cart"
                          onClick={() => onMoveToCart(item.id)}
                          disabled={item.id === processingId}
                        >
                          <img src={`${BASE_PATH}/images/cart-white.png`} className="cart-white"/>
                          カートに入れる
                        </button>
                    ) 
                    : (
                        <p className="bg-red-400 text-white py-2.5 px-8 rounded">
                          販売停止となりました
                        </p>
                    )
                  }
                </div>
              </div>
            </div>
          </div>
        </div>
      ))}
    </>
  )
}

FavoriteItem.propTypes = {
  items: PropTypes.object.isRequired,
  onDelete: PropTypes.func.isRequired,
  onMoveToCart: PropTypes.func.isRequired,
  processingId: PropTypes.number,
}