
request = (url, crud, pass, data, cb) ->

	data = _.extend data,
		auth_identity: "admin"
		auth_secret: pass

	payload = JSON.stringify data

	switch crud
		when 'CREATE' then method = 'POST'
		when 'READ' then method = 'GET'
		when 'UPDATE' then method = 'PUT'
		when 'DELETE' then method = 'DELETE'
	

	$.ajax
		type: 'POST'
		url: url
		# headers:
		# 	'Content-Type': 'application/x-www-form-urlencoded'
		beforeSend: (request) =>
			request.setRequestHeader('X-HTTP-Method-Override', method);

		dataType: 'json'
		data: payload
		headers: _.extend data, { payload: payload }

		success: cb



amd_define ['text!./html/start.html', 'text!./html/form.html', './es6-promise'], (templates..., Promise) ->

	Promise = Promise.Promise

	checkPassword = (password) -> new Promise((resolve, reject) ->
		request 'http://killergame.nu/members2/user/noobtoothfairy@gmail.com', 'READ', password, {}, (data) -> (
			if data?.email is 'noobtoothfairy@gmail.com' then resolve() else reject(data)
		)
	)

	getAutofill = (nin) -> new Promise((resolve, reject) ->
		$.ajax
			url: 'http://killergame.nu/members/index.php?action=get_autofill&q=' + nin
			type: "GET",
			dataType: 'json'
			success: resolve
			error: reject

	)


	class View extends Backbone.View

		initialize: ->

			@current_page = 0
			@code = ""
			@once 'render', @render, @


			@on 'render:1', ->
				@$('#input_card_number').val @code
				if @formdata
					@appendFormdata()


		appendFormdata: ->
			mappings = 
				"input_sd_mobile_number": "Annattelefonnummer"
				"input_co_address": "Co-Adress"
				"input_login_email": "E-Post"
				"input_last_name": "Efternamn"
				"input_first_name": "Förnamn"
				"input_street_address": "Gatuadress"
				"input_class": "Klass"
				"input_sex": "Kön"
				"input_mobile_number": "Mobilnr."
				"input_post_town": "Ort"
				"input_nin": "Personnr"
				"input_postal_number": "Postnummer"

			for key, val of mappings
				@$("#" + key).val @formdata[val]
				console.log key, @formdata[val]



		setFormdata: (data) =>
			@formdata = {}
			for key, val of data
				key = key.replace(/\s|\r|\n/mg, '')
				@formdata[key] = val


		renderForm: (form) ->
			pass = form.find('#passphrase').val()
			nin = form.find('#personal-security-number').val().replace(/\s|\-/mg, '')
			if nin.length > 10
				nin = nin.slice 2

			NProgress.start()
			done = =>
				@next()
				NProgress.done()


			getNINdata = =>
				NProgress.set(0.5)
				getAutofill(nin).then(((data) =>
					@setFormdata(data);
					done()
				).bind(@)).catch(=>
					done()
				)


			checkPassword(pass).then(=> if nin.length then getNINdata() else done() ).catch(->
				NProgress.done()
				alert 'password error!'
				console.log arguments
			)


		events:
			'click #next': 'next'
			'click #prev': 'prev'
			'submit form': (ev) -> ev.preventDefault()
			'submit form#start': (ev) ->
				form = $(ev.currentTarget)
				@renderForm form



		next: ->
			@current_page++
			@update()
		prev: ->
			@current_page--
			@update()

		update: ->
			@$el.html templates[@current_page]
			@trigger "render:#{@current_page}"

			try document.forms[0].querySelector('input').focus()
			


		render: (code) ->
			@code = if code then code else ""
			@update()


	return View
