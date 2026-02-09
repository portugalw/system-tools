

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
          console.log(response.body);
        throw new Error('Erro HTTP ' + response.status);
      }
     return response.json();
    } );
    };


  })(window);

 


document.addEventListener('DOMContentLoaded', () => {

  
});

function mascaraInteiros(event) {
  
    // Remove tudo que não for número
    let v = event.target.value.replace(/\D/g, '');

    // Impede começar com zero
    if (v.startsWith('0')) {
        v = v.replace(/^0+/, ''); 
    }

    event.target.value = v;
}
