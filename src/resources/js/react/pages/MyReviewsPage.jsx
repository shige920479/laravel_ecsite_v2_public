import { useState } from "react";
import MyReviewCard from "../components/review/MyReviewCard";
import Spinner from "../components/ui/Spinner";
import { useMyReviews } from "../hooks/review/useMyReviews";
import Pagination from "../components/filters/Pagination";
import SortReview from "../components/filters/SortReview";
import Modal from "../components/ui/Modal";
import ReviewModal from "../components/review/ReviewModal";
import { useReviewForm } from "../hooks/review/useReviewForm";
import { deleteReview } from "../api/itemReview";
import toast from "react-hot-toast";
import { handleApiError } from "../../utils/apiErrorHandler";

export default function MyReviewsPage() {

  const [filters, setFilters] = useState({
    review_sort: 'desc',
    per_page: 3, // per_pageは3で固定
    page: 1
  });
  const {isInitialLoading, allReviews, meta} = useMyReviews(filters);
  const [editingReview, setEditingReview] = useState(null);
  const [openModal, setOpenModal] = useState(false);  

  const handleOpen = () => {
    setOpenModal(true);
  }

  const handleClose = () => {
    setOpenModal(false);
    setEditingReview(null);
    setErrors(null);
  }

  const handleSubmit = async (e) => {
    e.preventDefault();
    await onSubmit();
  }

  const handleSuccess = (_updatedReview) => {
    setFilters({
      review_sort: 'desc',
      per_page: 3,
      page: 1
    })
  }

  const {form, setForm, onSubmit, isOpen, errors, setErrors} = useReviewForm({
    editingReview,
    openModal,
    onSuccess: handleSuccess,
    onClose: handleClose,
  })

  const handleDelete = async (reviewId) => {
    if (! confirm('この投稿を削除しますが宜しいですか？')) return;
    
    try {
      const res = await deleteReview(`/api/reviews/${reviewId}`);

      setFilters((prev) => ({...prev}));
      toast.success(res.message);

    } catch (error) {
      handleApiError(error);
    }
  }
  
  if (isInitialLoading) {
    return <Spinner overlay size="2x"/>
  }

  return (
    <>
      <div className="flex justify-between">
        <h2 className="text-xl font-bold text-gray-500 mb-3">投稿履歴一覧</h2>
        <div className="flex gap-8">
          <SortReview 
            value={filters.review_sort}
            onChange={
              (e) => setFilters((prev) => ({
                ...prev,
                page: 1,
                review_sort: e.target.value,
              }))
            }
          />
          <Pagination
            meta={meta} 
            onPageChange={
              (page) =>
                setFilters(prev => ({
                  ...prev,
                  page: page
                }))
            }
          />
        </div>
      </div>
      <div className="space-y-6">
        <MyReviewCard 
          allReviews={allReviews}
          handleOpen={handleOpen}
          setEditingReview={setEditingReview}
          onDelete={handleDelete}/>
      </div>
      
      {isOpen && 
        <Modal onClose={handleClose}>
          <ReviewModal
            form={form}
            setForm={setForm}
            editingReview={editingReview}
            handleSubmit={handleSubmit}
            errors={errors}
          />
        </Modal>
      }
    </>
  )
}