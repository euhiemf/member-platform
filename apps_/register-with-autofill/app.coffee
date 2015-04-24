


amd_define ['text!./html/start.html', 'text!./html/form.html'], (templates...) ->

	class Autofill extends Backbone.Model

	class View extends Backbone.View

		initialize: ->

			@current_page = 0
			@once 'render', @render, @


		events:
			'click #next': 'next'
			'click #prev': 'prev'
			'submit form': (ev) -> ev.preventDefault()
			'submit form#start': (ev) ->
				form = $(ev.currentTarget)
				pass = form.find('#passphrase').val()
				nin = form.find('#personal-security-number').val()

				$.ajax
					type: 'POST'
					url: 'http://killergame.nu/members/index.php'
					headers:
						'Content-Type': 'application/x-www-form-urlencoded'
					data:
						'user_email': 'noobtoothfairy@gmail.com' 
						'user_password': pass
						'login': 'Log in' 
					success: @getAutofill.bind @, if nin then nin else false


		getAutofill: (nin) ->
			if not nin then return

			autofill = new Autofill({id: 0})
			autofill.url = 'http://killergame.nu/members/index.php?action=get_autofill&q=9608125812'
			autofill.fetch
				success: (args...) ->
					console.log args



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
