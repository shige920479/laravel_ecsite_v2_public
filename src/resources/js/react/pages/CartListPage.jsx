import { BASE_PATH } from "../../config";
import CartItem from "../components/cart/CartItem";
import CartSummary from "../components/cart/CartSummary";
import FieldError from "../components/errors/FieldError";
import { useCart } from "../hooks/useCart";
import PropTypes from "prop-types";

export default function CartListPage({cartErrors}) {
  
  const {
    cartItems,
    isPageLoading,
    loadingCartId,
    handleDelete,
    deleteLoadingId,
    handleChangeQuantity,
    errors,
    handleOrderCheck,
    orderIds,
  } = useCart(cartErrors);

  const orderItems = cartItems.filter((cart) => orderIds.includes(cart.id));
  const totalAmount = orderItems.reduce(
    (sum, cart) => sum + (cart.item_price * cart.quantity) ,
  0);

  if (isPageLoading) return null;

  return (
    <>
      <h2 className="text-2xl font-bold">カート</h2>
      <FieldError errors={errors} field="account" className="text-sm text-red-600 mb-2 bg-amber-200 p-2 text-center"/>
      {cartItems.length === 0
        ? (
            <p className="mt-10">未だカートに商品がありません</p>
        )
        : (
            <div className="cart-flex">
              <div id="cart-list-side">
                {cartItems.map(cart => (
                  <CartItem
                    key={cart.id}
                    cart={cart}
                    isUpdateLoading={loadingCartId === cart.id}
                    onDelete={handleDelete}
                    isDeleteLoading={deleteLoadingId === cart.id}
                    onChangeQuantity={handleChangeQuantity}
                    errors={errors}
                    handleOrderCheck={handleOrderCheck}
                    orderIds={orderIds}
                  />
                ))}
                <hr className="hr" />
              </div>
              <CartSummary totalAmount={totalAmount} orderIds={orderIds} errors={errors} />
            </div>
        )
      }
      <a className="link-text" href={`${BASE_PATH}/`}>一覧ページへ戻る</a>
    </>
  )
}

CartListPage.propTypes = {
  cartErrors: PropTypes.array,
}