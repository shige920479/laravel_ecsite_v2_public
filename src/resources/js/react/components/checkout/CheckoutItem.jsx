import PropTypes from "prop-types"

export default function OrderItem({cart, onRemove}) {
  
  return (
    <div className="confirm-item">
      <span className="w30">{cart.item_name}</span>
      <span className="quantity">{cart.quantity}個</span>
      <span>&yen; <span className="amount">{ cart.subtotal.toLocaleString()}</span> <small>(税込)</small></span>
      <div className="confirm-actions w30">
        <div className="inline-form">
          <button 
            className="order-btn btn-secondary cart-back-btn"
            onClick={() => onRemove(cart.id)}
          >
            カートへ戻す
          </button>
        </div>
      </div>
    </div>
  )
}

OrderItem.propTypes = {
  cart: PropTypes.object.isRequired,
  onRemove: PropTypes.func.isRequired,
}