import PropTypes from "prop-types";
import FieldError from "../errors/FieldError";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faSpinner } from "@fortawesome/free-solid-svg-icons";

export default function CartItem({ 
  cart, isUpdateLoading, onDelete, isDeleteLoading, onChangeQuantity, errors, handleOrderCheck, orderIds
}) 
{
  return (
    <div className="cart" key={cart.id}>
      <hr className="hr" />
      <div className="cart-box">
        <div className="cart-img">
          <img src={cart.main_image} alt="" />
        </div>
        <div className="cart-info">
          <ul>
            {errors?.[cart.id]?.quantity && (
              <FieldError errors={errors?.[cart.id]} field="quantity" className="text-sm text-red-600 mb-2"/>
            )}
            <li>
              <div className="flex justify-between">
                <p>{cart.item_name}</p>
                {! cart.is_selling && (
                  <p className="bg-red-400 text-white py-0.5 px-2 rounded">販売停止となりました</p>
                )}                
              </div>
            </li>
            <li><small>商品番号:</small>{cart.item_id}</li>
            <li><small>ショップ:</small>{cart.shop_name}</li>
          </ul>
          <div className="price-del-update">
            <div>
              &yen;<span className="unit-price text-12">{cart.item_price.toLocaleString()}</span> (税込)
            </div>
            <div className="flex gap-10 items-center">
              <button 
                className="cartitem-del-btn cursor-pointer"
                onClick={() => onDelete(cart.id)}
                disabled={isDeleteLoading}
              >削除
              </button>
              <label className="text-xs flex items-center">注文する
                <input
                  type="checkbox"
                  checked={cart.is_selling && orderIds.includes(cart.id)} 
                  onChange={(e) => handleOrderCheck(cart.id, e.target.checked)}
                  className="ml-1.5 h-full"
                  disabled={! cart.is_selling}
                />
              </label>
            </div>
            <div>
              <div className="quantity-wrapper">
              <span className="text-xs mr-2">数量を選択</span>
                <button 
                  onClick={() => onChangeQuantity(cart.id, -1)}
                  disabled={cart.quantity <= 1 || isUpdateLoading}
                  className="qty-btn"
                >-
                </button>
                <span className={`qty-value ${isUpdateLoading ? "loading" : ""}`}>
                  {cart.quantity}
                </span>
                <button
                  onClick={() => onChangeQuantity(cart.id, +1)}
                  className="qty-btn"
                  disabled={isUpdateLoading}
                >+
                </button>
                {isUpdateLoading && (
                  <FontAwesomeIcon
                    icon={faSpinner}
                    spin
                    className="qty-spinner"
                  />
                )}
              </div>
            </div>
          </div>
          <hr className="hr-subtotal" />
          <div className="item-subtotal">
            <p>商品小計</p>
            <div>
              <span className="subtotal-calc">
                {(cart.item_price * cart.quantity).toLocaleString()}
              </span>円 (税込)
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

CartItem.propTypes = {
  cart: PropTypes.object.isRequired,
  isUpdateLoading: PropTypes.bool.isRequired,
  onDelete: PropTypes.func.isRequired,
  isDeleteLoading: PropTypes.bool.isRequired,
  onChangeQuantity: PropTypes.func.isRequired,
  errors: PropTypes.object,
  handleOrderCheck: PropTypes.func.isRequired,
  orderIds: PropTypes.array.isRequired
};
