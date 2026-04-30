import PropTypes from "prop-types";
import ItemCard from "./ItemCard";
import ItemCardSkeleton from "../ui/ItemCardSkeleton";

export default function ItemList({ items = [], isFetching, currentUrl }) {

  if (!items || items.length === 0) {
    return <p>商品がありません</p>;
  }

  return (
    <ul className="product-list">
      {isFetching
        ? items.map(item => (
          <ItemCardSkeleton key={item.id} />
        ))
        : items.map(item => (
          <ItemCard item={item} currentUrl={currentUrl} key={item.id} />
        ))
      }
    </ul>
  );
}

ItemList.propTypes = {
  items: PropTypes.array.isRequired,
  isFetching: PropTypes.bool.isRequired,
  currentUrl: PropTypes.string.isRequired,
};
