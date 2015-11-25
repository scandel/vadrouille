/**
 * Add JS to form components :
 * - init datepickers
 * - change minutes to "0" in timepickers, when hour selected
 * - autocomplete cities
 */

// Client side cache
var cache = {};

/**
 * Set minutes to 0 in time pickers, when an hour is selected and minutes are not.
 */
function timePickerInit(timePicker) {
    var input_name, input_hour, input_minute ;
    input_name = timePicker.attr('id') ;
    input_hour = input_name + '_hour';
    input_minute = input_name + '_minute';
    if ( $('#'+input_minute).length ) {
        $('#'+input_hour).on('change',function() {
            if ( $('#'+input_minute).val() == "" ) {
                $('#'+input_minute).val("0");
            }
        });
    }
}

$(document).ready(function() {

    /**
     * Datepickers
     * Bootstrap Datepicker
     */
    $('.datepicker').each(function(){
       var cBtn = ($(this).hasClass('datepicker-clear'));
       $(this).datepicker({
           format: "dd/mm/yyyy",
           language: "fr",
           forceParse: false,
           autoclose: true,
           orientation: "top",
           todayHighlight: false,
           startDate: '0',
           clearBtn: cBtn,
       });
    });


    $('.timepicker').each(function(){
        timePickerInit($(this));
    });

    /**
     * City autocompleters
     * JQuery UI Autocomplete
     *
     * todo : 1) Check ; 2) Expose routes with FOSJS
     *
     */

    $('.city-autocomplete').each(function() {
         var input_name, input_id ;
         input_name = $(this).attr('id') ;
         input_id = input_name.replace('name', 'id');
         $(this).autocomplete({
             source: function( request, response ) {
                 var firstLetters = request.term;
                 if ( firstLetters in cache ) {
                     response( cache[ firstLetters ] );
                     return;
                 }

                 $.getJSON( '/app_dev.php/ville/completer/' + firstLetters, null, function( data, status, xhr ) {
                     cache[ firstLetters ] = data;
                     response( data );
                 });
             },
             minLength: 1,

             select: function( event, ui ) {
                event.preventDefault();
                $(this).val( ui.item.name );
                 // Set value for city id
                $("#"+input_id).val(ui.item.id);
              },

             change: function( event, ui ) {
                 //console.log(ui);
                 // If value isn't selected from autocomplete, erase city id value
                 if (ui.item) {
                     $("#" + input_id).val(ui.item.id);
                 }
                 else {
                     $("#" + input_id).val('');
                 }
             },

        }).autocomplete( "instance" )._renderItem = function (ul, item) {
             // Style menu items with flags
            return $("<li>")
                .append('<a><img src="/bundles/app/img/flags/' + item.country+ '.png"> ' + item.name + ' (' + item.postcode + ')</a>')
                .appendTo(ul.addClass('autocomplete-city-row'));
        };
    });
});