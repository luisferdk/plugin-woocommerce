(function ($) {
  $gender = document.querySelector('#gender').value;
  if ($gender) {
    fetch(`https://api.themoviedb.org/3/discover/movie?with_genres=${$gender}`, {
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI0YTBhOTNiZDMxMmQzYmQzMThjNTkwMGFlMWMwYjQxMSIsInN1YiI6IjYxMGEwM2RmZWU0M2U4MDA0NzliOTYzZSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.sdjFIh9rGCZIPibKXCrR4e7DHXzTKb2nl-8WSKEZbQM'
      }
    })
      .then(res => res.json())
      .then(data => {
        console.log(data)
        let html = '';
        data.results.map(item => {
          html += `
      <div class="product-movie">
        <h2>${item.title.length <= 53 ? item.title : item.title.substring(0, 50) + '...'}</h2>
        <img src="https://image.tmdb.org/t/p/w500/${item.poster_path}" />
        <button data-id="${item.id}" data-title="${item.title}" data-image="${`https://image.tmdb.org/t/p/w500/${item.poster_path}`}" class='add-movie'>Select Movie</button>
      </div>`
        })
        document.querySelector('.products-movies').innerHTML = html

        $('.add-movie').click(function () {
          if ($(this).hasClass('active')) {
            $('.add-movie').removeClass('active')
            $('#movie_id')[0].value = ''
            $('#movie_title')[0].value = ''
            $('#movie_image')[0].value = ''
            $('.woocommerce-error').show();
          }
          else {
            $('.add-movie').removeClass('active')
            $(this).addClass('active')
            $('.woocommerce-error').hide();
            $('#movie_id')[0].value = $(this).data('id')
            $('#movie_title')[0].value = $(this).data('title')
            $('#movie_image')[0].value = $(this).data('image')
          }

        })
      })
  }
})(jQuery);




(function ($) {

  $(document).on('click', '.single_add_to_cart_button', function (e) {
    e.preventDefault();

    var $thisbutton = $(this),
      $form = $thisbutton.closest('form.cart'),
      id = $thisbutton.val(),
      product_qty = $form.find('input[name=quantity]').val() || 1,
      product_id = $form.find('input[name=product_id]').val() || id,
      variation_id = $form.find('input[name=variation_id]').val() || 0,
      gender = $form.find('input[name=gender]').val() || '',
      movie = {
        movie_id: $form.find('input[name=movie_id]').val() || '',
        movie_title: $form.find('input[name=movie_title]').val() || '',
        movie_image: $form.find('input[name=movie_image]').val() || '',
      };

    var data = {
      action: 'woocommerce_ajax_add_to_cart',
      product_id: product_id,
      product_sku: '',
      quantity: product_qty,
      variation_id: variation_id,
      gender: gender,
      movie: movie
    };

    $(document.body).trigger('adding_to_cart', [$thisbutton, data]);

    $.ajax({
      type: 'post',
      url: wc_add_to_cart_params.ajax_url,
      data: data,
      beforeSend: function (response) {
        $thisbutton.removeClass('added').addClass('loading');
      },
      complete: function (response) {
        $thisbutton.addClass('added').removeClass('loading');
      },
      success: function (response) {

        if (response.error && response.product_url) {
          window.location = response.product_url;
          return;
        } else {
          $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
          $('.woocommerce-error').hide();
        }
      },
    });

    return false;
  });
})(jQuery);