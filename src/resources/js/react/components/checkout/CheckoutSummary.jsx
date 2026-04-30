import PropTypes from "prop-types"

export default function OrderSummary({totalAmount, route, onSubmit}) {
  return (
    <>
      <div className="confirm-total-box">
        <p>ご購入金額</p>
        <div className="confirm-total">&yen; 
          <span id="order-total-amount">{totalAmount.toLocaleString()}</span>
        </div>
      </div>

      <div className="confirm-buttons">
        <a href={route} className="order-btn btn-back">カートに戻る</a>
        <div>
          <button
            className="order-btn btn-primary"
            onClick={onSubmit}
          >
            注文を確定する
          </button>
        </div>
      </div>
    </>
  )
}

OrderSummary.propTypes = {
  totalAmount: PropTypes.number.isRequired,
  route: PropTypes.string.isRequired,
  onSubmit: PropTypes.func.isRequired,
}