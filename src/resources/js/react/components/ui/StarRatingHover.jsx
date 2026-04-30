import { useState } from "react";
import PropTypes from "prop-types";

export default function StarRatingHover({form, handleChange}) {
  const [hover, setHover] = useState(0);

  return (
    <div className="inline-block ml-3 cursor-pointer space-x-1 text-3xl text-gray-400">
      {[1,2,3,4,5].map(rating => (
        <span
          key={rating}
          style={{
            cursor: 'pointer',
            color: (hover || form.star) >= rating ? '#f59e0b' : '#ccc'
          }}
          onMouseEnter={() => setHover(rating)}
          onMouseLeave={() => setHover(0)}
          onClick={() => handleChange('star', rating)}
        >
          ★
        </span>
      ))}
    </div>
  );
}

StarRatingHover.propTypes = {
  form: PropTypes.object.isRequired,
  handleChange: PropTypes.func.isRequired,
}