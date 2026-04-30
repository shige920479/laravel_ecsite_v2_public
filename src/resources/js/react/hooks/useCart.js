import { useEffect, useRef, useState } from "react";
import { changeQuantity, deleteCart, getCart } from "../api/cartApi";
import { handleApiError } from "../../utils/apiErrorHandler";
import toast from "react-hot-toast";

export const useCart = (cartErrors) => {

  const [cartItems, setCartItems] = useState([]);
  const [isPageLoading, setIsPageLoading] = useState(true);
  const [errors, setErrors] = useState(cartErrors);
  const [loadingCartId, setLoadingCartId] = useState(null);
  const [deleteLoadingId, setDeleteLoadingId] = useState(null);
  const isUpdating = useRef(false);
  const [orderIds, setOrderIds] = useState([]);

  useEffect(() => {

    setIsPageLoading(true);

    const fetchData = async () => {
      try {
        const data = await getCart('/api/cart');
        setCartItems(data.data);

      } catch (error) {
        const validationErrors = handleApiError(error);
        if (validationErrors) setErrors(validationErrors);
        
      } finally {
        setIsPageLoading(false);
      }
    }

    fetchData();
  }, []);

  useEffect(() => {
    const cartIds = cartItems.filter((cart) => cart.is_selling).map(c => c.id);

    setOrderIds(prev => {
      if (prev.length === 0) return cartIds;
      return prev.filter(id => cartIds.includes(id));
    });
  }, [cartItems]);

  const handleDelete = async (id) => {

    setDeleteLoadingId(id);
    try {
      const data = await deleteCart(`/api/cart/${id}`);
      setCartItems(prev =>
        prev.filter(cart => cart.id !== id)
      );
      toast.success(data.message);

    } catch (error) {
      handleApiError(error);

    } finally {
      setDeleteLoadingId(null);
    }
  }

  const handleChangeQuantity = async (id, diff) => {

    if (isUpdating.current) return;
    isUpdating.current = true;
    
    setLoadingCartId(id);
    
    try {
      const currentCart = cartItems.find(cart => cart.id === id);
      if (! currentCart) return;

      const newQty = currentCart.quantity + Number(diff);
      if (newQty < 1) return;

      const data = await changeQuantity(
        `/api/cart/${id}`,
         {quantity: newQty},
      )

      setCartItems(prev =>
        prev.map(cart =>
          cart.id === id
            ? {...cart, quantity: newQty}
            : cart
        )
      );

      setErrors(prev => {
        const next = { ...prev };
        delete next[id];
        return next;
      });

      toast.success(data.message);

    } catch (error) {
      if (error.name !== "AbortError") {
        const validationErrors = handleApiError(error);
        if (validationErrors) {
          setErrors(prev => ({
            ...prev,
            [id]: validationErrors
          }))
        }
      }
    
    } finally {
      setLoadingCartId(prev => prev === id ? null : prev);
      isUpdating.current = false;
    }
  }

  const handleOrderCheck = (cartId, checked) => {
    if(checked) {
      setOrderIds(prev => [...prev, cartId]);
    } else {
      setOrderIds(prev => prev.filter(id => id !== cartId));
    }
  }

  return {
    cartItems,
    isPageLoading,
    loadingCartId,
    handleDelete,
    deleteLoadingId,
    handleChangeQuantity,
    errors,
    handleOrderCheck,
    orderIds
  }
}