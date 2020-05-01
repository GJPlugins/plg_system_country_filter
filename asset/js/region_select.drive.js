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
    var setting = Joomla.getOptions('gApi' , false);
    setting.types = ['(cities)'] ;
    var input = document.getElementById('pac-input');
    var autocomplete = new google.maps.places.Autocomplete(input , setting );

    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var place = autocomplete.getPlace(); //получаем место
        console.log(place);
        console.log(place.name);  //название места
        console.log(place.id);  //уникальный идентификатор места
    });



    console.log( autocomplete )
}

(function () {
    RS = new RegionSelect();
    RS.Init();
})()
