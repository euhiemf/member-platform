
class Menu extends Backbone.View

	el: '#main-menu'

	events:
		'click li:not(.has-children) a': (ev) ->
			@$('.active-menu').removeClass 'active-menu'
			$(ev.currentTarget).addClass 'active-menu'



	render: ->

		levels = ['first', 'second', 'third']

		dataLoop = (o, base_url, base_el, level) ->

			for key, val of o when _.has(o, key) and _.has(val, 'menu-item')
				url = base_url + key

				icon = if not val['icon'] then "" else val['icon']

				el = $("<li><a href='##{url}'><i class='fa #{icon}'></i>#{val['name']}</a></li>")
				base_el.append el
				if _.keys(val['menu-item']).length > 0

					el.addClass 'has-children'

					el.find('a').append $('<span class="fa arrow"></span>')

					nbel = $('<ul class="nav nav-' + levels[level] + '-level collapse"></ul>')
					el.append nbel

					dataLoop val['menu-item'], url + '/', nbel, level + 1


		dataLoop @data, '/', @$el, 1

		@$el.metisMenu();






class Links extends Backbone.DeepModel
	initialize: -> @

	route: -> @
	buildMenu: -> 
		menu = new Menu()
		menu.data = _.clone @toJSON()
		menu.render()

module.exports = Links