/**
 *
 * @constructor
 */
var RegionSelect = function ( InitNew ) {
    var $ = jQuery ;
    var self = this ;
    this.__v = '0.0.3';
    this.__group = 'system' ;
    this.__plugin = 'country_filter' ;



    this.AjaxDefaultData = {
        group : this.__group,
        plugin : this.__plugin ,
        option : 'com_ajax' ,
        format : 'json' ,
        task : null ,
    }

    this.selector = '.js-rz-city a' ;
    this.Init = function () {

        var $a = $(this.selector) ;
        $a.on('click' , self.loadModalRegionSelect );
        this.getCityClient() ;

        // self.addEventClck();
    };
    /**
     * Установить город в модуле
     * @param d obj
     * {
     *     cities_title:
     *     regions_title :
     *     country_title :
     *
     * }
     */
    this.changeCityHead = function ( d )
    {
        var titleText ;
        var $headerCities = $(self.selector);

        $headerCities.text(d.cities_title)

        titleText = d.cities_title ;
        if ( d.regions_title && d.regions_title !== d.cities_title  ){
            titleText += ', ' + d.regions_title
        }
        titleText += ', ' +d.country_title;
        $headerCities.attr('title' , titleText  )
    };
    this.getCityClient = function ()
    {
        var data = self.AjaxDefaultData;
        data.task = 'Ajax_getCityClient' ;


        wgnz11.getModul("Ajax").then(function (Ajax) {
            Ajax.ReturnRespond = true ;
            Ajax.send(data).then(function (r) {
                var $headerCities = $(self.selector);
                var d = r.data[0] ;
                var titleText ;
                console.log($headerCities)

                if ( d.cities_title ){
                    $headerCities.text(d.cities_title)
                    titleText = d.cities_title ;

                    if ( d.regions_title && d.regions_title !== d.cities_title  ){
                        titleText += ', ' + d.regions_title
                    }
                    titleText += ', ' +d.country_title;
                    $headerCities.attr('title' , titleText  )
                }

                console.log( r.data[0].cities_title )

            });
        })
        // this.tippyInt();
    }


    this.tippyInt = function ()
    {
        wgnz11.__loadModul.Tippy().then(function(a){
            console.log( typeof tippy )
            // alert( self.selector )

            setTimeout( function () {
                if ( typeof tippy === 'function' ) {
                    console.log( typeof tippy )
                    tippy( self.selector , {
                        content: 'Tooltip',
                    });
                }
            } , 2000 )

        })
    };

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
                        $('.pac-container.pac-logo').remove() ;

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
    this.AutocompleteLaoded = false ;
    /**
     * Подгрузить Googleapis Maps libraries=places
     */
    this.loadGoogleMap = function () {
        var gApi = Joomla.getOptions('gApi');

        if ( self.AutocompleteLaoded   ) {
            country_filter_initMap();
            return ;
        }

        wgnz11.load.js('https://maps.googleapis.com/maps/api/js?key='+gApi.api_key+'&libraries=places&callback=country_filter_initMap')
            .then(
                function (a) {
                    self.AutocompleteLaoded = true ;
                    console.log('Asset load - ', a)
                },
                function (err) {
                    console.log(err)
                }
            );
    };
    if ( InitNew ){
        this.Init()
    }

};

/**
 * Колбек после загрузки Google Map
 */
function country_filter_initMap() {

    var self = this ;
    var RS = new RegionSelect();




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
                var Cookie = res.data[0].Cookie
                console.log( 'Cookie ' , Cookie )
                wgnz11.getModul('Storage_class').then(function () {
                    var d = res.data[0]
                    var headData = {
                        cities_title : d.city.title ,
                        regions_title : d.region.title ,
                        country_title :d.country.title ,
                    }
                    Storage_class.set( 'country_filter' , res.data[0] );
                    console.log(d)

                    /**
                     *     cities_title:
                     *     regions_title :
                     *     country_title :
                     */
                    RS.changeCityHead(headData)


                })



                console.log(res.data[0]) ;
            },function (err) {console.log(err)});
        });

    }





    // Avoid paying for data that you don't need by restricting the set of
    // place fields that are returned to just the address components.
    /*autocomplete.setFields(['address_component']);

    */



    console.log( autocomplete )
}

new RegionSelect( true  );




