/**
 *
 * @param InitNew
 * @constructor
 */
window.CountryFilterCore_OLDcityData = {}
window.CountryFilterCore = function ( InitNew ) {
    var $ = jQuery ;
    var self = this ;
    this.__group = 'system' ;
    this.__plugin = 'country_filter' ;
    this.__params = Joomla.getOptions( this.__plugin , {} );




    /**
     * селекторы елементов плагина
     * @type {{inputCityAutocomplete: string, aCity: string, cityTop: string}}
     */
    this.selectos = {
        aCity : '.js-rz-city a' ,
        cityTop : 'a.header-location__popular-link , p.header-location__search-example a' ,
        inputCityAutocomplete : '#pac-input' ,
        modalBtnApply : 'button[_ngcontent-c77]' ,
        modalLinkRoot : 'a[_ngcontent-c77]' ,
        //
        link_cities : 'a.link_cities-a' ,

    };





    /**
     * Ключ данных для Local Storage
     * @type {string}
     */
    this.StorageName = 'CountryFilter' ;

    this.cityData = {} ;

    this.SaveCityData = function ( Location )
    {
        var citiesAliasOld = false ;
        this.cityData = Location ;

        if ( typeof window.CountryFilterCore_OLDcityData.id !== 'undefined' ){
            if ( this.cityData.id === window.CountryFilterCore_OLDcityData.id ) return ;
            citiesAliasOld = window.CountryFilterCore_OLDcityData.citiesAlias
        }

        window.CountryFilterCore_OLDcityData = Object.assign({}, this.cityData );
        var citiesAlias = this.cityData.citiesAlias ;
        console.log( citiesAlias );

        // Изменить все ссылки
        var juri = this.JoomlaStoragePlugin( 'siteUrl' ) ;
        /*$('a').each(function (i,a)
        {
            var href = $(a).attr('href');
            regV = /\#dbg|xdebug|^javascript|^\#|^http.+/gi

            if ( typeof href === 'undefined' || href.match(regV) ) return ;

            // Удаляем citiesAliasOld из ссылок
            if ( citiesAliasOld ){
                console.log(href)
                href =  href.replace( '/'+citiesAliasOld, '');

                console.log(href)
            }

            // Добавляем новый алиас
            $(a).removeAttr('href');
           $(a).attr('href' , '/' + citiesAlias + href ) ;

        })*/

        this.getModul('Storage_class').then(function () {
            Storage_class.set( self.StorageName , Location );
        })

    }



    /**
     * Параметры запроса для плагина
     * @type {{task: null, plugin: string, format: string, group: string, option: string}}
     */
    this.AjaxDefaultData = {
        group : this.__group,
        plugin : this.__plugin ,
        option : 'com_ajax' ,
        format : 'json' ,
        task : null ,
    }

    this.Init = function () {
        this.load.js( this.__params.siteUrl + 'plugins/system/country_filter/asset/js/CountryFilter.RegionSelect.js')
    };
    /**
     * найти город по названию
     * Data = {
     *     Data.alias   string
     *     Data.city    string
     * }
     */
    this.getLocationByCityName = function ( Data )
    {
        data = $.extend( true , this.AjaxDefaultData , Data );
        data.task = 'getLocationByCityName';
        return new Promise( function ( resolve , reject )
        {
            self.getModul( "Ajax" ).then( function ( Ajax )
            {
                // Не обрабатывать сообщения
                Ajax.ReturnRespond = true;
                // Отправить запрос
                Ajax.send( data ).then( function ( r )
                {
                    resolve( r )
                } , function ( err )
                {
                    console.error( err )
                })
            });
        });
    };


    if ( InitNew ){ this.Init() }
};
window.CountryFilterCore.prototype = new GNZ11() ;
new CountryFilterCore( true  );




