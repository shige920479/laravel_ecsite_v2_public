export const useReviewFilter = ({allReviews, filterStar, visibleCount}) => {

  const filteredReviews = filterStar
    ? allReviews.filter(review => review.star === filterStar)
    : allReviews;

  const visibleReviews = filteredReviews.slice(0, visibleCount);

  return {filteredReviews, visibleReviews};

}