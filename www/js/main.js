jQuery(document).ready(function()
{
		loadImage = '<img src="/img/load.gif" />';

		//редирект по мирам из выпадающего списка
		jQuery('.js-world-select').change(function()
		{
			var href = jQuery(this).val();
			if( typeof href !== 'undefined' )
				location = href;
		});


		//переход вверх страницы
		jQuery(".js-get-top").click(function()
		{
			window.scroll(0,0);
		});

		//тултипс с вопросительной инфой
		jQuery(".js-quest-tooltip").tooltip(
		{
			bodyHandler: function(){return jQuery(this).attr('tooltip');},
			delay: 0,
			track: true,
			extraClass: "quest-tooltip"
		});

	/**новости**************************************************************************/
		//открыть-закрыть новость
		jQuery(".js-news").click(function()
		{
			toggleNews(this, 'toggle');
		});

		//открываем блок при переходе к новости с другой страницы
		var idN  = parseInt( document.location.hash.substr(5), 10);
		var node = document.getElementsByName('item' + idN);
		if ( idN !== '' && node.length === 1)
		{
			toggleNews(node[0].parentNode, 'toggle');
		}


		//открыть все новости
		jQuery(".js-open-all-news").click(function()
		{
			jQuery(".js-news").each(function()
			{
				toggleNews(this, 'open');
			});
		});

		//закрыть все новости
		jQuery(".js-close-all-news").click(function()
		{
			jQuery(".js-news").each(function()
			{
				toggleNews(this, 'close');
			});
		});

	/**страница аддона*******************************************************************/
		//выбор ссылки по умолчанию
		if( jQuery(".js-addon-selector").length >0){
			var browser = DetectBrowserForAddon(window.navigator.userAgent);

			if (browser !== false){
				var currentOption = jQuery(".js-addon-selector option:contains('" + browser + "')");
				currentOption.attr("selected","selected");
				jQuery(".js-addon-link").attr("href",currentOption.val());
			}
		}

		//открытие селектора
		jQuery(".js-addon-select-opener").click(function(){
			jQuery(this).remove();
			jQuery(".js-addon-selector").removeClass("hide");
		});

		//изменение ссылки по селектору
		jQuery(".js-addon-selector").change(function(){
			jQuery(".js-addon-link").attr("href",jQuery(this).val());
			jQuery(".js-addon-link")[0].click();
		});

	/**табличка археологии***************************************************************/
		//подсветка строк/столбцов в табличке
		jQuery('.js-arch-rank-table td').hover(
		function()
		{
			jQuery(this).addClass('arch-table-active');
			jQuery(this).parents('tr:first').find('th').addClass('arch-table-active');

			var index = parseInt(jQuery(this).attr('cellIndex'));
			jQuery(this).parents('table:first').find('thead th:eq(' + index + ')').addClass('arch-table-active');
		},
		function()
		{
			jQuery(this).removeClass('arch-table-active');
			jQuery(this).parents('tr:first').find('th').removeClass('arch-table-active');

			var index = parseInt(jQuery(this).attr('cellIndex'));
			jQuery(this).parents('table:first').find('thead th:eq(' + index + ')').removeClass('arch-table-active');
		});


	/**калькулятор веса армии***************************************************************/
		//запрос рассчёта
		jQuery(".js-army-weight-compute").bind("change click", function()
		{
			var parseValue = function(elem){
				elem.val(elem.val().replace(/[kKкК]/g,'000'));
				elem.val(elem.val().replace(/[^\d]/g,''));
				var source = parseInt(elem.val(), 10);
				if( isNaN(source) || source <= 0 ){
					return false;
				}else{
					elem.val(source);
					return source;
				}
			};

			var result = jQuery(".js-army-weight-result");
			var idW = jQuery(".js-army-world").val();
			var weight1 = parseValue(jQuery(".js-army-weight1"));
			var weight2 = parseValue(jQuery(".js-army-weight2"));

			if( weight1 === false || weight2 === false )
			{
				printMessage('error', 'Некорректное значение веса', result);
				return;
			}

			result.html(loadImage);

			jQuery.post( '/ajax/army-battle-time-compute/',
			{
				'idW': idW,
				'weight1': weight1,
				'weight2': weight2,
				'format': 'json'
			},
			function(data)
			{
				if( typeof data.error !== 'undefined' )
				{
					printMessage('error', data.error, result);
				}else{
					printMessage('success', data.html, result);
				}
			},
			'json');
		});

/**форматер кк ****************************************************************************/
		//запрос форматирования
		jQuery(".js-redbutton-formater-submit").bind("click", function()
		{
			var result = jQuery(".js-redbutton-formater-result");
			var message = jQuery("textarea:first").val();

			result.html(loadImage);

			jQuery.post( '/ajax/redbutton-format/',
			{
				'message': message,
				'format': 'json'
			},
			function(data)
			{
				if( typeof data.error !== 'undefined' )
				{
					printMessage('error', data.error, result);
				}else{
					printMessage('success', data.html, result);
				}
			},
			'json');
		});


	/**изменения мира*******************************************************************/
		//создаём датапикер для быстрого перехода
		if (typeof minDate !== 'undefined' && typeof worldID !== 'undefined')
		{
			jQuery('#js-datepicker').datepicker(
			{
				dateFormat: 'dd-mm-yy',
				firstDay: 1,
				hideIfNoPrevNext: true,
				minDate: minDate,
				maxDate: "Today",
				onSelect: function(dateText, inst)
				{
					if( /^[\d]{2}-[\d]{2}-[\d]{4}$/.test(dateText) === true )
					{
						location = '/world/'+worldID+'/history/'+dateText+'/view.html';
					}else{
						alert('error 408');
					}

				}
			});
		}


	/**поиск игроков********************************************************************/
		//быстрый поиск по нику (по миру и глобальный в сервисах)

		//проверка на частые запросы
		var postSearch = null;
		var lockMicroTime = 0;

		jQuery(".js-search-term").bind("keyup change", function()
		{
			//новый локтайм и внутреннее время
			lockMicroTime = new Date().getTime();
			var innerTime = lockMicroTime;
			var target = this;

			//ждём 200 милисекунд и запускаем запрос с проверками
			//@TODO переписать на замыкание (IE8 bug =( )
			setTimeout(function()
			{
				if(lockMicroTime != innerTime)
					return false;

				var idW = jQuery(target).attr('idW');
				var term = jQuery.trim( jQuery(target).val() );
				var result = jQuery(".js-search-result");
				var colSpan = ( typeof idW === 'undefined') ? 5 : 6;
				result.parent().removeClass('hide');

				if( /^[\wА-ЯЁа-яё\s.-]{3,30}$/.test(term) !== true )
				{
					result.html('<tr><td colspan="'+colSpan+'">Такое условие поиска недопустимо.</td></tr>');
					return false;
				}

				//обрубаем лишние запросы
				if (postSearch != null)
					postSearch.abort();

				result.html('<tr><td colspan="'+colSpan+'">'+loadImage+'</td></tr>');

				postSearch = jQuery.post('/ajax/search/',
				{
					'idW': ( typeof idW == 'undefined') ? 0 : idW,
					'term': term,
					'format': 'html'
				},
				function(data){
					postSearch = null;
					result.html(data);
				},
				'html');

			},200);

		});

		//toggle быстрый
		jQuery(".js-fast-search-slider").click(function()
		{
			jQuery('.js-full-search-form').slideUp('fast');
			jQuery('.js-fast-search-form').slideToggle("normal");
		});
		//toggle расширенный
		jQuery(".js-full-search-slider").click(function()
		{
			jQuery('.js-fast-search-form').slideUp('fast');
			jQuery('.js-full-search-form').slideToggle("normal");
		});

		//создаём слайдеры
		jQuery(".js-ui-slider" ).each(function()
		{
			drawSlider( this );
		});

		//скрыть\открыть форму выбора фильтров по алам
		jQuery("#alliance").change(function()
		{
			if( jQuery(this).val() === 'none' )
			{
				jQuery('.js-alliance-filter').slideUp('fast');;
			}else{
				jQuery('.js-alliance-filter').slideDown('fast');
			}

		});


		//проверка сладеров (ручной ввод)
		jQuery(".js-advanced-search").submit(function()
		{
			var result = true;
			jQuery(".js-slider-validate").each(function()
			{
				var min = jQuery(this).find('input:first').val();
				var max = jQuery(this).find('input:last').val();

				if( parseInt(min,10) < 0 || parseInt(max,10) < 0 ||
					parseInt(min,10) >= parseInt(max,10) ||
					/^[0-9]+$/.test(min) !== true ||
					/^[0-9]+$/.test(max) !== true )
				{
					jQuery(this).find('input').addClass('invalid-form');
					result = false;
				}else{
					jQuery(this).find('input').removeClass('invalid-form');
				}
			});

			return result;
		});

		//открыть расширенные настройки расширенного поиска
		jQuery('.js-search-additional-props-show').click(function()
		{
			jQuery(this).hide();
			jQuery('.js-search-additional-props').slideDown('fast');
		});


	/**карта колец******************************************************************************/

		if (document.getElementById('mycarousel') !== null)
		{
			//ширина монитора - кол-во выводимых комплов
			var width = parseInt(jQuery("#mycarousel").width());

			//180 - ширина одного столбца
			//20 - ширина боковых столбцов цифр
			//40 - ширина стрелок по бокам
			var len = Math.floor( ( width - (20*2 + 40*2) ) / 180 );
			len = (len > 4) ? len : 4;

			//создаём и рисуем карту с заданными настройками
			var myCarousel = new Carousel(
				jQuery("#mycarousel .js-map-container"),
				jQuery('.js-map-info'),
				jQuery('#mycarousel .js-map-full-info-window.active'),
				len,
				parseInt(jQuery('.js-map-scroll').val()),
				jQuery('#mycarousel').attr('data-world-id'),
				parseInt(jQuery('.js-map-ring').val()),
				parseInt(jQuery('.js-map-ring option:selected').attr('data-max-compl')) );

			//первоначальный инит карты
			myCarousel.draw();

			//кнопка "вправо"
			jQuery('.js-map-right').click( function()
			{
				myCarousel.right();
			});

			//кнопка "лево"
			jQuery('.js-map-left').click( function()
			{
				myCarousel.left();
			});

			//быстрый переход
			jQuery('.js-map-ring').change( function()
			{
				myCarousel.changeRing( jQuery('.js-map-ring').val() );
			});

			//изменение скорости прокрутки
			jQuery('.js-map-scroll').change( function()
			{
				myCarousel.changeScroll( jQuery('.js-map-scroll').val() );
			});

			//быстрый переход к комплу
			jQuery('.js-map-goto').bind('change click keyup', function()
			{
				var num = jQuery(this).val();
				//console.log(num);
				if( /^[0-9]+$/.test(num) )
				{
					myCarousel.goTo( num );
				}
			});

			//переключение активного окошка подробной инфы
			jQuery('.js-map-full-info-window').click( function()
			{
				jQuery('.js-map-full-info-window').addClass('deactive').removeClass('active');
				jQuery(this).removeClass('deactive').addClass('active');

				jQuery('.js-map-full-info-window[empty].deactive').html('<div class="color-gray-map font-20 text-center mrg-top-75">Нажмите чтобы активировать</div>');
				jQuery('.js-map-full-info-window[empty].active').html('');

				myCarousel.setPlayerInfoBox( jQuery(this) );
			});


		}


	/**регистрация юзера******************************************************************/
		//проверка сложности пароля при вводе (в профиле тоже)
		jQuery("#pass").bind("keyup change click", function()
		{
			var classPattern = 'pass-strong-';
			var value = jQuery(this).val();
			var res = complexityPass(value);

			jQuery('.js-pass-strong div').each(function(key, el)
			{
				var currentClass = classPattern + (key+1);
				if( (key+1) > res )
					currentClass = classPattern + '0';
				jQuery(el).attr( 'class', currentClass );
			});
		});


	/**автопоиск авторизованных юзеров*********************************************************/

		//автовыбор соответствующего чекбокса при выборе варианта
		jQuery('.js-autosearch-select').bind('click focus change',function()
		{
			jQuery('.js-autosearch input[type=radio]').removeAttr('checked');
			jQuery(this).parent().find('input[type=radio]:first').attr('checked', 'checked');
		});

		//показать форму добавления/редактирования
		jQuery('.js-autosearch-form-show').click(function()
		{
			jQuery(this).removeClass('pseudo finger');
			jQuery('.js-autosearch-form').show();
		});

		//скрыть форму по отмене
		jQuery('.js-autosearch-form-hide').click(function()
		{
			jQuery('.js-autosearch-form-show').addClass('pseudo').addClass('finger');
			jQuery('.js-autosearch-form').hide();
		});

		//сохранить c проверками
		jQuery('.js-autosearch-form-submit').click(function()
		{
			var result = jQuery(".js-ajax-result");
			var form = jQuery('.js-autosearch-form');

			var type = form.find('input[name="typesearch"]:checked').val();

			var href = null;
			var post = {
				'prop': form.find('input[name="property"]').val(),
				'csrf': jQuery('#js-csrf-token').val(),
				'format': 'json'
			};

			if( type === 'new' )
			{
				href = '/ajax/autosearch-save-new/';
				post['newname'] = form.find('input[name="new_name"]').val();
				post['idW'] = worldID;

			}else if( type === 'edit' ){
				href = '/ajax/autosearch-save-as/';
				post['idA'] = form.find('select[name="edit_name"]').val();
			}

			if( href !== null )
			{
				result.html(loadImage);

				jQuery.post(href, post, function(data)
				{
					if( typeof data.error !== 'undefined' )
					{
						printMessage('error', data.error, result);
					}else{
						printMessage('success', data.success, result);
						jQuery('.js-autosearch').remove();
					}
				},
				'json');
			}

		});

		//удаление конкретного автопоиска из личного кабинета
		jQuery('.js-autosearch-delete').click( function()
		{
			if( !confirm( 'Вы уверены, что хотите удалить данный автопоиск?' ) )
				return false;

			var container = jQuery(this).parents('li:first');
			var result = jQuery(".js-ajax-result");
			var idA = jQuery(this).attr('idA');

			result.html(loadImage);

			jQuery.post(
				'/ajax/autosearch-del/',
				{
					'idA': idA,
					'csrf': jQuery('#js-csrf-token').val(),
					'format': 'json'
				},
				function(data)
				{
					if( typeof data.error !== 'undefined' )
					{
						printMessage('error', data.error, result);
					}else{
						printMessage('success', data.success, result);
						container.remove();
					}
				},
				'json');
		});

	/**списки игроков/алов**************************************************************/

		//выделение строки по клику на неё
		jQuery('.js-table-select tbody tr').live('click', function(e)
		{
			if( e.target.nodeName == 'A' )
				return;

			jQuery(this).toggleClass('select');
		});

		//редирект при изменении количества игроков на странице
		jQuery('.js-count-select').change(function()
		{
			location = jQuery(this).val();
		});

	/**страница игрока**********************************************************************/

		//добавление игрока в мониторинг
		jQuery(".js-monitor-add-player").live('click', function()
		{
			var container = jQuery(".js-monitor-player");
			var result = jQuery(".js-ajax-result");
			var idP = jQuery(this).attr('idPlayer');

			result.html(loadImage);

			jQuery.post( '/ajax/monitor-add/',
			{
				'idP': idP,
				'csrf': jQuery('#js-csrf-token').val(),
				'format': 'json'
			},
			function(data)
			{
				if( typeof data.error !== 'undefined' )
				{
					printMessage('error', data.error, result);
				}else{
					printMessage('success', data.html, result);
					container.remove();
				}
			},
			'json');
		});

		//удаление игрока из мониторинга
		jQuery(".js-monitor-del-player").live('click', function()
		{
			if( !confirm( 'Вы уверены, что хотите удалить игрока из мониторинга?' ) )
				return false;

			var container = jQuery(".js-monitor-player");
			var result = jQuery(".js-ajax-result");
			var idP = jQuery(this).attr('idPlayer');

			result.html(loadImage);

			jQuery.post( '/ajax/monitor-del/',
			{
				'idP': idP,
				'csrf': jQuery('#js-csrf-token').val(),
				'format': 'json'
			},
			function(data)
			{
				if( typeof data.error !== 'undefined' )
				{
					printMessage('error', data.error, result);
				}else{
					printMessage('success', data.html, result);
					container.remove();
				}
			},
			'json');
		});


	/** вся работа с графиками на сайте **************************************************************/

	if( jQuery('.js-graph-init').length > 0 ){
		var g = new Graph(jQuery('.js-graph-init').attr('data-init-type'));
		g.init();
	}




}); //end of document.ready


