/**
 * Управление выбором Города
 * @param InitNew
 * @constructor
 */
window.CountryFilterRegionSelect = function ( InitNew ) {
    var $ = jQuery ;
    var self = this ;

    this.Redirect_link ;
    this.Init = function () {
        var $a = $(this.selectos.aCity) ;
        $a.on('click' , self.loadModalRegionSelect );
        this.getCityClient();
        $(this.selectos.link_cities).on('click' , self.onLinkCities );
    };
    this.onLinkCities = function (event) {
        event.preventDefault() ;
        var Data = {},
            linkHref = $( this ).attr('href');
        Data.alias = $(this).data('city_alias')
        Data.city = $(this).text().trim();
        self.getLocationByCityName(Data).then(function ( Location )
        {
            var LData = Location.data ;
            // Команда модулю изменить подпись названия города
            self.ModuleCity.ChangeCity( LData );
            // Сохранить данные о выбраном городе
            self.SaveCityData( LData );
            window.location.href = linkHref ;
        });
        console.log( Data );
        console.log( this );

    }
    /**
     * Операции над модулем
     * @type {{ChangeCity: Window.CountryFilterRegionSelect.ModuleCity.ChangeCity}}
     */
    this.ModuleCity = {
        // Команда модулю изменить подпись названия города
        ChangeCity : function (Location)
        {
            $(self.selectos.aCity).text(Location.cities)
        }
    } ;





    this.getCityClient = function ()
    {

        var countryFilterKey = false ;
        var dataStorage = null ;

        this.getModul('Storage_class').then(function () {
            countryFilterKey = Storage_class.isset( self.StorageName );
            if ( !countryFilterKey ){
                get() ; return   ;
            }
            dataStorage = Storage_class.get( self.StorageName );
            self.ModuleCity.ChangeCity( dataStorage  )
        });
        if ( countryFilterKey ) return   ;
        function get()
        {
            var data = Object.assign({}, self.AjaxDefaultData )
            data.task = 'getCityData' ;
            wgnz11.getModul("Ajax").then(function (Ajax) {
                Ajax.ReturnRespond = true ;
                Ajax.send(data).then(function (r) {
                    // Если результат не чего не вернул
                    if (  !r.success || !r.data.length )  return ;

                    countryFilterKey = Storage_class.isset( self.StorageName )
                    if ( r.data[0].citiesAlias === self.__params.default_city && !countryFilterKey ){
                        return;
                    }

                    self.ModuleCity.ChangeCity( r.data[0] )

                });
            });
        }
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
        var data = Object.assign({}, self.AjaxDefaultData )

        data.task = 'getModuleAjax' ;
        data.moduleName = 'region_select_modal' ;

        wgnz11.getModul("Ajax").then(function (Ajax) {
            Ajax.ReturnRespond = true ;
            Ajax.send(data).then(function (r) {
                Joomla.loadOptions({'gApi' : r.data[0].module.api })
                new BuildModal(r)
            },function (error)
            {
                console.log(error);
            });
        })



        this.RegionSelectModalWindow = false ;

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
                        self.RegionSelectModalWindow = a ;

                        $( self.selectos.modalBtnApply ).on('click.RegionSelect', function ()
                        {
                            self.RegionSelectModalWindow.close();
                        })
                        // Подгрузить Googleapis Maps libraries=places
                        self.loadGoogleMap();
                    },
                    beforeClose: function(){
                        if (self.ReloadAfterRespond) return  false ;
                        var relaod =  $( self.selectos.modalBtnApply ).attr('relaod')
                        console.log(relaod)
                        if ( relaod ) window.location.href = relaod ;
                    },
                    afterClose  : function () {
                        $('body').trigger('onModalRegionSelectClose')




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

        if ( !gApi.api_key ) return;

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

window.CountryFilterRegionSelect.prototype = new window.CountryFilterCore() ;
new CountryFilterRegionSelect( true  );


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
        console.log('setCityPrefix')
        alert('setCityPrefix')
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

                    Storage_class.set( 'country_filter' , res.data[0] );
                    console.log(d)

                    // Установить Название города в модуле
                    RS.changeCityHead(d)


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






