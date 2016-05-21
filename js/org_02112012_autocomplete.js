(function( $ ) {
	$.widget( "ui.combobox", {
		_create: function() {
			var input,
				self = this,
				select = this.element.hide(),
				selected = select.children( ":selected" ),
				value = selected.val() ? selected.text() : "",
				wrapper = this.wrapper = $( "<span>" )
					.addClass( "ui-combobox" )
					.insertAfter( select );
				input = $( "<input>" )
					.appendTo( wrapper )
					.val( value )
					//ui-state-default adds comboc like skin
					.addClass( "ui-combobox-input" )
					.autocomplete({
						delay: 0,
						minLength: 2,
						source: function( request, response ) {
							$.ajax({
								url: self.options.source,
								dataType: "json",
								cache: true,
								data: {
									target : select.attr('id'),
									userinput: request.term
								},
								error :function(jqXHR, textStatus, errorThrown) {
									alert(jqXHR.responseText);
									//alert('error :'+jqXHR+' '+textStatus+' '+errorThrown);
								},
								complete :function (jqXHR, textStatus) {
								},
								success: function( data ) {
									response( $.map( data, function( item ) {
										return {
											value: item.taxon,
											label: item.taxon
										}
									}));
								}
							});
						},

					select: function( event, ui ) {
						selectCallback($(select).attr('id'), ui.item.label);
						ui.item.selected = true;
						self.store=ui.item.label;
						self._trigger( "selected", event, {
							item: ui.item.label //option
						});
					},
					change: function( event, ui ) {
						if ( !ui.item ) {
							var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
							valid = false;
							select.children( "option" ).each(function() {
								if ( $( this ).text().match( matcher ) ) {
									this.selected = valid = true;
									return false;
								}
							});
							if ( !valid ) {
								// remove invalid value, as it didn't match anything
								$( this ).val( "" );
								select.val( "" );
								input.data( "autocomplete" ).term = "";
								return false;
							}
						}
					}
				})
				.addClass( "ui-widget ui-widget-content ui-corner-left" );

			input.click(function() {
				selectCallback($(select).attr('id'), '');
				input.val('');
			});

			input.data( "autocomplete" )._renderItem = function( ul, item ) {
				return $( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.label + "</a>" )
					.appendTo( ul );
			};
			/*
			$( "<a>" )
				.attr( "tabIndex", -1 )
				.attr( "title", "Vis alle muligheder" )
				.appendTo( wrapper )
				.button({
					icons: {
						primary: "ui-icon-triangle-1-s"
					},
					text: false
				})
				.removeClass( "ui-corner-all" )
				.addClass( "ui-corner-right ui-combobox-toggle" )
				.click(function() {
					if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
						input.autocomplete( "close" );
						return;
					}
					$( this ).blur();
					input.autocomplete("search"," ");
					input.focus();
				});
			*/
		},

		destroy: function() {
			this.wrapper.remove();
			this.element.show();
			$.Widget.prototype.destroy.call( this );
		}
	});
})( jQuery );
