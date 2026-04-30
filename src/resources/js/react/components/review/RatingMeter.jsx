import PropTypes from "prop-types";

export default function RatingMeter({ ratingSummary, filterByStar, handleOpen}) {
  
  const starWidth = (ratingSummary.avgStar / 5) * 100;

  return (

    <>

    <h2>カスタマーレビュー</h2>
    <div className="stars rating">
      <div className="stars-filled" style={{ width: `${starWidth}%` }}></div>
    </div>
    <span className="rating-span">5つのうち <strong>{ratingSummary.avgStar}</strong>つ</span>
    <div className="review-summary mb-6">
      <div className="review-graph text-2xl font-bold">
        <ul className="star-bar-chart">

          {ratingSummary?.distribution.map(rating => (
            <li 
              key={rating.star}
              className="star-bar-row cursor-pointer"
              onClick={() => filterByStar(rating.star)}
            >
                <span className="span1">★{ rating.star }</span>
                <div className="star-bar">
                  <div className="star-bar-filled" style={{ width: rating.percent }}></div>
                </div>
                <span className="span2">{ rating.percent }</span>
            </li>
          ))}

        </ul>
      </div>
      <div className="text-gray-600 mt-2">
        <strong>{ratingSummary.count}</strong> 件のレビュー
        <button
          type="button" 
          onClick={() => filterByStar(false)}
          className="ml-5 text-blue-700 underline cursor-pointer"
        >
          [ 全てみる ]
        </button>
      </div>
      <button 
        type="button"
        className="text-sm text-white bg-mist-700 py-2 px-5 mt-3 rounded-2xl cursor-pointer"
        onClick={handleOpen}
      >
        この商品のレビューを投稿する
      </button>
    </div>
    </>
  )
}

RatingMeter.propTypes = {
  ratingSummary: PropTypes.object.isRequired,
  filterByStar: PropTypes.func.isRequired,
  handleOpen: PropTypes.func.isRequired,
}