/*
 * JS for the "edit trip" page
 */

// Nb max d'étapes
var max_stops = 5

// Récupère le div qui contient la collection de tags
var collectionHolder = $('ul.stops');
// Indice pour numéroter les nouvelles étapes
var iter = collectionHolder.find('.deletable').length+2;

// ajoute un lien « add a tag »
var $addStopLink = $('<div class="col-sm-8 col-sm-push-4"><button id="add_stop" class="btn btn-default"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Ajouter une étape</button></div>');
var $newLinkLi = $('<li></li>').append($addStopLink);

// Carto
var map, markerLayer;
var polyline = null;
var markers = new Array();
var travel_time=0;
// var activeSteps = 0; // nb d'étapes actives (visibles - meme si vides)
var currentRoadbook = null;
var modify = 0;

france = new Object;
france.center = L.latLng(46.40, 2.60); // (3.082418, 45.777168);
france.zoom = 3;

// Array of stops positions
// 0 = departure, 1 = arrival, 2+ = intermediate stops
var pos= new Array();
for (var i=0; i < 200; i++){
    pos['app_trip_edit_stops_' + i] = new Object;
    pos['app_trip_edit_stops_' + i].set = false;
}

//Extend the Default marker class
var RedIcon = L.Icon.Default.extend({
    options: {
        iconUrl: '/bundles/app/img/icons/marker-icon_red.png'
    }
});

var GreenIcon = L.Icon.Default.extend({
    options: {
        iconUrl: '/bundles/app/img/icons/marker-icon_green.png'
    }
});


/*  ==== Adding New stops ======  */

function addStopForm(collectionHolder, $newLinkLi) {
    // Récupère l'élément ayant l'attribut data-prototype comme expliqué plus tôt
    var prototype = collectionHolder.attr('data-prototype');

    // Remplace '__name__' dans le HTML du prototype par un nombre basé sur
    // la longueur de la collection courante
    var newForm = prototype.replace(/__name__/g, iter++);

    // Affiche le formulaire dans la page dans un li, avant le lien "ajouter un tag"
    var $newFormLi = $('<li class="deletable"></li>').append(newForm);
    $newLinkLi.before($newFormLi);

    // ajoute un lien de suppression au nouveau formulaire
    addStopFormDeleteLink($newFormLi);

    // ajoute l'autocompletion dans le nouveau formulaire
    $newFormLi.find('.city-autocomplete').each(function() {
        var input_name, input_id ;
        input_name = $(this).attr('id') ;
        input_item = input_name.replace('_city_name', '');
        input_id = input_name.replace('_city_name', '_city_id');
        $(this).autocomplete({
            //source: AJAX_WRAP+'?name=city_complete',
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
                console.log("Selection ", input_item);
                console.log(ui.item);
                if (!$('#'+input_id).val() || $('#'+input_id).val() != ui.item.id) {
                    UpdateCity(input_item, ui.item);
                }
            },

            change: function( event, ui ) {
                console.log("Changement ", input_item);
                if ( $(this).val().trim() == '' ) {
                    $.each(['city_id', /*'city_details',*/ 'place', 'lat', 'lng'], function (index, what) {
                        $('#' + input_item + '_' + what).val('');
                    });
                }
            },

        }).autocomplete( "instance" )._renderItem = function (ul, item) {
            // Style menu items with flags
            return $("<li>")
                .append('<a><img src="/bundles/app/img/flags/' + item.country+ '.png"> ' + item.name + ' (' + item.postcode + ')</a>')
                .appendTo(ul.addClass('autocomplete-city-row'));
        };
    });
}

function addStopFormDeleteLink($stopFormLi) {
    var $removeFormA = $('<div class="row"><div class="col-sm-8 col-sm-push-4 clearfix"><button class="btn btn-link btn-sm"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Supprimer cette étape</button></div></div>');
    $stopFormLi.append($removeFormA);
    var item = $stopFormLi.children().first().attr('id');

    $removeFormA.on('click', function(e) {
        // empêche le lien de créer un « # » dans l'URL
        e.preventDefault();

        // supprime l'élément li pour le formulaire de tag
        $stopFormLi.remove();

        // supprime le marker de l'étape
        MapRemoveMarker(item);

        if (collectionHolder.find('.deletable').length < max_stops) {
            $('#add_stop').attr('disabled', false);
        }

    });
}

/*=======  Fonctions Mappy pour le départ et l'arrivée ================*/

/**
 * Initialisation carte
 *
 */
function MapInit() {

    // Création de la carte
    map = new L.Mappy.Map('map', {
        center: france.center,
        zoom: france.zoom,
        layersControl: false,
        logoControl: {
            dir: "/bundles/app/mappy/images/"
        }
    });

    // Instantiation du token pour utiliser le service de route
    L.Mappy.setToken("nbgQVKjuZyTf4Xp/oddeUjQZXtPzwBqZylL5hyS+pzuqM1Lge5kASz+cCeRN/+6FatA3ADO6/mWuxlNsPshDTQ==");

    /* Cadre Pour les marqueurs */
    markerLayer =  L.layerGroup().addTo(map);
}


/**
 * Met un marqueur sur le point de RV;
 * ne change pas l'affichage de la carte
 *
 * @param stop : 0..6
 */
