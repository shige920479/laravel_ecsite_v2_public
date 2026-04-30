import PropTypes from "prop-types";

export default function StarRating({avgStar}) {
  const starWidth = (avgStar / 5) * 100;

  return (
    <div className="stars text-xs!">
      <div className="stars-filled" style={{ width: `${starWidth}%` }}></div>
    </div>
  )
}

StarRating.propTypes = {
  avgStar: PropTypes.number.isRequired
}
