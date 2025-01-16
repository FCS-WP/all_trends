// import { DisplayLabel } from './components/DisplayLabel';

let Main = {
  init: async function () {

    // initialize demo javascript component - async/await invokes some 
    //  level of babel transformation 
    const displayLabel = new DisplayLabel();
    await displayLabel.init();

  }
};


// Main.init();

let selector = "#currency_selector select",
    _this = $(selector),
    currency = _this.val();


$.ajax({
  url: "/wp-admin/admin-ajax.php",
  method: 'POST',
  data: {
    action: 'get_current_currency',
  },
  success: function (response) {
    if (response.success) {
      let active_currency = response.data.currency;
      _this.find("option").removeAttr('selected');
      let options = _this.find("option");
      $(options).each(function(i, option){
        if($(option).attr('value') == active_currency){
          $(option).attr('selected', 'selected');
        }
      });
    } else {
      alert(response.data.message);
    }
  },
  error: function () {
    alert('Error getting currency. Please try again.');
  },
});



$('body').on('change', selector, function () {
  let _this = $(this);
  const currency = _this.val();

  $.ajax({
    url: "/wp-admin/admin-ajax.php",
    method: 'POST',
    data: {
      action: 'change_currency',
      currency: currency,
    },
    success: function (response) {
      if (response.success) {
        location.reload();
      } else {
        alert(response.data.message);
      }
    },
    error: function () {
      alert('Error changing currency. Please try again.');
    },
  });
});