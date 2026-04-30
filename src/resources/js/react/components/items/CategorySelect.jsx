import PropTypes from "prop-types";

export default function CategorySelect({categories, filters, setFilters}) {

  const handleParentChange = (e) => {
    const value = e.target.value;

    setFilters(prev => ({
      ...prev,
      category: value,
      sub_category: '',
      item_category: '',
      page: 1
    }));
  };

  const handleSubChange = (e) => {
    const value = e.target.value;

    setFilters(prev => ({
      ...prev,
      sub_category: value,
      item_category: '',
      page: 1
    }));
  };

  const handleItemCateChange = (e) => {
    const value = e.target.value;

    setFilters(prev => ({
      ...prev,
      item_category: value,
      page: 1
    }));
  };

  const parentList = categories ?? [];
  const subList = parentList.find(parent => parent.slug === filters.category)?.children ?? [];
  const itemCateList = subList.find(sub => sub.slug === filters.sub_category)?.children ?? [];

  return (
    <div className="flex gap-2">
    <select
      name="category"
      value={filters.category}
      onChange={handleParentChange}
      className="min-w-40 text-xs! h-8! text-gray-500"
    >
      <option value="">カテゴリー全て</option>
      {parentList && parentList.map(parent => (
        <option key={parent.id} value={parent.slug}>{parent.name}</option>
      ))}
    </select>
    <select 
      name="sub_category"
      value={filters.sub_category}
      onChange={handleSubChange}
      disabled={! filters.category}
      className="min-w-40 text-xs! h-8! text-gray-500"
    >
      <option value="">サブカテゴリー 全て</option>
      {subList && subList.map(sub => (
        <option key={sub.id} value={sub.slug}>{sub.name}</option>
      ))}
    </select>
    <select
      name="item_category"
      value={filters.item_category}
      onChange={handleItemCateChange}
      disabled={! filters.sub_category}
      className="min-w-40 text-xs! h-8! text-gray-500"
    >
      <option value="">商品カテゴリー 全て</option>
      {itemCateList && itemCateList.map(itemCate => (
        <option key={itemCate.id} value={itemCate.slug}>{itemCate.name}</option>
      ))}
    </select>
    </div>
  )
}

CategorySelect.propTypes = {
  categories: PropTypes.object.isRequired,
  filters: PropTypes.object.isRequired,
  setFilters: PropTypes.func.isRequired
}