/**functions************************************************************************/

/*
 * Весь функционал карты игроков
 */
function Carousel(container, infobox, playerinfo, len, scroll, worldId, ring, maxCompl)
{
	this.container = jQuery(container); // контейнер с табличкой
	this.infobox = jQuery(infobox);     // контейнер с информацией и лоадгифом
	this.playerbox = jQuery(playerinfo);// контейнер с подробной информацией по игроку

	this.len  = len;                    // количество выводимых комплов
	this.scroll  = scroll;              // количество скролимых коплексов

	this.first     = 1;                 // номер первого видимого комплекса
	this.last      = len;               // номер последнего видимого комплекса
	this.maxCompl  = maxCompl;          // максимальный номер комплекса на кольце
	this.idW       = worldId;           // id мира
	this.ring      = ring;              // id кольца

	//вкл/выкл картинки загрузки в инфобоксе
	this.loadGif = function(bool)
	{
		this.infobox.html( (bool === true) ? loadImage : '');
	};

	//вывести информацию в инфобокс
	this.addInfo = function(str)
	{
		this.infobox.html( str );
	};

	//загружаем и выводим данные
	this.draw = function()
	{
		this.loadGif(true);

		var tmp = this;
		//запрос данных для карусели
		jQuery.post('/ajax/map/',
		{
			'idW': this.idW,
			'ring': this.ring,
			'first': this.first,
			'last':  this.last,
			'format' : 'json'
		},
		function(data)
		{
			if (typeof data.error !== 'undefined')
			{
				tmp.addInfo(data.error);
				return;
			}

			tmp.maxCompl  = parseInt( data.numMax );
			tmp.container.html( data.map );
			tmp.initEvents();
			tmp.loadGif( false );
		},
		'json');
	};

	//жмак "вправо"
	this.right = function()
	{
		if( (this.first + this.scroll) <= this.maxCompl )
		{
			this.first = this.first + this.scroll;
		}else{
			this.first = this.scroll - this.maxCompl + this.first;
		}

		if( (this.last  + this.scroll) <= this.maxCompl )
		{
			this.last = this.last + this.scroll;
		}else{
			this.last = this.scroll - this.maxCompl + this.last;
		}

		this.draw();
	};

	//жмак "влево"
	this.left = function()
	{
		if( (this.first - this.scroll) > 0 )
		{
			this.first = this.first - this.scroll;
		}else{
			this.first = this.maxCompl - this.scroll + this.first;
		}

		if( (this.last  - this.scroll) > 0 )
		{
			this.last  = this.last  - this.scroll;
		}else{
			this.last = this.maxCompl - this.scroll + this.last;
		}

		this.draw();
	};

	//смена кольца
	this.changeRing = function( idR )
	{
		if( !/^[0-9]+$/.test(idR) )
		{
			this.addInfo('Недопустимое значение номера кольца.');
			return;
		}

		this.ring  = parseInt(idR);
		this.first = 1;
		this.last  = this.len;

		this.draw();
	};

	//смена скорости прокрутки
	this.changeScroll = function( num )
	{
		if( !/^[0-9]+$/.test(num) )
		{
			this.addInfo('Недопустимое значение скроллинга');
			return;
		}
		this.scroll = parseInt(num);
	};

	//быстрая прокрутка к заданному комплексу
	this.goTo = function( num )
	{
		if( !/^[0-9]+$/.test(num) )
		{
			this.addInfo('Недопустимое значение номера комплекса');
			return;
		}

		num = parseInt(num);
		var inc = this.len - 1;

		this.first = (num >= this.maxCompl) ? this.maxCompl : num;

		if( (this.first  + inc) <= this.maxCompl )
		{
			this.last = this.first + inc;
		}else{
			this.last = inc  - this.maxCompl + this.first;
		}

		this.draw();
	};

	//инит эвентов
	this.initEvents = function()
	{
		//инит тултипсов
		jQuery('.js-map-icon').tooltip(
		{
			bodyHandler: function(){
				return '<b>' + jQuery(this).attr("name") + '</b>: ' + jQuery(this).attr("value");
			},
			delay: 0,
			track: true,
			extraClass: "map-tooltip"
		});

		//инит подробной инфы
		var tmp = this;
		jQuery('.js-map-cell').click( function(e)
		{
			if( e.target.nodeName == 'A' )
				return;

			tmp.getInfo( jQuery(this).attr('idP') );
		});
	};

	//получаем подробную инфу
	this.getInfo = function( idP )
	{
		if( !/^[0-9]+$/.test(idP) )
		{
			this.addInfo('Недопустимый идентификатор игрока.');
			return;
		}

		this.loadGif(true);
		var tmp = this;

		//запрос данных по игроку
		jQuery.post('/ajax/player-info/',
		{
			'idP': idP,
			'format' : 'json'
		},
		function(data)
		{
			if (typeof data.error !== 'undefined')
			{
				tmp.addInfo(data.error);
				return;
			}

			tmp.playerbox.html( data.html );
			tmp.playerbox.removeAttr('empty');
			tmp.playerbox.parents('div.hide:first').removeClass('hide');
			tmp.loadGif( false );
		},
		'json');
	};

	//установить новое активное окно подробной инфы
	this.setPlayerInfoBox = function( container )
	{
		this.playerbox = jQuery(container);
	};

}

