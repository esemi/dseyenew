jQuery(document).ready(function()
{
		loadImage = '<img src="/img/load.gif" />';

		//редирект по мирам из выпадающего списка
		jQuery('.js-world-select').change(function()
		{
			var href = jQuery(this).val();
			if( typeof href != 'undefined' )
				location = href;
		});


		//переход вверх страницы
		jQuery(".js-get-top").click(function()
		{
			window.scroll(0,0);
		});


		//фокус на поле формы
		jQuery('.js-form-focus:first').focus();


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
		if ( idN != '' && node.length === 1)
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

	/**статистика онлайна***************************************************************/
		//меню по графикам статистики онлайна
		jQuery('.js-graph-menu-online a').click(function()
		{
			loadAndDrawOnlineGraph(jQuery(this));
			return true;
		});

	/**страница аддона*******************************************************************/
		//выбор ссылки по умолчанию
		if( jQuery(".js-addon-selector").length >0){
			var browser = DetectBrowserForAddon(window.navigator.userAgent);

			if (browser !== false){
				var link = jQuery(".js-addon-selector option:contains('" + browser + "')").val();
				console.log(link);
			}
		}

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
			var parseValue = function(elem)
			{
				elem.val(elem.val().replace(/[kKкК]/g,'000'));
				elem.val(elem.val().replace(/[^\d]/g,''));
				var source = parseInt(elem.val(), 10);
				if( isNaN(source) || source <= 0 ){
					return false;
				}else{
					elem.val(source);
					return source;
				}
			}

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
				if( typeof data.error != 'undefined' )
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
				if( typeof data.error != 'undefined' )
				{
					printMessage('error', data.error, result);
				}else{
					printMessage('success', data.html, result);
				}
			},
			'json');
		});



	/**статистика мира***************************************************************/
		//меню по графикам статистики миров
		jQuery('.js-graph-menu-world a').click(function()
		{
			loadAndDrawWorldGraph(jQuery(this));
			return true;
		});


	/**статистика альянса***************************************************************/
		//меню по графикам статистики альянсов
		jQuery('.js-graph-menu-alliance a').click(function()
		{
			loadAndDrawAllianceGraph(jQuery(this));
			return true;
		});


	/**изменения мира*******************************************************************/
		//создаём датапикер для быстрого перехода
		if (typeof minDate != 'undefined' && typeof worldID != 'undefined')
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
				var colSpan = ( typeof idW == 'undefined') ? 5 : 6;
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
			if( jQuery(this).val() == 'none' )
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
					/^[0-9]+$/.test(min) != true ||
					/^[0-9]+$/.test(max) != true )
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
		})


	/**карта колец******************************************************************************/

		if (document.getElementById('mycarousel') != null)
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
				worldID,
				parseInt(jQuery('.js-map-ring').val()),
				maxCompl );

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

			if( type == 'new' )
			{
				href = '/ajax/autosearch-save-new/';
				post['newname'] = form.find('input[name="new_name"]').val();
				post['idW'] = worldID;

			}else if( type == 'edit' ){
				href = '/ajax/autosearch-save-as/';
				post['idA'] = form.find('select[name="edit_name"]').val();
			}

			if( href != null )
			{
				result.html(loadImage);

				jQuery.post(href, post, function(data)
				{
					if( typeof data.error != 'undefined' )
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
					if( typeof data.error != 'undefined' )
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

		//меню по графикам игрока
		jQuery('.js-graph-menu-player a').click( function()
		{
			loadAndDrawPlayerGraph(jQuery(this));
			return true
		});

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
				if( typeof data.error != 'undefined' )
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
				if( typeof data.error != 'undefined' )
				{
					printMessage('error', data.error, result);
				}else{
					printMessage('success', data.html, result);
					container.remove();
				}
			},
			'json');
		});


}); //end of document.ready


//цвета для графиков
//@TODO Убрать зависимость
var colorRed = 'color:#e90000;';
var colorGreen = 'color: #7f881d';
var colorGray = 'color: #a5a5a5;';
var chart = null;
var chartColors =['#3d261f','#ff6f00','#136100','#050094'];




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
		this.infobox.html( (bool == true) ? loadImage : '');
	}

	//вывести информацию в инфобокс
	this.addInfo = function(str)
	{
		this.infobox.html( str );
	}

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
			if (typeof data.error != 'undefined')
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
	}

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
	}

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
	}

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
	}

	//смена скорости прокрутки
	this.changeScroll = function( num )
	{
		if( !/^[0-9]+$/.test(num) )
		{
			this.addInfo('Недопустимое значение скроллинга');
			return;
		}
		this.scroll = parseInt(num);
	}

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
	}

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
	}

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
			if (typeof data.error != 'undefined')
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
	}

	//установить новое активное окно подробной инфы
	this.setPlayerInfoBox = function( container )
	{
		this.playerbox = jQuery(container);
	}

}

