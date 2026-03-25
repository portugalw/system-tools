




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

const el = id => document.getElementById(id);

function formatDate(date) {
        return new Date(date).toLocaleString('pt-BR');
 }


 const I18n = (function () {
  let dict = Object.create(null);
  let loaded = false;

  async function load(data) {
    
    //console.log(data);
    if (loaded) return; // 🔥 evita múltiplos fetch

    //const res = await fetch(url);
   // const data = await res.json();

    dict = Object.assign(Object.create(null), data);
    //console.log(dict);
    loaded = true;
  }

  function t(key, params) {
    let text = dict[key];
    if (text === undefined) return key;

    if (!params) return text;

    for (const k in params) {
      text = text.split('{' + k + '}').join(params[k]);
    }

    return text;
  }

  return {
    load,
    t
  };
})();

 I18n.load(window.I18N_DATA);

