import PropTypes from "prop-types"

export default function SortReview({value, onChange}) {

  return (
        <div className="item-select-box mt-6">
          <label htmlFor="review_sort" className="mr-1.5">表示順</label>
          <select name="review_sort" id="item-select" value={value} onChange={onChange}>
            <option value="desc">新しい投稿順</option>
            <option value="asc" >古い投稿順</option>
          </select>
        </div>
  )
}

SortReview.propTypes = {
  value: PropTypes.string.isRequired,
  onChange: PropTypes.func.isRequired,
}