/*
 * Весь функционал графиков
 */
function Graph()
{

}

/*
 * печатает в блок ошибку или успешное сообщение
 */
function printMessage(type, message, container)
{
	if(type == 'error')
	{
		container.addClass('color-red');
		container.removeClass('color-green');
		container.html(message);
	}else if( type == 'success' ){
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
	if(action == 'toggle')
	{
		jQuery(node).toggleClass('color-logo');
		jQuery(node).toggleClass('bold');
		jQuery(node).toggleClass('pseudo');
		jQuery(node).parent().children('p').slideToggle("normal");
	}else if(action == 'open'){
		jQuery(node).removeClass('pseudo');
		jQuery(node).addClass('color-logo bold');
		jQuery(node).parent().children('p').slideDown("normal");
	}else if(action == 'close'){
		jQuery(node).addClass('pseudo');
		jQuery(node).removeClass('color-logo bold');
		jQuery(node).parent().children('p').slideUp("normal");
	}
}

/*
 * График количества игроков в мирах на главной
*/
function loadAndDrawIndexPieGraph()
{
	var container = jQuery('#graph-container-pie');
	printGraphLoad(container);

	jQuery.post(
		'/ajax/graph-current-players-count/',
		{
			'format': 'json'
		},
		function(res)
		{
			if( typeof res.error != 'undefined' )
			{
				printGraphError(container, res.error );
			}else{
				container.html();
				drawIndexPieGraph(res.series);
			}
		}
		,'json');
}


/*
 * грузит и рисует график онлайна
 * если пункт меню не указан - пытается взять тип из хеша урла. По дефолту classic_hour
 */
function loadAndDrawOnlineGraph(target)
{
	var selectClass = jQuery('.js-graph-menu-online').attr('selectclass');

	var type_graph = 'hour';
	var version = 'classic';

	if( typeof target != 'undefined' )
	{
		version = target.parent().attr('version');
		type_graph = target.parent().attr('type');
	}else{
		var hash = location.hash.substring(1);
		switch(hash) {
			case 'classic_hour':
			case 'classic_day':
			case 'unlim_hour':
			case 'unlim_day':
			case 'alpha_hour':
			case 'alpha_day':
			case 'de_hour':
			case 'de_day':
				var tmp = hash.split('_');
				version = tmp[0];
				type_graph = tmp[1];
				break;
			default:
				hash = 'classic_hour';
				break;
		}
		target = jQuery('.js-graph-menu-online a[href="#'+ hash +'"]');
	}

	jQuery('.js-graph-menu-online a').removeClass(selectClass);
	target.addClass(selectClass);

	var container = jQuery('#graph-container');

	printGraphLoad(container);

	jQuery.post(
		'/ajax/graph-online/',
		{
			'format': 'json',
			'type': type_graph,
			'version' : version
		},
		function(res)
		{
			if( typeof res.error != 'undefined' )
			{
				printGraphError(container, res.error );
			}else{
				container.html();

				if( res.series.length == 1)
					drawHourGraphOnline(res.series, version);
				else
					drawDayGraphOnline(res.series, version);
			}
		}
		,'json');
}

/*
 * грузит и рисует график игрока
 * если пункт меню не указан - пытается взять тип из хеша урла. По дефолту rank_old
 */
function loadAndDrawPlayerGraph(target)
{
	var selectClass = jQuery('.js-graph-menu-player').attr('selectclass');

	var type = 'rank_old';
	if( typeof target != 'undefined' )
	{
		type = target.parent().attr('graph');
	}else{
		var hash = location.hash.substring(1);
		switch(hash) {
			case 'rank_old':
			case 'bo':
			case 'nra':
			case 'ra':
			case 'level':
			case 'mesto':
			case 'rank_new':
			case 'archeology':
			case 'building':
			case 'science':
			case 'summary':
			case 'dshelp':
				type = hash;
				break;
			default:
				type = 'rank_old';
				break;
		}
		target = jQuery('.js-graph-menu-player li[graph='+type+'] a');
	}

	jQuery('.js-graph-menu-player a').removeClass(selectClass);
	target.addClass(selectClass);

	var container = jQuery('#graph-container');

	printGraphLoad(container);

	jQuery.post(
		'/ajax/graph-player/',
		{
			'format': 'json',
			'type': type,
			'idP' : parseInt( container.attr('iditem'),10 )
		},
		function(res)
		{
			if( typeof res.error != 'undefined' )
			{
				printGraphError(container, res.error );
			}else{
				container.html();

				if(!!res.url)
					container.html('<img src='+res.url+' alt="График РА игрока с dshelp.info" />');
				else if(res.series.length == 1)
					drawSingleGraphPlayer( res.series );
				else
					drawSumGraphPlayer( res.series, res.borders );
			}
		}
		,'json');
}


/*
 * грузит и рисует график мира
 * если пункт меню не указан - пытается взять тип из хеша урла. По дефолту in_out_day
 */
function loadAndDrawWorldGraph(target)
{
	var selectClass = jQuery('.js-graph-menu-world').attr('selectclass');

	var type = 'in_out_day';
	if( typeof target != 'undefined' )
	{
		type = target.attr('href').substring(1);
	}else{
		var hash = location.hash.substring(1);
		switch(hash) {
			case 'in_out_day':
			case 'in_out_all':
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
				type = hash;
				break;
			default:
				type = 'in_out_day';
				break;
		}
		target = jQuery('.js-graph-menu-world a[href=#'+type+']');
	}

	jQuery('.js-graph-menu-world a').removeClass(selectClass);
	target.addClass(selectClass);

	var container = jQuery('#graph-container');

	printGraphLoad(container);

	jQuery.post(
		'/ajax/graph-world/',
		{
			'format': 'json',
			'type': type,
			'idW' : parseInt( container.attr('iditem'),10 )
		},
		function(res)
		{
			if( typeof res.error != 'undefined' )
			{
				printGraphError(container, res.error );
				return;
			}

			container.html();
			switch(type) {
				case 'in_out_day':
					drawInOutGraph(res.series, 'Пришли/ушли за месяц', true);
					break;
				case 'in_out_all':
					drawInOutGraph(res.series, 'Пришли/ушли за всё время', false);
					break;
				case 'count_player':
					drawStatWorldGraph(res.series, 'Количество игроков');
					break;
				case 'count_colony':
					drawStatWorldGraph(res.series, 'Количество колоний');
					break;
				case 'count_alliance':
					drawStatWorldGraph(res.series, 'Количество альянсов');
					break;
				case 'rank_old_sum':
					drawStatWorldGraph(res.series, 'Суммарный рейтинг (стар.)');
					break;
				case 'rank_old_avg':
					drawStatWorldGraph(res.series, 'Средний рейтинг (стар.)');
					break;
				case 'bo_sum':
					drawStatWorldGraph(res.series, 'Суммарный боевой рейтинг');
					break;
				case 'bo_avg':
					drawStatWorldGraph(res.series, 'Средний боевой рейтинг');
					break;
				case 'nra_sum':
					drawStatWorldGraph(res.series, 'Суммарный новый рейтинг активности');
					break;
				case 'nra_avg':
					drawStatWorldGraph(res.series, 'Средний новый рейтинг активности');
					break;
				case 'ra_sum':
					drawStatWorldGraph(res.series, 'Суммарный рейтинг активности');
					break;
				case 'ra_avg':
					drawStatWorldGraph(res.series, 'Средний рейтинг активности');
					break;
				case 'level_avg':
					drawStatWorldGraph(res.series, 'Средний уровень');
					break;
				case 'rank_new_sum':
					drawStatWorldGraph(res.series, 'Суммарный рейтинг (нов.)');
					break;
				case 'rank_new_avg':
					drawStatWorldGraph(res.series, 'Средний рейтинг (нов.)');
					break;
				case 'arch_sum':
					drawStatWorldGraph(res.series, 'Суммарная археология');
					break;
				case 'arch_avg':
					drawStatWorldGraph(res.series, 'Средняя археология');
					break;
				case 'build_sum':
					drawStatWorldGraph(res.series, 'Суммарное строительство');
					break;
				case 'build_avg':
					drawStatWorldGraph(res.series, 'Среднее строительство');
					break;
				case 'scien_sum':
					drawStatWorldGraph(res.series, 'Суммарная наука');
					break;
				case 'scien_avg':
					drawStatWorldGraph(res.series, 'Средняя наука');
					break;
				default:
					return;
					break;
			}
		}
		,'json');
}


/*
 * грузит и рисует график мира
 * если пункт меню не указан - пытается взять тип из хеша урла. По дефолту in_out_day
 */
function loadAndDrawAllianceGraph(target)
{
	var selectClass = jQuery('.js-graph-menu-alliance').attr('selectclass');

	var type = 'count_player';
	if( typeof target != 'undefined' )
	{
		type = target.attr('href').substring(1);
	}else{
		var hash = location.hash.substring(1);
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
		target = jQuery('.js-graph-menu-alliance a[href=#'+type+']');
	}

	jQuery('.js-graph-menu-alliance a').removeClass(selectClass);
	target.addClass(selectClass);

	var container = jQuery('#graph-container');

	printGraphLoad(container);

	jQuery.post(
		'/ajax/graph-alliance/',
		{
			'format': 'json',
			'type': type,
			'idA' : parseInt( container.attr('iditem'),10 )
		},
		function(res)
		{
			if( typeof res.error != 'undefined' )
			{
				printGraphError(container, res.error );
				return;
			}

			container.html();
			switch(type) {
				case 'count_player':
					drawStatWorldGraph(res.series, 'Количество игроков');
					break;
				case 'count_colony':
					drawStatWorldGraph(res.series, 'Количество колоний');
					break;
				case 'rank_old_sum':
					drawStatWorldGraph(res.series, 'Суммарный рейтинг (стар.)');
					break;
				case 'rank_old_avg':
					drawStatWorldGraph(res.series, 'Средний рейтинг (стар.)');
					break;
				case 'bo_sum':
					drawStatWorldGraph(res.series, 'Суммарный боевой рейтинг');
					break;
				case 'bo_avg':
					drawStatWorldGraph(res.series, 'Средний боевой рейтинг');
					break;
				case 'ra_sum':
					drawStatWorldGraph(res.series, 'Суммарный рейтинг активности');
					break;
				case 'ra_avg':
					drawStatWorldGraph(res.series, 'Средний рейтинг активности');
					break;
				case 'nra_sum':
					drawStatWorldGraph(res.series, 'Суммарный новый рейтинг активности');
					break;
				case 'nra_avg':
					drawStatWorldGraph(res.series, 'Средний новый рейтинг активности');
					break;
				case 'level_avg':
					drawStatWorldGraph(res.series, 'Средний уровень');
					break;
				case 'rank_new_sum':
					drawStatWorldGraph(res.series, 'Суммарный рейтинг (нов.)');
					break;
				case 'rank_new_avg':
					drawStatWorldGraph(res.series, 'Средний рейтинг (нов.)');
					break;
				case 'arch_sum':
					drawStatWorldGraph(res.series, 'Суммарная археология');
					break;
				case 'arch_avg':
					drawStatWorldGraph(res.series, 'Средняя археология');
					break;
				case 'build_sum':
					drawStatWorldGraph(res.series, 'Суммарное строительство');
					break;
				case 'build_avg':
					drawStatWorldGraph(res.series, 'Среднее строительство');
					break;
				case 'scien_sum':
					drawStatWorldGraph(res.series, 'Суммарная наука');
					break;
				case 'scien_avg':
					drawStatWorldGraph(res.series, 'Средняя наука');
					break;
				default:
					return;
					break;
			}
		}
		,'json');
}


//выводит ошибку графика в контейнер
function printGraphError(container, text)
{
	container.html('<div class="mrg-top-34 mrg-bottom-44 mrg-left-70"><img src="/img/eye_big.gif" alt="глазик">'+text+'</div>');
}

//выводит load.gif графика
function printGraphLoad(container)
{
	container.html(loadImage);
}

//подготовка данных для графика дата-число
function prepareGraphDataDate( data )
{
	var parseDate = function ( str )
	{
		var tmp = str.split('.');
		if( tmp.length == 2){ //месяцы
			tmp[0] = tmp[0]-1;
			return Date.UTC( tmp[1], tmp[0], 01, 00, 00);
		}else if( tmp.length == 3){ //дни
			tmp[1] = tmp[1]-1;
			return Date.UTC( tmp[2], tmp[1], tmp[0], 00, 00);
		}else if( tmp.length == 4){ // +часы
			tmp[2] = tmp[2]-1;
			return Date.UTC( tmp[3], tmp[2], tmp[1], tmp[0], 00);
		}else{ // +минуты
			tmp[3] = tmp[3]-1;
			return Date.UTC( tmp[4], tmp[3], tmp[2],  tmp[1], tmp[0] );
		}
	}

	for( var i in data )
	{
		for( var j in data[i].data )
		{
			data[i].data[j][0] = parseDate(data[i].data[j][0]);
			data[i].data[j][1] = Math.round(parseFloat(data[i].data[j][1]) * 100) / 100;
		}
	}
}

//вычисляет индексный словарь дельт значений графика
function _getGraphDelta( data )
{
	var out = {};

	for( var i in data )
		for( var j = 1; j < data[i].data.length; j++ )
			out[i + '_' + data[i].data[j][0]] = Math.round(parseFloat(data[i].data[j][1] - data[i].data[j-1][1]) * 100) / 100;

	return out;
}

//вычисляет индексный словарь исходных значений графика (для нормированных тултипсов)
function _getGraphSource( data )
{
	var out = {};

	for( var i in data )
	{
		for( var j in data[i].data )
			out[i + '_' + data[i].data[j][0]] = data[i].data[j][1];
	}

	return out;
}

//рисуем график мира по рассам
function drawStatWorldGraph(series, title)
{
	prepareGraphDataDate( series );
	var delta = _getGraphDelta( series );

	var options = {
		chart: {
			renderTo: 'graph-container',
			zoomType: 'xy',
			margin: [30, 10, 45, 60],
			defaultSeriesType: 'line'
		},
		title: {
			text: title,
			y: 7
		},
		legend:{
			enabled: (series.length > 1),
			y:15
		},
		credits:{
			enabled: false
		},
		xAxis: {
			gridLineWidth: 1,
			lineColor: '#000',
			type: 'datetime',
			maxZoom: 7 * 24 * 3600000,
			title: {
				text: null
			}
		},
		yAxis: {
			labels: {
				style: {
					fontSize :'10px'
				}
			},
			allowDecimals: false,
			minorTickInterval: 'auto',
			lineColor: '#000',
			lineWidth: 0.5,
			title: {
				text: null
			},
			startOnTick: false,
			showFirstLabel: false
		},
		tooltip: {
			formatter: function()
			{
				return '<b>' + Highcharts.dateFormat('%d.%m.%Y', this.x) + '</b><br>' +
					this.series.name + ': ' +'<b>' + numFormat(this.y) + '</b> ' +
					getDelta(delta, this.series.index +'_'+ this.x);
			}
		},
		colors: chartColors,
		plotOptions: {
			lineWidth: 1,
			shadow: false,
			series:{
				marker: {
					symbol:'circle',
					radius: 1
				}
			},
			states: {
				hover: {
					lineWidth: 1
				}
			}
		},
		series: series
	};

	chart = new Highcharts.Chart(options);
}

//рисуем сводный график игрока
function drawSumGraphPlayer(series, borders)
{
	prepareGraphDataDate( series );

	var delta = _getGraphDelta( series );

	var source = _getGraphSource( series )

	//нормирование серий
	for( var i in series )
	{
		for( var j in series[i].data )
		{
			if(borders[series[i].realname]['max'] == 0.0 || (borders[series[i].realname]['min'] == borders[series[i].realname]['max']) )
				series[i].data[j][1] = 0.0;
			else
				series[i].data[j][1] = ( (series[i].data[j][1] - borders[series[i].realname]['min']) / (borders[series[i].realname]['max'] - borders[series[i].realname]['min']) ) * 100;
		}
	}

	var options = {
		chart: {
			renderTo: 'graph-container',
			zoomType: 'xy',
			margin: [10, 10, 45, 60],
			defaultSeriesType: 'line'
		},
		title: {
			text: ''
		},
		legend:{
			enabled: true,
			y:15
		},
		credits:{
			enabled: false
		},
		xAxis: {
			gridLineWidth: 1,
			lineColor: '#000',
			type: 'datetime',
			maxZoom: 3 * 24 * 3600000,
			title: {
				text: null
			}
		},
		yAxis: {
			min: -5,
			max: 105,
			labels: {
				style: {
					fontSize :'10px'
				}
			},
			allowDecimals: false,
			minorTickInterval: 'auto',
			lineColor: '#000',
			lineWidth: 0.5,
			title: {
				text: 'проценты',
				style: {
					color: '#6e0022',
					fontWeight: 'normal'
				},
				align: 'high'
			},
			startOnTick: false,
			showFirstLabel: false
		},
		tooltip: {
			crosshairs: false,
			shared: false,
			formatter: function()
			{
				return '<b>' + Highcharts.dateFormat('%H:%M %d.%m.%Y', this.x) + '</b><br>' +
					'<span style="color:' + this.series.color + ';">' + this.series.name + '</span> : ' +
					'<b>' + numFormat(source[this.series.index +'_'+ this.x]) + '</b> ' +
					getDelta(delta, this.series.index +'_'+ this.x, (this.series.realname == 'mesto') ? true : false);
			}
		},
		plotOptions: {
			lineWidth: 1,
			shadow: false,
			series:{
				marker: {
					symbol:'circle',
					radius: 2
				}
			},
			states: {
				hover: {
					lineWidth: 1
				}
			}
		},
		series: series
	};


	chart = new Highcharts.Chart(options);
}

//рисуем одиночный график игрока
function drawSingleGraphPlayer(series)
{
	prepareGraphDataDate( series );

	var delta = _getGraphDelta( series );

	var reversed = (series[0].realname == 'mesto');

	var options = {
		chart: {
			renderTo: 'graph-container',
			zoomType: 'xy',
			margin: [10, 10, 20, 60],
			defaultSeriesType: 'line'
		},
		title: {
			text: ''
		},
		legend:{
			enabled: false
		},
		credits:{
			enabled: false
		},
		xAxis: {
			gridLineWidth: 1,
			lineColor: '#000',
			type: 'datetime',
			maxZoom: 3 * 24 * 3600000,
			title: {
				text: null
			}
		},
		yAxis: {
			labels: {
				style: {
					fontSize :'10px'
				}
			},
			allowDecimals: false,
			minorTickInterval: 'auto',
			lineColor: '#000',
			lineWidth: 0.5,
			title: {
				text: null
			},
			startOnTick: false,
			showFirstLabel: false,
			reversed: reversed
		},
		tooltip: {
			formatter: function()
			{
				return '<b>' + Highcharts.dateFormat('%H:%M %d.%m.%Y', this.x) + '</b><br>' +
					'<span style="color:' + this.series.color + ';">' + this.series.name + '</span> : ' +
					'<b>' + numFormat(this.y) + '</b> ' +
					getDelta(delta, this.series.index +'_'+ this.x, reversed);
			}
		},
		plotOptions: {
			lineWidth: 1,
			shadow: false,
			series:{
				marker: {
					symbol:'circle',
					radius: 2
				}
			},
			states: {
				hover: {
					lineWidth: 1
				}
			}
		},
		series: series
	};

	chart = new Highcharts.Chart(options);
}

//рисуем почасовой график онлайна
function drawHourGraphOnline(series, version)
{
	prepareGraphDataDate( series );

	var options = {
		chart: {
			renderTo: 'graph-container',
			margin: [30, 10, 20, 60],
			zoomType: 'x',
			defaultSeriesType: 'area'
		},
		title: {
			style: {
				color: '#636363'
			},
			y: 5,
			text: 'Количество игроков online по часам (' + version + ')'
		},
		legend:{
			enabled: false
		},
		credits:{
			enabled: false
		},
		xAxis: {
			gridLineWidth: 1,
			lineColor: '#000',
			type: 'datetime',
			maxZoom: 1 * 24 * 3600000, // максимальный зум
			title: {
				text: null
			}
		},
		yAxis: {
			labels: {
				style: {
					fontSize : '10px'
				}
			},
			allowDecimals: false,
			minorTickInterval: 'auto',
			lineColor: '#000',
			lineWidth: 1,
			title: {
				text: null
			},
			startOnTick: false,
			showFirstLabel: false
		},
		tooltip: {
			formatter: function()
			{
				  return '<b>' + Highcharts.dateFormat('%H:00 %d.%m.%Y', this.x) + '</b><br>' +
						  this.series.name + ': ' + '<b>' + numFormat(this.y) + '</b>';
			}
		},
		colors: chartColors,
		plotOptions: {
			area: {
				fillOpacity: .50,
				lineWidth: 1,
				marker: {
					enabled: false,
					states: {
						hover: {
							enabled: true,
							radius: 4
						}
					}
				},
				shadow: false,
				states: {
					hover: {
						lineWidth: 1
					}
				}
			}
		},
		series: series
	};

	chart = new Highcharts.Chart(options);
}

//рисуем график онлайна за всё время (с агрегацией)
function drawDayGraphOnline(series, version)
{
	prepareGraphDataDate( series );

	var options = {
		chart: {
			renderTo: 'graph-container',
			zoomType: 'xy',
			margin: [30, 10, 45, 60],
			defaultSeriesType: 'line'
		},
		title: {
			style: {
				color: '#636363'
			},
			text: 'Количество игроков online по дням (' + version + ')'
		},
		legend:{
			y: 15
		},
		credits:{
			enabled: false
		},
		xAxis: {
			gridLineWidth: 1,
			lineColor: '#000',
			type: 'datetime',
			maxZoom: 7 * 24 * 3600000, // максимальный зум - неделя
			title: {
				text: null
			}
		},
		yAxis: {
			labels: {
				style: {
					fontSize :'10px'
				}
			},
			allowDecimals: false,
			minorTickInterval: 'auto',
			lineColor: '#000',
			lineWidth: 0.5,
			title: {
				text: null
			},
			startOnTick: false,
			showFirstLabel: false
		},
		tooltip: {
			formatter: function()
			{
				return '<b>' + Highcharts.dateFormat('%d.%m.%Y', this.x) + '</b><br>' +
						this.series.name + ': ' + '<b>' + numFormat(this.y) + '</b>';
			}
		},
		colors: chartColors,
		plotOptions: {
			lineWidth: 1,
			shadow: false,
			series:{
				marker: {
					symbol:'circle',
					radius: 2
				}
			},
			states: {
				hover: {
					lineWidth: 1
				}
			}
		},
		series: series
	};

	chart = new Highcharts.Chart(options);
}

//Рисует пай чарт на главной стртанице
function drawIndexPieGraph(series)
{
	var options = {
		chart: {
			renderTo: 'graph-container-pie',
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false,
			defaultSeriesType: 'pie'
		},
		colors: [
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
		],
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
			formatter: function()
			{
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

	chart = new Highcharts.Chart(options);
}

//Рисует бар-чарты пришли/ушли по миру/альянсу
function drawInOutGraph(series, title, allowDay)
{
	prepareGraphDataDate(series);

	var options = {
		chart:{
			renderTo: 'graph-container',
			margin: [30, 10, 45, 60],
			type: 'column'
		},
		credits:{
			enabled: false
		},
		title:{
			style: {
				color: '#636363'
			},
			text: title,
			y: 7
		},
		xAxis:{
			type: 'datetime',
			gridLineWidth: 1,
			lineColor: '#000'
		},
		yAxis:{
			allowDecimals: false,
			min: 0,
			title:{
				text: null
			},
			labels: {
				style: {
					fontSize :'12px'
				}
			}
		},
		legend:{
			enabled: true,
			y:15

		},
		tooltip:{
			formatter: function()
			{
				return '<b>' + Highcharts.dateFormat((allowDay) ? '%d.%m.%Y':'%m.%Y', this.x) + '</b><br>' + this.series.name + ': ' + this.y;
			}
		},
		plotOptions: {
			column:{
				pointPadding: 0,
				minPointLength: 5,
				dataLabels: {
					enabled: true,
					style: {
						fontWeight: 'normal',
                        fontSize :'10px'
					}
				}
			}
		},
		series: series
	};

	chart = new Highcharts.Chart(options);
}

//форматирует большие числа в нормальный вид
function numFormat(number)
{
	return number.toString().reverse().replace(/(\d{3})(?=\d)/g,'$1`').reverse()

}

//возвращает форматированную дельту параметра для графиков из словаря
function getDelta( hash, index, reversed )
{
	var delta = '';
	if( typeof reversed === 'undefined' )
		reversed = false;

	if( typeof hash[index] === 'undefined' || hash[index] === 0 )
	{
		delta = '(<span style="' + colorGray + '">Без изменений</span>)';
	}else{
		var color = ( reversed === (hash[index] > 0) ) ? colorRed : colorGreen;
		if(hash[index] > 0)
		{
			delta = '(<span style="' + color + '">+' + numFormat(hash[index]) + '</span>)';
		}else if(hash[index] < 0){
			delta = '(<span style="' + color + '">' + numFormat(hash[index]) + '</span>)';
		}
	}
	return delta;
}


String.prototype.reverse=function(){
	return this.split("").reverse().join("");
}


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