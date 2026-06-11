import PropTypes from "prop-types";
import RatingMeter from "../components/review/RatingMeter";
import ItemImage from "../components/review/ItemImage";
import { useState } from "react";
import Spinner from "../components/ui/Spinner";
import ReviewCard from "../components/review/ReviewCard";
import ReviewModal from "../components/review/ReviewModal";
import Modal from "../components/ui/Modal";
import { useReviewForm } from "../hooks/review/useReviewForm";
import { useReviews } from "../hooks/review/useReviews";
import { useReviewFilter } from "../hooks/review/useReviewFilter";
import toast from "react-hot-toast";
import { handleApiError } from "../../utils/apiErrorHandler";
import { deleteReview, toggleHelpful } from "../api/itemReview";
import { BASE_PATH } from "../../config";

export default function ReviewPage({itemId}) {

  const [filterStar, setFilterStar] = useState(null);
  const [visibleCount, setVisibleCount] = useState(2);
  const [openModal, setOpenModal] = useState(false);
  const [editingReview, setEditingReview] = useState(null);

  const handleOpen = () => {
    if (! user?.id) {
            const currentUrl = (window.location.pathname + window.location.search).replace(BASE_PATH, '') || '/';
      window.location.href = `${BASE_PATH}/login?redirect=${encodeURIComponent(currentUrl)}`;
      return;
    }
    setOpenModal(true);
  }

  const handleClose = () => {
    setOpenModal(false);
    setEditingReview(null);
    setErrors(null);
  }

  const {
    allReviews, 
    setAllReviews, 
    item, 
    ratingSummary, 
    user, 
    isInitialLoading
  } = useReviews({itemId});

  const {form, setForm, onSubmit, isEditMode, isOpen, errors, setErrors} = useReviewForm({
    itemId,
    editingReview,
    openModal,
    onSuccess: (newReview) => {
      if (isEditMode) {
        setAllReviews((prev) => [
          newReview,
          ...prev.filter((review) => review.id !== editingReview.id),
        ]);
      } else {
        setAllReviews((prev) => [newReview, ...prev]);
      }
      setFilterStar(null);
    },
    onClose: handleClose,
  });

  const { filteredReviews, visibleReviews } = useReviewFilter({
    allReviews,
    filterStar,
    visibleCount
  });

  const showMore = () => {
    setVisibleCount(prev => prev + 2);
  }

  const filterByStar = (star) => {
    setFilterStar(star || null);
    setVisibleCount(2);
  }

  const handleSubmit = async (e) => {
    e.preventDefault();
    await onSubmit();
  }

  const handleDelete = async (reviewId) => {
    
    if (! confirm('この投稿を削除しますが宜しいですか？')) return;
    
    try {
      const res = await deleteReview(`/api/reviews/${reviewId}`);

      setAllReviews((prev) => prev.filter((review) => review.id !== reviewId));
      toast.success(res.message);

    } catch (error) {
      handleApiError(error);

    } finally {
      setFilterStar(null);
      setVisibleCount(2);
    }
  }

  const handleHelpful = async (reviewId) => {

    try {
      const res = await toggleHelpful(`/api/reviews/${reviewId}/toggle`);

      const isHelpful = res.data.is_helpful;
      const addCount = isHelpful ? 1 : -1;

      setAllReviews((prev) =>
        prev.map((review) =>
          review.id === reviewId
          ? {...review,
              helpful_count: review.helpful_count + addCount,
              is_helpful: ! review.is_helpful,
            }
          : review
        )
      )

    } catch (error) {
      handleApiError(error);
    }
  }

  if (isInitialLoading) {
    return <Spinner overlay size="2x"/>
  }

  return (
    <>
      <div className="review-container">
        <div className="left-review">
          <RatingMeter ratingSummary={ratingSummary} filterByStar={filterByStar} handleOpen={handleOpen}/>
          <ItemImage item={item}/>
        </div>
        <div className="right-review">
          <h3>お客様からのレビュー
            {filterStar 
            ? (
                <span className="mx-5 text-base text-amber-500">{ `星${filterStar}つのレビューを表示中` }</span>
              )
            : (
                <span className="mx-5 text-base text-amber-500">全てのレビューを表示中</span>
              )
            }
          </h3>
          <ReviewCard 
            reviews={visibleReviews}
            user={user} 
            setEditingReview={setEditingReview}
            handleDelete={handleDelete}
            onHelpfulCount={handleHelpful}
          />

          {visibleCount < filteredReviews.length && (
            <button
             type="button"
             onClick={showMore}
             className="cursor-pointer font-bold text-gray-500 w-full mt-3 py-2 border border-gray-400 rounded"
             >
              もっと見る
            </button>
          )}

        </div>
      </div>
      {isOpen && (
        <Modal onClose={handleClose} >
          <ReviewModal
            form={form}
            setForm={setForm}
            editingReview={editingReview}
            handleSubmit={handleSubmit}
            errors={errors}
          />
        </Modal>
      )}
    </>
  )
}

ReviewPage.propTypes = {
  itemId: PropTypes.number.isRequired,
}