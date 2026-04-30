import PropTypes from "prop-types"

export default function SortSelect({ value, onChange }) {
  return (
        <div className="item-select-box">
          <label htmlFor="item-select" className="mr-1.5">表示順</label>
          <select name="item_sort" id="item-select" value={value} onChange={onChange}>
            <option value="">並べ替えなし</option>
            <option value="price_asc" >価格の安い順</option>
            <option value="price_desc" >価格の高い順</option>
            <option value="date_desc" >新着順</option>
            <option value="shop_asc" >ショップ順</option>
          </select>
        </div>
  )
}

SortSelect.propTypes = {
  value: PropTypes.string.isRequired,
  onChange: PropTypes.func.isRequired
}