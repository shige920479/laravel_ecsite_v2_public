import { createRoot } from "react-dom/client";
import ItemsPage from "./pages/ItemsPage";
import ItemShowPage from "./pages/ItemShowPage";
import { Toaster } from "react-hot-toast";
import CartListPage from "./pages/CartListPage";
import FavoriteListPage from "./pages/FavoriteListPage";
import CheckoutPage from "./pages/CheckoutPage";
import ReviewPage from "./pages/ReviewsPage";
import MyReviewsPage from "./pages/MyReviewsPage";
import { BASE_PATH } from "../config";

const itemsRoot = document.getElementById('items-root');
const itemShowRoot = document.getElementById('item-show-root');
const cartRoot = document.getElementById('cart-index-root');
const favoriteRoot = document.getElementById('favorite-root');
const checkoutRoot = document.getElementById('checkout-confirm-root');
const reviewsRoot = document.getElementById('reviews-root');
const myReviewRoot = document.getElementById('my-review-root');

// 商品一覧ページ
if (itemsRoot) {
  const config = JSON.parse(itemsRoot.dataset.config || "{}");
  const categories = JSON.parse(itemsRoot.dataset.categories || "{}");

  createRoot(itemsRoot).render(
  <>
    <Toaster
      position="top-right" toastOptions={{duration: 3000, style: {background: "#333", color: "#fff",},}}
    />
    <ItemsPage itemIndexConfig={config} categories={categories}/>
  </>
  );
}

// 商品詳細ページ
if (itemShowRoot) {
  const config = JSON.parse(itemShowRoot.dataset.config || "{}");
  const prevUrl = itemShowRoot.dataset.url;

  const {
    item = {},
    isFavorite = false,
    isLoggedIn = false,
  } = config;

  createRoot(itemShowRoot).render(
    <>
      <Toaster
        position="top-right" toastOptions={{duration: 3000, style: {background: "#333", color: "#fff",},}}
      />
      <ItemShowPage 
        item={item}
        isFavorite={isFavorite}
        isLoggedIn={isLoggedIn}
      />
      <a className="link-text" href={prevUrl}>
         <span><img className="w-10" src={`${BASE_PATH}/images/prev.png`} alt="" /></span>
         <span>前のページへ戻る</span>
      </a>
    </>
  );

}
// Cart一覧
if (cartRoot) {
  const config = JSON.parse(cartRoot.dataset.config || "{}");
  const cartErrors = config.errors || {};

  createRoot(cartRoot).render(
    <>
      <Toaster
        position="top-right" toastOptions={{duration: 3000, style: {background: "#333", color: "#fff",},}}
      />
      <CartListPage cartErrors={cartErrors}/>
    </>
  )
}
// お気に入り一覧
if (favoriteRoot) {
  createRoot(favoriteRoot).render(
    <>
    <Toaster
      position="top-right" toastOptions={{duration: 3000, style: {background: "#333", color: "#fff",},}}
    />
      <FavoriteListPage/>
    </>
  )
}
// 注文確認
if (checkoutRoot) {
  const config = JSON.parse(checkoutRoot.dataset.config || "{}");

  createRoot(checkoutRoot).render(
    <>
    <Toaster
      position="top-right" toastOptions={{duration: 3000, style: {background: "#333", color: "#fff",},}}
    />
      <CheckoutPage checkoutConfig={config}/>
    </>
  )
}

// レビュー一覧ページ
if (reviewsRoot) {
  const itemId = JSON.parse(reviewsRoot.dataset.itemId || '');

  createRoot(reviewsRoot).render(
    <>
    <Toaster
      position="top-center" toastOptions={{duration: 3000, style: {background: "#333", color: "#fff",},}}
    />
    <ReviewPage itemId={itemId}/>
    </>
  )
}

// ユーザーレビュー一覧ページ
if (myReviewRoot) {
  createRoot(myReviewRoot).render(
    <>
      <Toaster
        position="top-center" toastOptions={{duration: 3000, style: {background: "#333", color: "#fff",},}}
      />
      <MyReviewsPage />
    </>
  )

}