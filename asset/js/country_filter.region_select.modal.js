/**
 *
 * @constructor
 */
window.RegionSelectModal = function ( InitNew ) {



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



    this.Init = function () {

        $(this.selectos.cityTop).on('click' , this.onCityTopSelect );

    };
    /**
     * Обработчик событя Клик по подсказке выбора города
     * @param event
     */
    this.onCityTopSelect = function (event)
    {
        event.preventDefault() ;
        var alias = $(this).data('city_alias')
        var city = $(this).text().trim();
        $(self.selectos.inputCityAutocomplete).val( city );

        console.log( alias )
        console.log( city )
        console.log( self )

    }

    // __proto__ = new  RegionSelect();
    if ( InitNew ){ this.Init() }

};
window.RegionSelectModal.prototype = new window.RegionSelect()
new RegionSelectModal( true  );




























