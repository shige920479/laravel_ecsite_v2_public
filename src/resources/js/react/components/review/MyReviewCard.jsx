import PropTypes from "prop-types"
import StarRating from "../items/StarRating"

export default function MyReviewCard({allReviews, setEditingReview, onDelete}) {

  if (allReviews.length < 1) {
    return (
      <p>投稿がありません</p>
    )
  }

  return (
    <>
    {allReviews.map((review) => (
      <div key={review.id} className="flex items-center gap-5 border-b border-gray-200 pb-4">

        <div className="shrink-0 w-30">
          <img src={review.mainImage} className="w-30 h-20 object-cover rounded" />
          <p className="text-xs mt-2">{review.shop_name}</p>
          <p className="text-xs">{review.item_name}</p>
          {review.verified_purchase &&
            <span className="text-xs text-white bg-violet-400 px-1.5 py-0.5 rounded">購入済商品</span>
          }
        </div>

        <div className="flex-1">
            <StarRating avgStar={review.star} />
            <div className="text-gray-700 text-base font-semibold">
             {review.title}
            </div>
            <div className="text-gray-500 text-sm">最終更新日：{review.updated_at}</div>
            <p className="my-2">{review.review}</p>
            
            <div className="flex justify-between items-center">
              <div className="inline-block">
                <button 
                  type="button"
                  className="text-blue-700! mx-2! cursor-pointer"
                  onClick={() => setEditingReview(review)}
                 >
                  [ 編集 ]
                </button>
                <span>|</span>
                <button 
                  type="button" 
                  className="text-red-500! mx-2! cursor-pointer"
                  onClick={() => onDelete(review.id)}
                >
                  [ 削除 ]
                </button>
              </div>
              
              <div className="text-sm text-gray-600">
                <strong className="text-blue-600">{review.helpful_count}</strong> 
                人の方が「役に立った」と評価しました
              </div>
            </div>
        </div>
      </div>
    ))}
    </>
  )
}

MyReviewCard.propTypes = {
  allReviews: PropTypes.array.isRequired,
  setEditingReview: PropTypes.func.isRequired,
  onDelete: PropTypes.func.isRequired,
}