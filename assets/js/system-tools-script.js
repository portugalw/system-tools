

(function (window) {
'use strict';


  if (!window.ST) {
  window.ST = {};
  }


  window.ST.fetchJson = function (action, params = {}) {
    const query = new URLSearchParams({
    action,
    ...params,
    _wpnonce: ST_AJAX.nonce
    });


    return fetch(`${ST_AJAX.url}?${query}`, {
      credentials: 'same-origin'
    }).then(response => {
        if (!response.ok) {
        throw new Error('Erro HTTP ' + response.status);
      }
     return response.json();
    } );
    };


  })(window);


document.addEventListener('DOMContentLoaded', () => {

  

});
