var RegionSelect = function () {
    var $ = jQuery ;
    var self = this ;
    this.Init = function () {
        var $a = $('.js-rz-city a') ;
        $a.on('click' , self.loadModalRegionSelect );
        // self.addEventClck();
    };
    /**
     *
     */
    /*this.addEventClck = function () {

    }*/
    this.loadModalRegionSelect = function () {
        var data = {
            group : 'system',
            plugin : 'country_filter' ,
            option : 'com_ajax' ,
            format : 'json' ,
            task : 'getModuleAjax' ,
            moduleName : 'region_select_modal' ,
        };
        wgnz11.getAjax().then(function (Ajax) {
            Ajax.ReturnRespond = true ;
            Ajax.send(data).then(function (r) {
                Joomla.loadOptions({'gApi' : r.data[0].module.api })

                BuildModal(r)
            });
        })

        /**
         * Создать модальное окно с выбором города
         * @param response
         * @constructor
         */
        function BuildModal(response){
            wgnz11.__loadModul.Fancybox().then(function (a) {
                a.open( response.data[0].module.content ,{
                    baseClass: "modalRegionSelect",
                    afterShow   : function(instance, current)   {

                    },
                    afterClose  : function () {},
                });
            });
        }

    }
};

function country_filter_initMap() {

    var options = Joomla.getOptions('gApi' , false);

    /**
     * Массив TYPES указывает явный тип или коллекцию типов,
     * Если ничего не указано, возвращаются все типы. Как правило, допускается только один тип.
     * Исключение является то , что вы можете смело смешивать geocodeи establishment тип, но обратите внимание ,
     * что это будет иметь тот же эффект, не указывая никаких типов.
     *
     * Поддерживаемые типы:
     *                      geocode поручает службе Places возвращать только результаты геокодирования,
     *                              а не бизнес-результаты.
     *                      address поручает службе Places возвращать только результаты геокодирования с точным адресом.
     *                      establishment поручает службе «Места» возвращать только бизнес-результаты.
     *                      (regions)коллекция типа дает указание службы Places ,
     *                      чтобы вернуть любой результат сопоставления следующих типов:
     *                      locality sublocality postal_code country administrative_area1 administrative_area2
     *
     *
     *
     * @type {string[]}
     */
    options.types = ['(cities)'] ;
    var input = document.getElementById('pac-input');
    var autocomplete = new google.maps.places.Autocomplete(  input , options  );
    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var PlaceResult = autocomplete.getPlace(); //Получить obj PlaceResult
        console.log(PlaceResult);
        console.log(PlaceResult.name);  //название места
        console.log(PlaceResult.id);  //уникальный идентификатор места
    });






    // Avoid paying for data that you don't need by restricting the set of
    // place fields that are returned to just the address components.
    /*autocomplete.setFields(['address_component']);

    */



    console.log( autocomplete )
}

(function () {
    RS = new RegionSelect();
    RS.Init();
})()
