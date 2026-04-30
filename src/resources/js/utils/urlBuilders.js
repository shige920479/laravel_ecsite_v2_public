// 商品一覧ページの検索・絞り込み用パス・クエリパラメーター生成
export const buildItemsUrl = (filters) => {

    const {item_search, item_sort, category, sub_category, item_category, per_page, page} = filters;

    let path = '';
    if (category) path += `/${category}`;
    if (sub_category) path += `/${sub_category}`;
    if (item_category) path += `/${item_category}`;
    if (! path) path = '/';

    const params = new URLSearchParams();
    if (item_search) params.append('item_search', item_search);
    if (item_sort) params.append('item_sort', item_sort);
    if (per_page) params.append('per_page', per_page);
    if (page) params.append('page', page);

    const queryString = params.toString();

    let url = path;
    if (queryString) {
      url = path + `?${queryString}`; 
    } 
    
    return url;
}

export const buildReviewUrl = (filters) => {

  const {review_sort ,per_page, page} = filters;
  let path = '/api/mypage/reviews';

  const params = new URLSearchParams();
  if (review_sort) params.append('review_sort', review_sort);
  if (per_page) params.append('per_page', per_page);
  if (page) params.append('page', page);

  const queryString = params.toString();

  let url = path;
  if (queryString) {
    url = path + `?${queryString}`;
  }

  return url;
}