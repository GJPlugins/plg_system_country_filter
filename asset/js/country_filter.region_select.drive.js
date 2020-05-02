/**
 *
 * @constructor
 */
var RegionSelect = function () {
    var $ = jQuery ;
    var self = this ;
    this.__v = '0.0.3';
    this.__group = 'system' ;
    this.__plugin = 'country_filter' ;



    this.Init = function () {
        var $a = $('.js-rz-city a') ;
        $a.on('click' , self.loadModalRegionSelect );
        // self.addEventClck();
    };














    
    




    




    
    
    


    this.AjaxDefaultData = {
        group : 'system',
        plugin : 'country_filter' ,
        option : 'com_ajax' ,
        format : 'json' ,
        task : null ,
    }
    /**
     * Обработка события клик "Выбрать город"
     * slector :  .js-rz-city a
     */
    this.loadModalRegionSelect = function () {
        var data = self.AjaxDefaultData;
        data.task = 'getModuleAjax' ;
        data.moduleName = 'region_select_modal' ;

        wgnz11.getModul("Ajax").then(function (Ajax) {
            Ajax.ReturnRespond = true ;
            Ajax.send(data).then(function (r) {
                Joomla.loadOptions({'gApi' : r.data[0].module.api })
                new BuildModal(r)
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
                        // Подгрузить Googleapis Maps libraries=places
                        self.loadGoogleMap();
                    },
                    afterClose  : function () {
                        /**
                         * Удалить CSS модального окна после закрытия
                         * Если оно было загружено не с Html модального окна
                         */
                        // var linkNode = document.getElementById('region_select_modal-css')[1] ;
                        // linkNode.parentNode.removeChild(linkNode);
                    },
                });
            });
        }
    };
    /**
     * Подгрузить Googleapis Maps libraries=places
     */
    this.loadGoogleMap = function () {
        var gApi = Joomla.getOptions('gApi');
        wgnz11.load.js('https://maps.googleapis.com/maps/api/js?key='+gApi.api_key+'&libraries=places&callback=country_filter_initMap')
            .then(
                function (a) {
                    console.log('Asset load - ', a)
                },
                function (err) {
                    console.log(err)
                }
            );
    };

    this.Init()
};

/**
 * Колбек после загрузки Google Map
 */
function country_filter_initMap() {
    var self = this ;
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
        setCityPrefix (PlaceResult)  ;
        console.log(PlaceResult);
        console.log(PlaceResult.name);  //название места
        console.log(PlaceResult.id);  //уникальный идентификатор места
    });

    /**
     * Уствновить префик города в Cookie
     * @param PlaceResult
     */
    function setCityPrefix (PlaceResult){

        wgnz11.getModul("Ajax").then(function (Ajax) {
            var data = {

                option : 'com_ajax' ,
                view : null ,
                group : 'system',
                plugin : 'country_filter' ,
                task : 'Ajax_setCityPrefix' ,
                service : 'GoogleMap' ,
                data : PlaceResult.address_components ,
                city : PlaceResult.name ,
                place_id : PlaceResult.id ,
                formatted_address : PlaceResult.formatted_address ,
                adr_address : PlaceResult.adr_address ,

            };

            // Вернуть весь тезультат
            Ajax.ReturnRespond = true ;
            Ajax.send(data , 'ns_'+'country_filter'+'-'+'setCityPrefix' , {method : 'POST'}).then(function (res) {
                console.log(res) ;
            },function (err) {console.log(err)});
        });

    }





    // Avoid paying for data that you don't need by restricting the set of
    // place fields that are returned to just the address components.
    /*autocomplete.setFields(['address_component']);

    */



    console.log( autocomplete )
}

new RegionSelect();


