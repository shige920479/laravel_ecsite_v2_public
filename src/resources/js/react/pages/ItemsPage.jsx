import PropTypes from "prop-types";
import { useEffect, useState } from "react";
import { useItems } from "../hooks/useItems";
import ItemList from "../components/items/ItemList";
import SortSelect from "../components/filters/SortSelect";
import Pagination from "../components/filters/Pagination";
import PerPage from "../components/filters/PerPage";
import CategorySelect from "../components/items/CategorySelect";
import Spinner from "../components/ui/Spinner";

export default function ItemsPage({itemIndexConfig, categories}) {

  const initialConfig = itemIndexConfig ?? {};

  const [filters, setFilters] = useState({
    item_search: initialConfig.item_search ?? '',
    item_sort: initialConfig.item_sort ?? '',
    category: initialConfig.category ?? '',
    sub_category: initialConfig.sub_category ?? '',
    item_category: initialConfig.item_category ?? '',
    per_page: initialConfig.per_page ?? 8,
    page: initialConfig.page ?? 1,
  });

  const {items, meta, isInitialLoading, isFetching, currentUrl} = useItems(filters);

  useEffect(() => {
    window.history.replaceState({}, "", currentUrl);
  }, [currentUrl]);

  if (isInitialLoading) {
    return <Spinner overlay size="2x"/>
  }

  return (
    <>
      <div className="flex justify-between py-2">
        <CategorySelect categories={categories} filters={filters} setFilters={setFilters}/>
        <div className="flex gap-2 items-center">
          <SortSelect
            value={filters.item_sort}
            onChange={
              (e) =>
                setFilters(prev => ({
                  ...prev,
                  item_sort: e.target.value,
                  page: 1
                }))
            } />
          <PerPage
            value={filters.per_page}
            onChange={
              (e) => setFilters(prev => ({
                ...prev,
                per_page: e.target.value,
                page: 1
              }))
            }
          />
        </div>
      </div>
      <ItemList items={items} isFetching={isFetching} currentUrl={currentUrl}/>
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
    </>
  )

}
ItemsPage.propTypes = {
  itemIndexConfig: PropTypes.object.isRequired,
  categories: PropTypes.object.isRequired
};
