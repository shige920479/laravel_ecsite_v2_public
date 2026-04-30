document.addEventListener('DOMContentLoaded', () => {
  const searchBtn = document.getElementById('searchAddress');

  if (searchBtn) {
    
    searchBtn.addEventListener('click', async () => {
      
      const postcode = document.getElementById('postcode').value.replace('-', '');
      if (!postcode) return alert('郵便番号を入力してください');
      
      try {
        const res = await fetch(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${postcode}`);
        const data = await res.json();

        if (data.results) {
          const result = data.results[0];
          const address = result.address1 + result.address2 + result.address3;
          document.getElementById('address').value = address;
        
        } else {
          alert('住所が見つかりません、郵便番号を再度ご確認ください');
          return;
        }

      } catch (error) {
        alert('通信エラー');
        console.error(error.message);
      }
    })
  }
})