function MapPutMarker(stop) {
    // console.log("Marker ",stop,pos[stop]);
    switch (stop) {
        case 'app_trip_edit_stops_0':
            var icon = new GreenIcon();
            var tooltip = 'Cliquez pour centrer la carte sur le point de départ.';
            break;
        case 'app_trip_edit_stops_1':
            var icon = new RedIcon();
            var tooltip = 'Cliquez pour centrer la carte sur le point d \'arrivée.';
            break;
        default:
            var icon = new L.Icon.Default();
            var tooltip = 'Cliquez pour centrer la carte sur l\'étape.';
            break;
    }

    if (pos[stop].set) {
        markerLayer.removeLayer(markers[stop]);
    }
    else pos[stop].set = true;

    markers[stop] = L.marker(pos[stop].center, {
        title: tooltip,
        icon: icon
    }).addTo(markerLayer);

    /*markers[stop].on('click', function () {
        MapCenter(stop);
    });*/

    // console.log(markers);
}

/**
 * Remove a marker
 * @param stop  : 'app_trip_edit_stops_x'
 */
function MapRemoveMarker(stop) {
    markerLayer.removeLayer(markers[stop]);
    markers[stop]=null;
    pos[stop].set = false;
}




/*========= recherche, vérification, update villes ============*/

/**
 * Quand une ville est choisie (autocompleter ou search)
 * Fait toutes les opérations nécessaires :
 *
 * @param item : ex : 'app_trip_edit_stops_0'
 * @param cityid
 * @param city_name
 * @param city_details (postcode)
 *
 */
function UpdateCity(item, uiItem) {

    // console.log("Update City");

    // Le nom
    $('#'+item+'_city_name').val(uiItem.name);

    // L'id
    $('#'+item+'_city_id').val(uiItem.id);

    // Coordonnées
    $('#'+item+'_lat').val(uiItem.lat);
    $('#'+item+'_lng').val(uiItem.lng);

    // Les détails (code postal, pays) pour la géoloc
    // $('#'+item+'_city_details').val(city_details);

    // Effacer le pt de RV (placeholder "N'importe où")
    $('#'+item+'_place').val('');

    // Placer le marqueur
    if ( !(isNaN(uiItem.lat)) && uiItem.lat!=0) {
        OnPlaceUpdate(item, -1, true);
    }

    // Change le nom dans les temps de parcours
    // UpdateStepNamesForTimes();
}


/**
 * Place un marqueur sur le lieu ;
 * si zoom >= 0, centre sur le lieu avec le niveau de zoom donné
 * si iti = true, calcule (éventuellement) l'itinéraire
 * (les deux options ne devraient pas être utilisées ensemble)
 * à executer lors de l'update d'un lieu
 *
 * @param item
 * @param zoom
 * @param iti
 */
function OnPlaceUpdate(item,zoom,iti) {

    var lat = parseFloat($('#'+item+'_lat').val());
    var lng = parseFloat($('#'+item+'_lng').val());

    // place un marker et calcule éventuellement l'itinéraire
    if ( !(isNaN(lat)) && !(isNaN(lng))) {

        // Est-ce que l'on change de position ?
        var change = true;
        if (pos[item].set) {
            var distance = pos[item].center.distanceTo(L.latLng(lat,lng));
            if (distance<10) // 10 m : on ne change pas
                change = false ;
        }
        pos[item].center = L.latLng(lat,lng);

        if (change) {
            MapPutMarker(item); // Met un marqueur sur le point de RV; ne change pas l'affichage de la carte
            // si les lieux de d?part et d'arriv?e sont connus tous deux, calcule et affiche l'itin?raire
            if ( iti && (pos['app_trip_edit_stops_0'].set==true) && (pos['app_trip_edit_stops_1'].set==true) ) {
                // Itinerary(); // Calcule le trajet, centre la carte sur le trajet, ?crit les infos trajet
            }
        }
        if (zoom >= 0) {
            map.setView([lat,lng],zoom);
        }
    }
}



jQuery(document).ready(function() {

    // ajoute un lien de suppression à tous les éléments li de
    // formulaires de tag existants
    collectionHolder.find('.deletable').each(function() {
        addStopFormDeleteLink($(this));
    });

    // ajoute l'ancre « ajouter un tag » et li à la balise ul
    collectionHolder.append($newLinkLi);

    $addStopLink.on('click', function(e) {
        // empêche le lien de créer un « # » dans l'URL
        e.preventDefault();

        // ajoute un nouveau formulaire tag (voir le prochain bloc de code)
        addStopForm(collectionHolder, $newLinkLi);

        // Si on est au max d'éléments, disable l'élément
        if (collectionHolder.find('.deletable').length >= max_stops) {
            $('#add_stop').attr('disabled', 'disabled');
        }
    });

    //---- Init Map ----
    MapInit();

    //---- Init markers : Si les villes sont déjà remplies  ---
    for (var i=0; i < 200; i++){
        var item = 'app_trip_edit_stops_' + i;
        var cityid = parseInt($('#'+item+'_city_id').val());
        if ( !(isNaN(cityid)) && cityid!=0) {
            OnPlaceUpdate(item,-1,true);
        }
    };

    //---- On "City" Change ----

    // Change select behavior
    $('.city-autocomplete').on("autocompleteselect", function (event, ui) {
        var input_item, input_id ;
        input_item = $(this).attr('id').replace('_city_name', '');
        input_id = $(this).attr('id').replace('_city_name', '_city_id');
        console.log("Selection ", input_item);
        console.log(ui.item);
        if (!$('#'+input_id).val() || $('#'+input_id).val() != ui.item.id) {
            UpdateCity(input_item, ui.item);
        }
    });

    // Change "change" behavior
    $('.city-autocomplete').on("autocompletechange", function () {
        var input_item ;
        input_item = $(this).attr('id').replace('_city_name', '');
        console.log("Changement ", input_item);
        if ( $(this).val().trim() == '' ){
            $.each(['city_id',/*'city_details',*/ 'place','lat','lng'], function(index, what) {
                $('#'+input_item+'_'+what).val('');
            });
        }
    });

});