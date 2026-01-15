(function () {
  function init() {
    // Fill hidden desk-id field
    var deskId = window.SB_BOOKING ? SB_BOOKING.deskId : "";
    var deskInput = document.querySelector('input[name="desk-id"]');
    if (deskInput && (!deskInput.value || deskInput.value === "")) {
      deskInput.value = deskId;
    }

    // Init calendar on booking-date
    var dateInput = document.querySelector('input[name="booking-date"]');
    if (!dateInput || typeof flatpickr === "undefined") return;

    // Avoid browser native date picker UX
    // (Even if CF7 outputs type="text", it’s ok)
    dateInput.setAttribute("autocomplete", "off");

    var booked = (window.SB_BOOKING && SB_BOOKING.bookedDates) ? SB_BOOKING.bookedDates : [];
    var minDate = (window.SB_BOOKING && SB_BOOKING.minDate) ? SB_BOOKING.minDate : "today";

    flatpickr(dateInput, {
      dateFormat: "Y-m-d",
      minDate: minDate,
      disable: booked,
      inline: true,
      onDayCreate: function (dObj, dStr, fp, dayElem) {
        var iso = fp.formatDate(dayElem.dateObj, "Y-m-d");
        if (booked.indexOf(iso) !== -1) {
          dayElem.classList.add("sb-day-booked");
          dayElem.setAttribute("title", "Booked");
        }
      }
    });

    // After successful CF7 submit -> reload to refresh booked dates
    document.addEventListener('wpcf7mailsent', function () {
      window.location.reload();
    }, false);
  }

  document.addEventListener("DOMContentLoaded", init);
})();
