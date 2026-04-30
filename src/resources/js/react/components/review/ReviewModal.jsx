import PropTypes from "prop-types";
import FieldError from "../errors/FieldError";
import StarRatingHover from "../ui/StarRatingHover";

export default function ReviewModal({form, setForm, editingReview, handleSubmit, errors}) {

  const handleChange = (name, value) => {
    setForm((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  return (

    <form onSubmit={(e) => handleSubmit(e)}>
      <ul className="space-y-5">
        <li className="space-y-2">
          <div className="flex items-center">
            <span className="text-sm font-semibold text-gray-700 mr-5">
              評価<small className="font-normal text-red-500 ml-2.5">※必須</small>
            </span>
            <FieldError errors={errors} field={'star'} className={"text-xs text-red-600"}/>
          </div>
          <StarRatingHover form={form} handleChange={handleChange}/>
        </li>
        <li className="space-y-2">
          <div className="flex items-center">
            <span className="text-sm font-semibold text-gray-700 mr-5">タイトル</span>
            <FieldError errors={errors} field={'title'} className={"text-xs text-red-600"}/>
          </div>
          <input
            name="title"
            value={form.title}
            onChange={(e) => handleChange(e.target.name, e.target.value)}
            placeholder="タイトル"
            className="w-full ml-3 py-2 px-3 border border-gray-200 rounded-md tracking-wide"
          />
        </li>
        <li className="space-y-2">
          <div className="flex items-center">
            <span className="text-sm font-semibold text-gray-700 mr-5">
              コメント<small className="font-normal text-red-500 ml-2.5">※必須</small>
            </span>
            <FieldError errors={errors} field={'review'} className={"text-xs text-red-600"}/>
          </div>
          <textarea
            name="review"
            value={form.review}
            onChange={(e) => handleChange(e.target.name, e.target.value)}
            placeholder="コメント"
            className="w-full ml-3 py-2 px-3 border border-gray-200 h-32 rounded-md tracking-wide"
          />
        </li>
        <li>
        <button
          type="submit"
          className={`w-full ml-3 py-2 px-3 font-bold text-white ${editingReview ? 'bg-sky-800' : 'bg-gray-700'} rounded-md cursor-pointer`}
        >
          {editingReview ? '変更内容を登録する' : 'レビューを投稿する'}
        </button>
        </li>
      </ul>
    </form>
  )
}

ReviewModal.propTypes = {
  form: PropTypes.object.isRequired,
  setForm: PropTypes.func.isRequired,
  editingReview: PropTypes.object,
  handleSubmit: PropTypes.func.isRequired,
  errors: PropTypes.object,
}