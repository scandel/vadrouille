/**
 * Invert departure and arrival fields
 */

jQuery(document).ready(function() {

    $('#invert-button').click(function() {
        var depName = $('#app_trip_search_depCity_name').val();
        var depId = $('#app_trip_search_depCity_id').val();

        $('#app_trip_search_depCity_name').val($('#app_trip_search_arrCity_name').val());
        $('#app_trip_search_depCity_id').val($('#app_trip_search_arrCity_id').val());

        $('#app_trip_search_arrCity_name').val(depName);
        $('#app_trip_search_arrCity_id').val(depId);
    });

});