/**
 * Весь функционал графиков
 *
 * @param {string} type
 * @returns void
 */
function Graph(type)
{
	this.type = type;

	this.chart;

	this.loadImage = loadImage;

	this.raseColors = ['#3d261f','#ff6f00','#136100','#050094'];

	this.redColor = '#e90000';
	this.greenColor = '#7f881d';
	this.grayColor = '#a5a5a5';

	this.barColors = [this.greenColor, this.redColor];

	this.pieColors = [
		'#ca4e74',/**/
		'#cc5472',
		'#d3666d',
		'#da7968',
		'#e08864',
		'#e89a5f',/**/
		'#c59657',
		'#af9352',
		'#838d48',
		'#6a8a43',
		'#4b863c',/**/
		'#497e42',
		'#46714c',
		'#415d5b',
		'#3f5362',
		'#383678',/**/
		'#573b77',
		'#6b3e77',
		'#854376',
		'#984675',
		'#ab4975'
	];

	this.showLoading = function(container){
		container.html(this.loadImage);
	};

	this.hideLoading = function(container){
		container.html('');
	};

	this.showGraphError = function(container, text){
		container.html('<div class="mrg-top-34 mrg-bottom-44 mrg-left-70"><img src="/img/eye_big.gif" alt="глазик">'+text+'</div>');
	};

	this.init = function(){
		switch(this.type){
			case 'online':
				this._initOnline();
				break;
			case 'index':
				this._initIndex();
				break;
			case 'world':
				this._initWorld();
				break;
				break;
			case 'alliance':
				this._initAlliance();
				break;
			case 'player':
				this._initPlayer();
				break;
		}
	};

	this._initIndex = function(){
		this._loadAndDrawIndexPieGraph();
	};

	this._initWorld = function(){
		var this_ = this;

		jQuery('.js-graph-menu-world a').click(function(){
			var selectClass = jQuery('.js-graph-menu-world').attr('selectclass');
			jQuery('.js-graph-menu-world a').removeClass(selectClass);
			jQuery(this).addClass(selectClass);
			this_._loadAndDrawWorldGraph(jQuery(this).attr('href').substring(1));
			return true;
		});

		var hash = this._getHashString();
		var type;
		switch(hash) {
			case 'in_out':
			case 'count_player':
			case 'count_colony':
			case 'count_alliance':
			case 'rank_old_sum':
			case 'rank_old_avg':
			case 'bo_sum':
			case 'bo_avg':
			case 'nra_sum':
			case 'nra_avg':
			case 'ra_sum':
			case 'ra_avg':
			case 'level_avg':
			case 'rank_new_sum':
			case 'rank_new_avg':
			case 'arch_sum':
			case 'arch_avg':
			case 'build_sum':
			case 'build_avg':
			case 'scien_sum':
			case 'scien_avg':
			case 'premium':
			case 'gate_not_avaliable':
				type = hash;
				break;
			default:
				type = 'count_player';
				break;
		}
		jQuery('.js-graph-menu-world a[href=#'+type+']').trigger('click');
	};

	this._initOnline = function(){
		var this_ = this;
		jQuery('.js-graph-menu-online a').click(function(){
			var selectClass = jQuery('.js-graph-menu-online').attr('selectclass');
			jQuery('.js-graph-menu-online a').removeClass(selectClass);
			jQuery(this).addClass(selectClass);
			this_._loadAndDrawOnlineGraph(jQuery(this).parents('li:first').attr('data-version'));
			return true;
		});

		var hash = this._getHashString();
		var version;
		switch(hash) {
			case 'classic':
			case 'unlim':
			case 'alpha':
			case 'de':
			case 'pulsar':
				version = hash;
				break;
			default:
				version = 'classic';
				break;
		}
		jQuery('.js-graph-menu-online li[data-version="' + version + '"] a').trigger('click');
	};

	this._initAlliance = function(){
		var this_ = this;

		jQuery('.js-graph-menu-alliance a').click(function(){
			var selectClass = jQuery('.js-graph-menu-alliance').attr('selectclass');
			jQuery('.js-graph-menu-alliance a').removeClass(selectClass);
			jQuery(this).addClass(selectClass);
			this_._loadAndDrawAllianceGraph(jQuery(this).attr('href').substring(1));
			return true;
		});

		var hash = this._getHashString();
		var type;
		switch(hash) {
			case 'count_player':
			case 'count_colony':
			case 'rank_old_sum':
			case 'rank_old_avg':
			case 'bo_sum':
			case 'bo_avg':
			case 'ra_sum':
			case 'ra_avg':
			case 'nra_sum':
			case 'nra_avg':
			case 'level_avg':
			case 'rank_new_sum':
			case 'rank_new_avg':
			case 'arch_sum':
			case 'arch_avg':
			case 'build_sum':
			case 'build_avg':
			case 'scien_sum':
			case 'scien_avg':
				type = hash;
				break;
			default:
				type = 'count_player';
				break;
		}
		jQuery('.js-graph-menu-alliance a[href=#'+type+']').trigger('click');
	};

	this._initPlayer = function(){
		var this_ = this;

		jQuery('.js-graph-menu-player a').click(function(){
			var selectClass = jQuery('.js-graph-menu-player').attr('selectclass');
			jQuery('.js-graph-menu-player a').removeClass(selectClass);
			jQuery(this).addClass(selectClass);
			this_._loadAndDrawPlayerGraph(jQuery(this).attr('href').substring(1));
			return true;
		});

		var hash = this._getHashString();
		var type;
		switch(hash) {
			case 'rank_old':
			case 'bo':
			case 'nra':
			case 'ra':
			case 'level':
			case 'rank_new':
			case 'archeology':
			case 'building':
			case 'science':
			case 'dshelp':
				type = hash;
				break;
			default:
				type = 'rank_old';
				break;
		}
		jQuery('.js-graph-menu-player a[href=#'+type+']').trigger('click');
	};

	this._getHashString = function(){
		return location.hash.substring(1);
	};

	this._prepareGraphDataDate = function(series){
		var parseDate = function( str ){
			var tmp = str.split('.');
			if( tmp.length === 2){ //месяцы
				tmp[0] = tmp[0]-1;
				return Date.UTC( tmp[1], tmp[0], 01, 00, 00);
			}else if( tmp.length === 3){ //дни
				tmp[1] = tmp[1]-1;
				return Date.UTC( tmp[2], tmp[1], tmp[0], 00, 00);
			}else if( tmp.length === 4){ // +часы
				tmp[2] = tmp[2]-1;
				return Date.UTC( tmp[3], tmp[2], tmp[1], tmp[0], 00);
			}else{ // +минуты
				tmp[3] = tmp[3]-1;
				return Date.UTC( tmp[4], tmp[3], tmp[2],  tmp[1], tmp[0] );
			}
		};

		for( var i in series ){
			for( var j in series[i].data ){
				series[i].data[j][0] = parseDate(series[i].data[j][0]);
				series[i].data[j][1] = Math.round(parseFloat(series[i].data[j][1]) * 100) / 100;
			}
		}
	};

	this._findDelta = function(data, xVal, decimalsNum){
		if( typeof decimalsNum === 'undefined' ){
			decimalsNum = 0;
		}

		var delta = '(<span style="color: ' + this.grayColor + ';">Без изменений</span>)';
		var curIndex = data.processedXData.indexOf(xVal);
		if( curIndex > 0 ){
			if( decimalsNum === 0 ){
				var deltaVal = Math.round(data.processedYData[curIndex]) - Math.round(data.processedYData[curIndex - 1]);
			}else{
				var deltaVal = data.processedYData[curIndex] - data.processedYData[curIndex - 1];
			}
			var color = (deltaVal < 0) ? this.redColor : this.greenColor;
			if(deltaVal > 0){
				delta = '(<span style="color: ' + color + '">+' + Highcharts.numberFormat(deltaVal, decimalsNum) + '</span>)';
			}else if(deltaVal < 0){
				delta = '(<span style="color: ' + color + '">' + Highcharts.numberFormat(deltaVal, decimalsNum) + '</span>)';
			}
		}
		return delta;
	};

	this._loadAndDrawOnlineGraph = function( version ){
		var container = jQuery('#graph-container');
		this.showLoading(container);

		var this_ = this;
		jQuery.post(
			'/ajax/graph-online/',
			{
				'format': 'json',
				'version' : version
			},
			function(res){
				if( typeof res.error !== 'undefined' ){
					this_.showGraphError(container, res.error);
				}else{
					this_.hideLoading(container);
					this_._drawStockGraphOnline(res.series, version);
				}
			}
			,'json');
	};

	this._drawStockGraphOnline = function(series){
		this._prepareGraphDataDate(series);
		var this_ = this;

		var options = {
			chart: {
				renderTo: 'graph-container',
				margin: [10, 10, 20, 10],
				zoomType: 'x',
				height: 500,
				type: 'area'
			},
			colors: this.raseColors,
			rangeSelector : {
				selected : 3,
				buttonTheme: {
					width: 85
				},
				buttons: [{
					type: 'week',
					count: 1,
					text: 'неделя'
				}, {
					type: 'month',
					count: 1,
					text: 'месяц'
				}, {
					type: 'year',
					count: 1,
					text: 'год'
				}, {
					type: 'all',
					text: 'всё время'
				}]
			},
			title: {
				enabled: false
			},
			legend:{
				enabled: false
			},
			credits:{
				enabled: false
			},
			xAxis: {
				gridLineWidth: 1,
				lineColor: '#000'
			},
			yAxis: {
				allowDecimals: false,
				labels: {
					style: {
						fontSize :'10px'
					}
				},
				lineColor: '#000',
				lineWidth: 0.5,
				minorTickInterval: 'auto',
				startOnTick: false,
				showFirstLabel: false
			},
			tooltip: {
				formatter: function(){
					return '<b>' + Highcharts.dateFormat('%H:00 %d.%m.%Y', this.x) + '</b><br>' +
							this.points[0].series.name + ': ' + '<b>' + Highcharts.numberFormat(this.y, 0) + '</b>';
				}
			},
			plotOptions: {
				area:{
					fillOpacity: 0.50
				},
				series:{
					dataGrouping: {
						groupPixelWidth: 5
					},
					lineWidth: 1,
					shadow: false,
					marker: {
						radius: 1
					},
					states: {
						hover: {
							lineWidth: 1
						}
					}
				}
			},
			series: series
		};


		this.chart = new Highcharts.StockChart(options);
	};

	this._loadAndDrawIndexPieGraph = function(){
		var container = jQuery('#graph-container');
		this.showLoading(container);

		var this_ = this;
		jQuery.post(
			'/ajax/graph-current-players-count/',
			{
				'format': 'json'
			},
			function(res){
				if( typeof res.error !== 'undefined' ){
					this_.showGraphError(container, res.error);
				}else{
					this_.hideLoading(container);
					this_._drawIndexPieGraph(res.series);
				}
			}
			,'json');
	};

	this._drawIndexPieGraph = function(series){
		var options = {
			chart: {
				renderTo: 'graph-container',
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false,
				defaultSeriesType: 'pie'
			},
			colors: this.pieColors,
			legend:{
				enabled: false,
				itemStyle:{
					font: '12px'
				}
			},
			credits:{
				enabled: false
			},
			title: {
				style: {
					color: '#636363'
				},
				text: 'Количество игроков в мирах (всего ' + series.total + ')'
			},
			tooltip: {
				formatter: function(){
					return '<b>' + this.point.name + '</b>: ' + this.y + ' (' + Math.round(this.percentage*100)/100 + '%)';
				}
			},
			plotOptions: {
				pie: {
					allowPointSelect: false,
					cursor: 'pointer',
					shadow: false,
					size: '62%',
					innerSize: '14%',
					dataLabels: {
						enabled: true,
						connectorColor: '#000000',
						softConnector: false,
						crop: false,
						connectorWidth: 0.5,
						formatter: function() {
							return '<b>'+ this.point.name +'</b>';
						}
					}
				}
			},
			series: [series]
		};

		this.chart = new Highcharts.Chart(options);
	};

	this._loadAndDrawWorldGraph = function(type){
		var container = jQuery('#graph-container');
		this.showLoading(container);

		var this_ = this;
		jQuery.post(
			'/ajax/graph-world/',
			{
				'format': 'json',
				'type': type,
				'idW' : parseInt( container.attr('iditem'),10 )
			},
			function(res)
			{
				if( typeof res.error !== 'undefined' ){
					this_.showGraphError(container, res.error);
					return;
				}

				this_.hideLoading(container);
				switch(type) {
					case 'in_out':
						this_._drawInOutGraphWorld(res.series,  res.title);
						break;
					default:
						this_._drawStockGraphGeneralStat(res.series,  res.title);
						break;
				}
			}
			,'json');
	};

	this._drawInOutGraphWorld = function(series, title){
		this._prepareGraphDataDate(series);
		var this_ = this;
		var options = {
			chart:{
				renderTo: 'graph-container',
				margin: [30, 10, 45, 10],
				zoomType: 'x',
				height: 500
			},
			rangeSelector : {
				selected : 0,
				buttonTheme: {
					width: 85
				},
				buttons: [{
					type: 'month',
					count: 1,
					text: 'месяц'
				}, {
					type: 'year',
					count: 1,
					text: 'год'
				}, {
					type: 'all',
					text: 'всё время'
				}]
			},
			colors: this.barColors,
			credits:{
				enabled: false
			},
			legend:{
				enabled: true
			},
			tooltip:{
				formatter: function(){
					var out = '<b>' + Highcharts.dateFormat('%d.%m.%Y', this.x) + '</b>';
					for( var i in this.points ){
						out += '<br/>' + this.points[i].series.name + ': <b>' + Highcharts.numberFormat(this.points[i].y, 0) + '</b>';
					}
					return out;
				}
			},
			title: {
				text: title,
				y: 7
			},
			xAxis: {
				gridLineWidth: 1,
				lineColor: '#000'
			},
			yAxis: {
				allowDecimals: false,
				labels: {
					style: {
						fontSize :'10px'
					}
				},
				lineColor: '#000',
				lineWidth: 0.5,
				minorTickInterval: 'auto',
				startOnTick: false,
				showFirstLabel: false
			},
			plotOptions: {
				series:{
					dataGrouping: {
						approximation: 'sum',
						groupPixelWidth: 10
					},
					lineWidth: 1,
					shadow: false,
					marker: {
						radius: 1
					},
					states: {
						hover: {
							lineWidth: 1
						}
					}
				}
			},
			series: series
		};

		this.chart = new Highcharts.StockChart(options);
	};

	this._drawStockGraphGeneralStat = function(series, title){
		this._prepareGraphDataDate(series);
		var this_ = this;

		var options = {
			chart:{
				renderTo: 'graph-container',
				margin: [30, 10, 45, 10],
				zoomType: 'x',
				height: 500
			},
			rangeSelector : {
				selected : 2,
				buttonTheme: {
					width: 85
				},
				buttons: [{
					type: 'month',
					count: 1,
					text: 'месяц'
				}, {
					type: 'year',
					count: 1,
					text: 'год'
				}, {
					type: 'all',
					text: 'всё время'
				}]
			},
			colors: this.raseColors,
			credits:{
				enabled: false
			},
			legend:{
				enabled: (series.length > 1)
			},
			tooltip:{
				formatter: function(){
					var out = '<b>' + Highcharts.dateFormat('%d.%m.%Y', this.x) + '</b>';
					for( var i in this.points ){
						out += '<br/>' + this.points[i].series.name + ': <b>' + Highcharts.numberFormat(this.points[i].y, 0) + '</b>';
						out += this_._findDelta(this.points[i].series, this.x);
					}
					return out;
				}
			},
			title: {
				text: title,
				y: 7
			},
			xAxis: {
				gridLineWidth: 1,
				lineColor: '#000'
			},
			yAxis: {
				allowDecimals: false,
				labels: {
					style: {
						fontSize :'10px'
					}
				},
				lineColor: '#000',
				lineWidth: 0.5,
				minorTickInterval: 'auto',
				startOnTick: false,
				showFirstLabel: false
			},
			plotOptions: {
				series:{
					dataGrouping: {
						groupPixelWidth: 10
					},
					lineWidth: 1,
					shadow: false,
					marker: {
						radius: 1
					},
					states: {
						hover: {
							lineWidth: 1
						}
					}
				}
			},
			series: series
		};

		this.chart = new Highcharts.StockChart(options);
	};

	this._loadAndDrawAllianceGraph = function(type){
		var container = jQuery('#graph-container');
		this.showLoading(container);

		var this_ = this;
		jQuery.post(
			'/ajax/graph-alliance/',
			{
				'format': 'json',
				'type': type,
				'idA' : parseInt( container.attr('iditem'),10 )
			},
			function(res){
				if( typeof res.error !== 'undefined' ){
					this_.showGraphError(container, res.error);
				}else{
					this_.hideLoading(container);
					this_._drawStockGraphGeneralStat(res.series,  res.title);
				}
			}
			,'json');
	};

	this._loadAndDrawPlayerGraph = function(type){
		var container = jQuery('#graph-container');
		this.showLoading(container);

		var this_ = this;
		jQuery.post(
			'/ajax/graph-player/',
			{
				'format': 'json',
				'type': type,
				'idP' : parseInt( container.attr('iditem'),10 )
			},
			function(res){
				if( typeof res.error !== 'undefined' ){
					this_.showGraphError(container, res.error);
				}else{
					this_.hideLoading(container);
					if(!!res.url){
						this_._drawExternalGraphDshelp(container, res.url);
					}else{
						this_._drawStockGraphSinglePlayer(res.series, res.decimal);
					}
				}
			}
			,'json');
	};

	this._drawExternalGraphDshelp = function(container, url){
		container.html('<img src=' + url + ' alt="График РА игрока с dshelp.info" />');
	};

	this._drawStockGraphSinglePlayer = function(series, decimals){
		this._prepareGraphDataDate(series);
		var this_ = this;

		var options = {
			chart:{
				renderTo: 'graph-container',
				margin: [10, 10, 20, 10],
				zoomType: 'x',
				height: 450
			},
			rangeSelector : {
				selected : 1,
				buttonTheme: {
					width: 85
				},
				buttons: [{
					type: 'week',
					count: 1,
					text: 'неделя'
				}, {
					type: 'month',
					count: 1,
					text: 'месяц'
				}, {
					type: 'all',
					text: 'всё время'
				}]
			},
			colors: this.raseColors,
			credits:{
				enabled: false
			},
			legend:{
				enabled: false
			},
			tooltip:{
				formatter: function(){
					var out = '<b>' + Highcharts.dateFormat('%H:%M %d.%m.%Y', this.x) + '</b>';
					for( var i in this.points ){
						out += '<br/>' + this.points[i].series.name + ': <b>' + Highcharts.numberFormat(this.points[i].y, (decimals) ? 2 : 0) + '</b>';
						out += this_._findDelta(this.points[i].series, this.x, (decimals) ? 2 : 0);
					}
					return out;
				}
			},
			title: {
				enabled: false
			},
			xAxis: {
				gridLineWidth: 1,
				lineColor: '#000'
			},
			yAxis: {
				allowDecimals: false,
				labels: {
					style: {
						fontSize :'10px'
					}
				},
				lineColor: '#000',
				lineWidth: 0.5,
				minorTickInterval: 'auto',
				startOnTick: false,
				showFirstLabel: false
			},
			plotOptions: {
				series:{
					dataGrouping: {
						enabled: false
					},
					lineWidth: 1,
					shadow: false,
					marker: {
						radius: 1
					},
					states: {
						hover: {
							lineWidth: 1
						}
					}
				}
			},
			series: series
		};

		this.chart = new Highcharts.StockChart(options);
	};
}

