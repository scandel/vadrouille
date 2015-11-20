/**
 * Add JS to form components :
 * - init datepickers
 * - autocomplete cities
 */

// Client side cache
var cache = {};

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
                // console.log(cache);
                //console.log( ui.item ?
                //"For: " + input_name + ", Selected: " + ui.item.name + " aka " + ui.item.id :
                //"Nothing selected, input was " + this.value );
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