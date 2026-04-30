import PropTypes from "prop-types";

import FieldError from "../errors/FieldError";
import { BASE_PATH } from "../../../config";

export default function CartSummary({ totalAmount, errors, orderIds}) {

  return (
      <div id="cart-total">
        <div id="cart-total-flex">
          <div>
            <FieldError errors={errors} field="ids" className="text-sm text-red-600 mb-2"/>
            <p>ご注文金額合計</p>
            <div><span id="total-price">{totalAmount.toLocaleString()}</span>円</div>
          </div>
          <form action={`${BASE_PATH}/checkout/confirm`} method="post">
            <button type="submit" className="btn cursor-pointer">注文に進む</button>
            <input
              type="hidden"
              name="_token"
              value={document.querySelector('meta[name="csrf-token"]').content}
            />
            {orderIds.map(id => (
              <input key={id} type="hidden" name="ids[]" value={id}/>
            ))}
            
          </form>
        </div>
      </div>
  )
}

CartSummary.propTypes = {
  totalAmount: PropTypes.number.isRequired,
  errors: PropTypes.object,
  orderIds: PropTypes.array,
}