/*
 * печатает в блок ошибку или успешное сообщение
 */
function printMessage(type, message, container)
{
	if(type === 'error')
	{
		container.addClass('color-red');
		container.removeClass('color-green');
		container.html(message);
	}else if( type === 'success' ){
		container.removeClass('color-red');
		container.addClass('color-green');
		container.html(message);
	}
}

/*
 *вычисление сложности пароля
 * @return int [0,5] степень сложности пароля
 */
function complexityPass( value )
{
	var res = Math.floor(value.length /10 );

	if( /[a-z]+/.test( value ) )
		res++;

	if( /[A-Z]+/.test( value ) )
		res++;

	if( /[0-9]+/.test( value ) )
		res++;

	if( /[!@#$%^&*()_+=-]+/.test( value ) )
		res++;

	if( res > 5 )
		res = 5;

	return res;
}

/*
 * рисуем слайдер
 */
function drawSlider(node)
{
	var name = jQuery(node).attr('name');
	var nodeMin = jQuery('#'+name+'_min');
	var nodeMax = jQuery('#'+name+'_max');
	var max = parseInt(jQuery(node).attr('max'));
	var minVal = parseInt(nodeMin.val());
	var maxVal = parseInt(nodeMax.val());

	//console.log(node,name,max,minVal, maxVal);

	jQuery(node).slider(
	{
		range: true,
		min: 0,
		step: 1,
		max: max,
		values: [ minVal, maxVal ],
		slide: function( event, ui )
		{
			nodeMin.val( parseInt(ui.values[0]) );
			nodeMax.val( parseInt(ui.values[1]) );
		}
	});
}

/*
 * открываем/закрываем новость и меняем цвет заголовка
 */
function toggleNews(node, action)
{
	if(action === 'toggle')
	{
		jQuery(node).toggleClass('color-logo');
		jQuery(node).toggleClass('bold');
		jQuery(node).toggleClass('pseudo');
		jQuery(node).parent().children('p').slideToggle("normal");
	}else if(action === 'open'){
		jQuery(node).removeClass('pseudo');
		jQuery(node).addClass('color-logo bold');
		jQuery(node).parent().children('p').slideDown("normal");
	}else if(action === 'close'){
		jQuery(node).addClass('pseudo');
		jQuery(node).removeClass('color-logo bold');
		jQuery(node).parent().children('p').slideUp("normal");
	}
}

String.prototype.reverse=function(){
	return this.split("").reverse().join("");
};

function DetectBrowserForAddon(userAgent)
{
	var result = false;

	if( /Firefox/i.test(userAgent) ){
		result = "firefox";
	}
	if( /Chrome/i.test(userAgent) ){
		result = "chrome";
	}
	if ( /Opera/i.test(userAgent) ){
		result = "opera";
	}

	return result;
}

Highcharts.setOptions({
	lang: {
		thousandsSep: '`',
		months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
		shortMonths: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
		rangeSelectorFrom: 'От',
		rangeSelectorTo: 'До',
		rangeSelectorZoom: 'Приблизить',
		weekdays: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота']
	}
});
