import { useEffect, useState } from "react";
import PropTypes from "prop-types";
import { postReview, updateReview } from "../../api/itemReview";
import { handleApiError } from "../../../utils/apiErrorHandler";
import toast from "react-hot-toast";

export const useReviewForm = ({itemId = null, editingReview, openModal, onSuccess, onClose}) => {

  const [form, setForm] = useState({
    star: 0,
    title: "",
    review: "",
  });

  const [isSubmiting, setIsSubmiting] = useState(false);
  const [errors, setErrors] = useState({});

  const isEditMode = !! editingReview;
  const isOpen = openModal || isEditMode;

  useEffect(() => {
    if (editingReview) {
      setForm({
        star: editingReview.star ?? 0,
        title: editingReview.title ?? "",
        review: editingReview.review ?? "",
      });
    } else {
      setForm({
        star: 0,
        title: "",
        review: "",
      });
    }
  }, [editingReview, isOpen]);

  const onSubmit = async () => {
    if (isSubmiting) return;

    setIsSubmiting(true);

    const url = isEditMode
      ? `/api/reviews/${editingReview.id}`
      : `/api/item/${itemId}/review`;

    try {
      if (! form.star || ! form.review) throw new Error('評価かコメントが未入力になっています');
  
      const res = isEditMode
        ? await updateReview(url, form,)
        : await postReview(url, form,);
      
      if (! res.data) throw new Error('データの取得に失敗しました、再読み込みをお願いします');

      onSuccess(res.data);

      if (res.success) {
        onClose();
        toast.success(res.message);
      }

    } catch (error) {
      const validationErrors = handleApiError(error);
      if (validationErrors) setErrors(validationErrors);
    
    } finally {
      setIsSubmiting(false);
    }
  }

  return {form, setForm, onSubmit, isEditMode, isOpen, errors, setErrors};
}

useReviewForm.propTypes = {
  itemId: PropTypes.number,
  editingReview: PropTypes.object.isRequired,
  openModal: PropTypes.bool.isRequired,
  onSuccess: PropTypes.func.isRequired,
  onClose: PropTypes.func.isRequired,
}