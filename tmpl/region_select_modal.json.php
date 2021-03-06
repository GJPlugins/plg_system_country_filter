<?php
	/**
	 * @package     ${NAMESPACE}
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	use CountryFilter\Helpers\CitiesDirectory;
	use Joomla\CMS\Factory;
	


	$arrCityTop_Def = [
		'Москва',
		'Санкт-Петербург',
		'Новосибирск',
		'Екатеринбург',
		'Нижний Новгород',
		'Казань',
	];
	$separator = ', ';
	$api_key = $this->params->get( 'google_map_api_key', false );
	$doc = Factory::getDocument();
	/**
	 * Случайный город
	 */
	$RansomCity = CitiesDirectory::getRansomCity();

//	$this->CityData = CitiesDirectory::getLocationByCityName($arrCityTop_Def);




	$cities_title = ( isset( $this->CityData['cities'] ) ? $this->CityData['cities'] : null );
	$regions_title = ( isset( $this->CityData['regions'] ) ? $this->CityData['regions'] : null );
	$country_title = ( isset( $this->CityData['country'] ) ? $this->CityData['country'] : null );
	
	if( $cities_title == $regions_title )
		$regions_title = null; #END IF
	
    $inpValue = !empty( $cities_title ) ? $cities_title : null;
	$inpValue .= !empty( $regions_title ) ? $separator . $regions_title : null;
	$inpValue .= !empty( $country_title ) ? $separator . $country_title : null;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	


?>

<link id="region_select_modal-css"
      href="<?= \Joomla\CMS\Uri\Uri::root() ?>/plugins/system/country_filter/asset/css/region_select_modal.json.css"
      rel="stylesheet" type="text/css"/>
<!--<script src="https://maps.googleapis.com/maps/api/js?key=--><? //= $api_key ?><!--&libraries=places&callback=country_filter_initMap" />-->
<script src="<?= \Joomla\CMS\Uri\Uri::root() ?>/plugins/system/country_filter/asset/js/country_filter.region_select.modal.js"></script>


<svg style="display: none;">
    <defs id="symbols">
        <symbol viewBox="0 0 24 24" id="icon-delivery-self">
            <path d="M24,11h-1l0.9-0.3L21.7,4H16h-2H0v14h2.2c0.4,1.2,1.5,2,2.8,2s2.4-0.8,2.8-2H14h2h0.2c0.4,1.2,1.5,2,2.8,2  s2.4-0.8,2.8-2H24V11z M5,18c-0.6,0-1-0.4-1-1s0.4-1,1-1s1,0.4,1,1S5.6,18,5,18z M14,16H7.8c-0.4-1.2-1.5-2-2.8-2s-2.4,0.8-2.8,2H2  V6h12V16z M16,6h4.3l1.3,4H16V6z M19,18c-0.6,0-1-0.4-1-1s0.4-1,1-1s1,0.4,1,1S19.6,18,19,18z M22,16h-0.2c-0.4-1.2-1.5-2-2.8-2  s-2.4,0.8-2.8,2H16v-4h6V16z"></path>
        </symbol>
    </defs>
</svg>

<div _ngcontent-c41="" class="modal__holder modal__holder_show_animation modal__holder_size_medium">
    <div _ngcontent-c41="" class="modal__header">
        <h3 _ngcontent-c41="" class="modal__heading">Выберите свой город</h3>
        <button _ngcontent-c41="" class="modal__close" type="button" aria-label="Закрыть модальное окно">
            <svg _ngcontent-c41="" height="16" pointer-events="none" width="16">
                <use _ngcontent-c41="" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-close-modal"></use>
            </svg>
        </button>
    </div>
    <div _ngcontent-c41="" class="modal__content">
        <div _ngcontent-c41=""></div>
        <common-city _nghost-c39="">
            <p _ngcontent-c39="" class="header-location__intro">
                <svg _ngcontent-c39="" height="24" width="24">
                    <use _ngcontent-c39="" xlink:href="#icon-delivery-self"
                         xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
				<?= $this->params->get( 'intro_txt', JText::_( 'Доставляем заказы по всей России!' ) ) ?>
            </p>

            <ul _ngcontent-c39="" class="header-location__popular">
				<?php
					# Если TOP подсказки включены
					if( $this->params->get( 'on_top_city', true ) )
					{
						$arrCityTop = $this->params->get( 'top_city', false , 'ARRAY' );


						

						foreach ($arrCityTop as $item)
                        {
                            ?>
                            <li _ngcontent-c39="" class="header-location__popular-item">
                                <a _ngcontent-c39="" data-city_alias="<?= $item->citiesAlias ?>"
                                   class="header-location__popular-link">
			                        <?= $item->cities ?>
                                </a>
                            </li>
	                        <?php
                        }
					}#END IF
     
     
				?>
            </ul>

            <form _ngcontent-c39="" onsubmit="return false" class="header-location__search ng-untouched ng-pristine ng-valid"
                  novalidate="">

                <label _ngcontent-c39="" class="header-location__search-label" for="cityinput">
					<?= $this->params->get( 'before_input_text', JText::_( 'Введите населенный пункт России' ) ) ?>
                </label>

                <auto-complete _ngcontent-c39="" class="header-location__search-input" id="cityinput" _nghost-c40="">
                    <input _ngcontent-c40=""
                           id="pac-input"
                           autocomplete="off"
                           class="header-location__autocomplete-input ng-untouched ng-pristine ng-valid"
                           name="search"
                           type="text"
                           value="<?= $inpValue ?>"
                           placeholder="Выберите свой город">
                    <ul _ngcontent-c40="" class="header-location__autocomplete-list dialog"><!----><!----><!---->
                        <!----></ul><!---->
                </auto-complete><!---->

                <p _ngcontent-c39="" class="header-location__search-example"> Например,
                    <a _ngcontent-c39="" class="link-dotted"  data-city_alias="<?= $RansomCity[0]['citiesAlias'] ?>">
                        <?= $RansomCity[0]['cities'] ?>
                    </a>
                </p>
                <input name="city_id" type="hidden" value="" >
            </form>

            <fieldset _ngcontent-c77="">
                <div _ngcontent-c77="" class="header-location__footer">
                    <a _ngcontent-c77="" apprzroute="" class="btn-Apply button button_size_medium button_color_gray"
                       href="<?= \Joomla\CMS\Uri\Uri::root(true) ?>">
                        Перейти на главную страницу </a>
                    <button _ngcontent-c77="" class="btn-Apply button button_size_medium button_color_green"> Применить</button>
                </div>
            </fieldset>


            <p _ngcontent-c39="" class="header-location__caption">Выбор города поможет предоставить актуальную
                информацию о наличии товара, его цены и способов доставки в вашем городе! Это поможет сохранить больше
                свободного времени для вас!</p></common-city>
    </div>
</div>
