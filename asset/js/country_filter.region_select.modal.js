/**
 *
 * @constructor
 */
window.RegionSelectModal = function ( InitNew ) {
    var $ = jQuery ;
    var self = this ;
    /**
     * После получения ответа - Переход на полученный город
     * @type {boolean}
     */
    this.ReloadAfterRespond = false ;
    console.log( InitNew )

    this.Init = function () {
        // click - по подсказкам по городам
        $(this.selectos.cityTop).on('click' , this.onCityTopSelect );

        var $inputCityAutocomplete = $(self.selectos.inputCityAutocomplete);
        var $form = $inputCityAutocomplete.closest('form')
        // Поле ввода города получает фокус
        $inputCityAutocomplete.on('focus' , function (event)
        {
            var $inputCityAutocomplete = $(self.selectos.inputCityAutocomplete);

            // Запрещаем отправку формы
            $form.on('submit' , function (event) {
                event.preventDefault();
                EVT_User()
                return false ;
            })
            // Обрабатываем нажатие ENTER
            $inputCityAutocomplete.on('keydown',function(e) {
                if (e.which === 13) {

                    EVT_User();
                    return false ;
                }
            });

            // Слушаем событие клик по кнопке приминить
            $( self.selectos.modalBtnApply ).off('click.RegionSelect')
                .on('click.region_select_modal', EVT_User ) ;

            $(self.selectos.modalLinkRoot).on('click.region_select_modal', EVT_User )
        }).on('blur' , function (event)
        {
            event.preventDefault();

            if ( $(event.relatedTarget).hasClass( 'btn-Apply' ) ){
                EVT_User(event);
            }


            console.log(event)

        });

        /**
         * Получить значение из инпута - передать для отрпавки запроса о городе
         * @constructor
         */
        var EVT_User = function  (event){
            event.preventDefault();
            var Data = {
                city : $inputCityAutocomplete.val().trim()
            } ;

            // Отправить данные о выбранном пользователем гооде
            self.sendLocationData( Data );
            self.ReloadAfterRespond = true ;
            return false ;
        }

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
        var Data = {}  ;

        event.preventDefault() ;

        Data.alias = $(this).data('city_alias')
        Data.city = $(this).text().trim();
        Data.window_location_href = window.location.href
        // Отправить данные о выбранном пользователем гооде
        self.sendLocationData( Data );

        // self.changeCityHead( city );
        // console.log( alias )


    }

    /**
     * Отправить данные о выбранном пользователем гооде
     * @param Data
     */
    this.sendLocationData = function(Data){
        var Location = {} ;
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
            $(self.selectos.modalLinkRoot).attr('href',LData.rLinkRoot);

            /**
             * Если переход на полученный город
             */
            if (self.ReloadAfterRespond){
                window.location.href = LData.rLink ;
                // закрыть модальное окно
                self.RegionSelectModalWindow.close();

            }

        });
    }
    setTimeout(function () {
        if ( InitNew ){ self.Init() }
    },2000)


};
window.RegionSelectModal.prototype = new window.CountryFilterRegionSelect()
new RegionSelectModal( true  );




























