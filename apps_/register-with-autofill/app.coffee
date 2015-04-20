


amd_define ['text!./html/start.html', 'text!./html/form.html'], (templates...) ->

	class View extends Backbone.View

		initialize: ->

			@current_page = 0
			@once 'render', @render, @


		events:
			'click #next': 'next'
			'click #prev': 'prev'
			'submit form': (ev) -> ev.preventDefault()
			'submit form#start': (ev) -> console.log 123123123


		next: ->
			@current_page++
			@update()
		prev: ->
			@current_page--
			@update()

		update: ->
			@$el.html templates[@current_page]
			try document.forms[0].querySelector('input').focus()
			


		render: (code) ->
			console.log code
			@update()


	return View
