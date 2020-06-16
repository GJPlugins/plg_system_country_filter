window.CountryFilterCore = function (  ) {
    var $ = jQuery ;
    var self = this ;
    this.__group = 'system' ;
    this.__plugin = 'country_filter' ;
    this.__param = Joomla.getOptions( this.__plugin , {} );

    this.Init = function ()
    {
        this.subFormTopInit();
    }
    /**
     * Обработка событий sub Form Топ города
     */
    this.subFormTopInit = function ()
    {
        var $wrpSubForm = $('[name="jform[params][top_city]"]').parent();
        var $selectV ;

        console.log( $('select.citiesAlias') )

        $wrpSubForm.on('change.top_city', 'select.citiesAlias' , function (event)
        {
            event.preventDefault();
            $selectV = $(this).find('option:selected').text();
            $(this).closest('.subform-repeatable-group').find('input.cities').val($selectV)
            console.log( this )
        })
    }

    this.Init();
}
window.CountryFilterCore.prototype = new GNZ11() ;
new CountryFilterCore();
