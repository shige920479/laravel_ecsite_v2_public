import PropTypes from "prop-types";
import StarRating from "../items/StarRating";

export default function ReviewCard({reviews, user, setEditingReview, handleDelete, onHelpfulCount}) {

  if (reviews.length < 1) {
    return (
      <p className="ml-5 mt-10">未だレビューがありません</p>
    )
  }

  return (
    <>
      {reviews.map(review => (
        <div key={review.id} className="review border-b py-4">
          <StarRating avgStar={review.star} />
          <div className="text-gray-700 text-base font-semibold">{review.title}</div>

          <div className="review-edit text-sm text-gray-600">
            投稿者: { review.nickname }
            {review.verified_purchase && (
              <span className="text-green-600 ml-2">✔ 購入者</span>
            )}
            {review.userId === user?.id && (
              <div className="inline-block">
                <button
                  type="button"
                  onClick={() => setEditingReview(review)}
                  className="text-blue-700! mx-2!"
                >
                  [ 編集 ]
                </button>
                <span>|</span>
                <button
                  type="button"
                  onClick={() => handleDelete(review.id)}
                >
                  [ 削除 ]
                </button>
              </div>
            )}

          </div>
          <div className="text-gray-500 text-sm">
            { review.updated_at }
          </div>
          <p className="mt-2">{ review.review }</p>
          <div className="text-sm text-gray-600 mt-2">
            <strong className={`text-lg ${review.is_helpful ? 'text-red-500' : 'text-blue-600'}`}>
              { review.helpful_count }
            </strong> 人の方が「役に立った」と評価しました
            {(user?.id && review.userId !== user?.id) &&(
              <button 
                type="button"
                className="ml-1 cursor-pointer text-lg"
                onClick={() => onHelpfulCount(review.id)}
              >
                👍
              </button>
            )}
          </div>
        </div>
      ))
    }
  </>

  )

}

ReviewCard.propTypes = {
  reviews: PropTypes.array.isRequired,
  user: PropTypes.object.isRequired,
  setEditingReview: PropTypes.func.isRequired,
  handleDelete: PropTypes.func.isRequired,
  onHelpfulCount: PropTypes.func.isRequired,
}