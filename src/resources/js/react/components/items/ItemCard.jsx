import PropTypes from "prop-types";
import StarRating from "./StarRating";
import { BASE_PATH } from "../../../config";

export default function ItemCard({ item, currentUrl }) {

  const from = encodeURIComponent(currentUrl);

  return (
    <li>
      <a href={`${BASE_PATH}/item/${item.id}?from=${from}`}>
        <img src={item.main_image} alt={item.name}/>
        <div className="flex justify-between">
          <p className="text-base!">&yen;{item.price.toLocaleString()}
            <small>(税込)</small>
          </p>
          <div>
            <StarRating avgStar={item.avg_star} />
            <span className="rating-text text-xs">({ item.reviews_count }件)</span>
          </div>
        </div>
        <p>{ item.name }</p>
        <p>{ item.shop_name }</p>
      </a>
    </li>
  )
}

ItemCard.propTypes = {
  item: PropTypes.object.isRequired,
  currentUrl: PropTypes.string.isRequired,
};