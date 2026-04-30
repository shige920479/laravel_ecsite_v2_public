import { useState } from "react";
import PropTypes from "prop-types";
import OrderItem from "../components/checkout/CheckoutItem";
import OrderSummary from "../components/checkout/CheckoutSummary";
import { checkoutApi } from "../api/checkoutApi";
import { handleApiError } from "../../utils/apiErrorHandler";
import FieldError from "../components/errors/FieldError";

export default function CheckoutPage({checkoutConfig}) {

  const {carts, route} = checkoutConfig;
  const [checkoutItems, setCheckoutItems] = useState(carts);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState(null);

  const removeFromConfirm = (cartId) => {
    setCheckoutItems(prev =>
      prev.filter(cart => cart.id !== cartId)
    );
  }

  const checkoutSubmit = async () => {
    if (loading) return;

    const url = "/api/checkout";
    const body = {
      "ids": checkoutItems.map(cart => cart.id)
    }
    
    try {
      setLoading(true);
      setErrors(null);

      const res = await checkoutApi(url, body);
      const stripeUrl = res?.data?.checkout_url;

      if (! stripeUrl) {
        setErrors({ids: ["決済画面の作成に失敗しました"]});
        return;
      }

      window.location.href = stripeUrl;

    } catch (error) {
      const validationErrors = handleApiError(error);
      if (validationErrors) setErrors(validationErrors);
    
    } finally {
      setLoading(false);
    }
  }

  const totalAmount = checkoutItems.reduce(
    (sum, cart) => sum + cart.subtotal, 0
  );

  return (
    <>
      <h1>注文内容の確認</h1>
      <FieldError errors={errors} field="ids" className="text-red-500 text-sm"/>
      <div className="confirm-item-header">
        <span className="w30">商品名</span>
        <span>数量</span>
        <span>小計</span>
        <span className="w30">リストから削除</span>
      </div>

      {checkoutItems.map(cart => (
          <OrderItem key={cart.id} cart={cart} onRemove={removeFromConfirm}/>
        )
      )}
      {checkoutItems.length > 0 && (
        <OrderSummary totalAmount={totalAmount} route={route} onSubmit={checkoutSubmit}/>
      )}
    </>
  )

}
CheckoutPage.propTypes = {
  checkoutConfig: PropTypes.object.isRequired,
}