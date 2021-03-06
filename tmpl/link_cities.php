<?php

	use Joomla\CMS\Factory;
	use Joomla\CMS\Uri\Uri;

	defined( '_JEXEC' ) or die;

    $doc = Factory::getDocument();
//	$doc->addStyleSheet( Uri::root( true ) . '/plugins/system/country_filter/asset/css/region_select_link_cities.css' );
    $doc->addStyleDeclaration('
            .region_select-link_cities ul {
            list-style: none;
        }
        .region_select-link_cities ul li {
            float: left;
            margin: 2px 5px;
            background: #FF5722;
        }
        .region_select-link_cities ul li a {
            color: #fff;
            margin: 5px;
        }
    ');
	$city_default = $this->params->get('default_city' , null ) ;





	?>

<div class="region_select-link_cities">
	<ul>
		<?php
			foreach( $this->LinkCitiesData as $linkCitiesDatum )
			{


			    $alias = ($city_default==$linkCitiesDatum['citiesAlias']?null:$linkCitiesDatum['citiesAlias']);
				?>
				<li class="<?= $linkCitiesDatum['citiesAlias'] ?>">
					<a class="link_cities-a" href="<?= Uri::root() . $alias  ?>" data-city_alias="<?= $linkCitiesDatum['citiesAlias'] ?>" >
                        <?= $linkCitiesDatum['cities'] ?>
                    </a>
				</li>
				<?php
			}#END FOREACH
		?>

	</ul>
</div>


	
