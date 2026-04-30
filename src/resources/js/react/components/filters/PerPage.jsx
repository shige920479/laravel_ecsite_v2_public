import PropTypes from "prop-types";

export default function PerPage({value, onChange}) {

  return (
    <div className="item-select-box">
      <label htmlFor="per-page" className="mr-1.5">表示数</label>
      <select name="per_page" id="per-page" value={value} onChange={onChange} className="min-w-15!">
        <option value="8">8件</option>
        <option value="12" >12件</option>
        <option value="16" >16件</option>
      </select>
    </div>
  );
}

PerPage.propTypes = {
  value: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
  onChange:PropTypes.func.isRequired,
}