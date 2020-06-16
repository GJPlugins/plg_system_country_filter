/**
 *
 * @constructor
 */
window.RegionSelectModal = function ( InitNew ) {
    var $ = jQuery ;
    var self = this ;

    console.log( InitNew )

    this.Init = function () {
        // click - по подсказкам по городам
        $(this.selectos.cityTop).on('click' , this.onCityTopSelect );

        console.log( $( this.selectos.cityTop ) )

        $(self.selectos.inputCityAutocomplete).on('focus' , function (event)
        {
            event.preventDefault();
            console.log( 'focus' );
        }).on('blur' , function (event)
        {
            event.preventDefault();
            console.log( 'blur' );    
        });

        // Если есть в локальном  хранилище информация о городе
        // - вставить название города в поле Autocomplete
        this.getModul('Storage_class').then(function ()
        {
            if ( Storage_class.isset( self.StorageName ) ){
                dataStorage = Storage_class.get( self.StorageName );
                console.log( dataStorage )
                $(self.selectos.inputCityAutocomplete).val( dataStorage.cities )
            }
        });


    };

    /**
     * Обработчик событя Клик по подсказке выбора города
     * @param event
     */
    this.onCityTopSelect = function (event)
    {
        var Data = {},
            Location = {} ;

        event.preventDefault() ;

        Data.alias = $(this).data('city_alias')
        Data.city = $(this).text().trim();
        Data.window_location_href = window.location.href
        /**
         * найти город по названию
         */
        self.getLocationByCityName(Data).then(function ( Location )
        {
            var LData = Location.data ;
            // Установить название города в поле ввода
            $(self.selectos.inputCityAutocomplete).val( LData.cities );
            // Команда модулю изменить подпись названия города
            self.ModuleCity.ChangeCity( LData );

            // Сохранить данные о выбраном городе
            self.SaveCityData( LData );

            // Установить ссылку для редиректа
           $( self.selectos.modalBtnApply ).on('click.modal' , function (event)
            {
                event.preventDefault();
            }).attr('relaod' , LData.rLink ) ;
            $(self.selectos.modalLinkRoot).attr('href',LData.rLinkRoot)
        });

        // self.changeCityHead( city );
        // console.log( alias )


    }
    setTimeout(function () {
        if ( InitNew ){ self.Init() }
    },2000)


};
window.RegionSelectModal.prototype = new window.CountryFilterRegionSelect()
new RegionSelectModal( true  );




























